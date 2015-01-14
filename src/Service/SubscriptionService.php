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

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use ZfrCash\Entity\BillableInterface;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\Plan;
use ZfrCash\Entity\Subscription;
use ZfrCash\Entity\VatCustomerInterface;
use ZfrCash\Repository\BillableRepositoryInterface;
use ZfrCash\StripePopulator\SubscriptionPopulatorTrait;
use ZfrStripe\Client\StripeClient;

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
     * @var VatService
     */
    private $vatService;

    /**
     * @var StripeClient
     */
    private $stripeClient;

    /**
     * Create a new subscription for the given customer on a given plan for a billable resource (with optional quantity)
     *
     * @param  CustomerInterface $customer
     * @param  BillableInterface $billable
     * @param  Plan              $plan
     * @param  int               $quantity
     * @return Subscription
     */
    public function create(CustomerInterface $customer, BillableInterface $billable, Plan $plan, $quantity = 1)
    {
        $parameters = [
            'customer'    => $customer->getStripeId(),
            'plan'        => $plan->getStripeId(),
            'quantity'    => (int) $quantity
        ];

        // If we want to handle VAT, we need to fetch the VAT tax rate
        if ($customer instanceof VatCustomerInterface) {
            $parameters['tax_percent'] = $this->vatService->getVatRate($customer);
        }

        $stripeSubscription = $this->stripeClient->createSubscription($parameters);
        $subscription       = new Subscription();

        $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);

        $subscription->setPayer($customer);
        $subscription->setPlan($plan);

        $billable->setSubscription($subscription);

        $this->objectManager->persist($subscription);
        $this->objectManager->flush([$billable, $subscription]);

        return $subscription;
    }

    /**
     * Cancel an existing subscription
     *
     * @param Subscription $subscription
     * @param bool         $atPeriodEnd
     */
    public function cancel(Subscription $subscription, $atPeriodEnd = false)
    {
        $stripeSubscription = $this->stripeClient->cancelSubscription([
            'id'            => $subscription->getStripeId(),
            'customer'      => $subscription->getPayer()->getStripeId(),
            'at_period_end' => (bool) $atPeriodEnd
        ]);

        $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);

        // Make sure to properly remove the subscription from the billable resource
        $billable = $this->billableRepository->findBySubscription($subscription);
        $billable->setSubscription(null);

        $this->objectManager->flush();
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
     * Update all the customer's subscriptions with a new VAT rate (for instance if customer has changed country)
     *
     * @param  VatCustomerInterface $customer
     * @return void
     */
    public function updateCustomerSubscriptionsVatRate(VatCustomerInterface $customer)
    {
        $vatRate       = $this->vatService->getVatRate($customer);
        $subscriptions = $this->subscriptionRepository->findBy(['payer' => $customer]);

        // For each subscription, we must update the subscription on Stripe, and update our entity

        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $stripeSubscription = $this->stripeClient->updateSubscription([
                'id'          => $subscription->getStripeId(),
                'customer'    => $customer->getStripeId(),
                'tax_percent' => $vatRate
            ]);

            $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);
        }

        $this->objectManager->flush();
    }

    /**
     * Get all subscriptions for a given customer
     *
     * @param  CustomerInterface $customer
     * @return Subscription[]
     */
    public function getByCustomer(CustomerInterface $customer)
    {
        return $this->subscriptionRepository->findBy(['payer' => $customer]);
    }

    /**
     * @param  array $stripeEvent
     * @return void
     */
    public function syncFromStripeEvent(array $stripeEvent)
    {
        // We assume subscriptions are always created from your application, hence we do not try to sync for those
        if (!in_array($stripeEvent['type'], ['customer.subscription.updated', 'customer.subscription.deleted'], true)) {
            return;
        }

        $stripeSubscription = $stripeEvent['data']['object'];

        // We must make sure that the subscription properly exists on our end before trying to update or cancelled it
        $subscription = $this->subscriptionRepository->findOneBy(['stripeId' => $stripeSubscription['id']]);

        if (null === $subscription) {
            return;
        }

        $this->populateSubscriptionFromStripeResource($subscription, $stripeSubscription);

        // If it relates to a "cancellation" event and that it has not been already cancelled server side,
        // we must properly detach the subscription from the billable resource
        if ($stripeEvent['type'] === 'customer.subscription.deleted' && !$subscription->isCancelled()) {
            $billable = $this->billableRepository->findBySubscription($subscription);
            $billable->setSubscription(null);
        }

        $this->objectManager->flush();
    }
}