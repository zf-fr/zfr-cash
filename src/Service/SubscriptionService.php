<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrCash\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use ZfrCash\Entity\BillableInterface;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\Plan;
use ZfrCash\Entity\Subscription;
use ZfrCash\Repository\BillableRepositoryInterface;
use ZfrCash\Populator\SubscriptionPopulatorTrait;
use ZfrStripe\Client\StripeClient;
use ZfrStripe\Exception\NotFoundException as StripeNotFoundException;

/**
 * Subscription service
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class SubscriptionService
{
    use SubscriptionPopulatorTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectRepository
     */
    private $subscriptionRepository;

    /**
     * @var BillableRepositoryInterface
     */
    private $billableRepository;

    /**
     * @var StripeClient
     */
    private $stripeClient;

    /**
     * @param ObjectManager               $objectManager
     * @param ObjectRepository            $subscriptionRepository
     * @param BillableRepositoryInterface $billableRepository
     * @param StripeClient                $stripeClient
     */
    public function __construct(
        ObjectManager $objectManager,
        ObjectRepository $subscriptionRepository,
        BillableRepositoryInterface $billableRepository,
        StripeClient $stripeClient
    ) {
        $this->objectManager          = $objectManager;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->billableRepository     = $billableRepository;
        $this->stripeClient           = $stripeClient;
    }

    /**
     * Create a new subscription for the given customer on a given plan for a billable resource
     *
     * Possible options are:
     *
     *      - tax_percent: allow to set a tax that will be applied in addition of normal plan price
     *      - quantity: set a quantity for the plan
     *      - trial_end: a DateTime that represents that allows to manually set an trial date
     *      - application_fee_percent: if you are creating subscription on behalf of other through Stripe Connect
     *      - metadata: any pair of metadata
     *
     * @param  CustomerInterface $customer
     * @param  BillableInterface $billable
     * @param  Plan              $plan
     * @param  array             $options
     * @return Subscription
     */
    public function create(CustomerInterface $customer, BillableInterface $billable, Plan $plan, array $options = [])
    {
        $stripeSubscription = $this->stripeClient->createSubscription(array_filter([
            'customer'                => $customer->getStripeId(),
            'plan'                    => $plan->getStripeId(),
            'quantity'                => isset($options['quantity']) ? (int) $options['quantity'] : null,
            'tax_percent'             => isset($options['tax_percent']) ? $options['tax_percent'] : null,
            'trial_end'               => isset($options['trial_end']) ? $options['trial_end']->getTimestamp() : null,
            'application_fee_percent' => isset($options['application_fee_percent']) ? $options['application_fee_percent'] : null,
            'metadata'                => isset($options['metadata']) ? $options['metadata'] : null
        ]));

        $subscription = new Subscription();
        $subscription->setPayer($customer);
        $subscription->setPlan($plan);

        $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);

        $billable->setSubscription($subscription);

        $this->objectManager->persist($subscription);
        $this->objectManager->flush();

        return $subscription;
    }

    /**
     * Cancel an existing subscription
     *
     * @param  Subscription $subscription
     * @param  bool         $atPeriodEnd
     * @return Subscription|null
     */
    public function cancel(Subscription $subscription, $atPeriodEnd = false)
    {
        $stripeSubscription = null;

        try {
            $stripeSubscription = $this->stripeClient->cancelSubscription([
                'id'            => $subscription->getStripeId(),
                'customer'      => $subscription->getPayer()->getStripeId(),
                'at_period_end' => (bool) $atPeriodEnd
            ]);
        } catch (StripeNotFoundException $exception) {
            // The subscription may have been removed manually from Stripe, but we still need to remove it from database
        }

        // If cancel_at_period_end is false, then this means we must remove the subscription right now
        if (!$atPeriodEnd) {
            $this->remove($subscription);

            return null;
        }

        // Otherwise, it's what we called "on grace", this means that the subscription is not yet cancelled
        if (null !== $stripeSubscription) {
            $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);
            $this->objectManager->flush();
        }

        return $subscription;
    }

    /**
     * Modify a subscription to a new plan
     *
     * @param  Subscription $subscription
     * @param  Plan         $plan
     * @param  bool         $prorate
     * @return Subscription
     */
    public function modifyPlan(Subscription $subscription, Plan $plan, $prorate = true)
    {
        $stripeSubscription = $this->stripeClient->updateSubscription([
            'id'       => $subscription->getStripeId(),
            'customer' => $subscription->getPayer()->getStripeId(),
            'plan'     => $plan->getStripeId(),
            'prorate'  => (bool) $prorate
        ]);

        $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);

        $this->objectManager->flush($subscription);
    }

    /**
     * Modify a subscription quantity
     *
     * @param  Subscription $subscription
     * @param  int          $quantity
     * @param  bool         $prorate
     * @return Subscription
     */
    public function modifyQuantity(Subscription $subscription, $quantity, $prorate = true)
    {
        $stripeSubscription = $this->stripeClient->updateSubscription([
            'id'       => $subscription->getStripeId(),
            'customer' => $subscription->getPayer()->getStripeId(),
            'quantity' => (int) $quantity,
            'prorate'  => (bool) $prorate
        ]);

        $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);

        $this->objectManager->flush($subscription);
    }

    /**
     * Get a subscription by its ID
     *
     * @param  int $id
     * @return Subscription|null
     */
    public function getById($id)
    {
        return $this->subscriptionRepository->find($id);
    }

    /**
     * Get a subscription by its Stripe ID
     *
     * @param  string $stripeId
     * @return Subscription|null
     */
    public function getOneByStripeId($stripeId)
    {
        return $this->subscriptionRepository->findOneBy(['stripeId' => $stripeId]);
    }

    /**
     * Get all subscriptions for a given customer
     *
     * NOTE: we do not paginate, because the number of subscriptions by customer will stay very low (Stripe
     * has a soft-limit of 25 subscriptions per customer)
     *
     * @param  CustomerInterface $customer
     * @return Subscription[]
     */
    public function getByCustomer(CustomerInterface $customer)
    {
        return $this->subscriptionRepository->findBy(['payer' => $customer]);
    }

    /**
     * Sync (only update and deletion) an existing subscription
     *
     * @param  array $stripeSubscription
     * @return void
     */
    public function syncFromStripeResource(array $stripeSubscription)
    {
        if ($stripeSubscription['object'] !== 'subscription') {
            return;
        }

        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->findOneBy(['stripe_id' => $stripeSubscription['id']]);

        if (null === $subscription) {
            return; // We do not handle creation
        }

        // If ended_at is not null, then the subscription has been removed completely
        if (null !== $stripeSubscription['ended_at']) {
            $this->remove($subscription);

            return null;
        }

        $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);

        $this->objectManager->flush();
    }

    /**
     * Remove the subscription from database
     *
     * This is an internal method, please use "cancel" to cancel your subscription
     *
     * @param  Subscription $subscription
     * @return void
     */
    private function remove(Subscription $subscription)
    {
        $billable = $this->billableRepository->findOneBySubscription($subscription);
        $billable->setSubscription(null);

        $this->objectManager->remove($subscription);
        $this->objectManager->flush();
    }
}