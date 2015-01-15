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
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\Discount;
use ZfrCash\Entity\Subscription;
use ZfrCash\Repository\CustomerRepositoryInterface;
use ZfrCash\StripePopulator\DiscountPopulatorTrait;
use ZfrStripe\Client\StripeClient;

/**
 * Service that handle discounts
 *
 * Discounts (== attaching a coupon to a customer or subscription) can either be done directly using this
 * service, but also from Stripe UI. Therefore, we keep bi-directional syncing between Stripe and our own
 * database for discounts, as it is pretty neat to be able to quickly add a discount through Stripe UI
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class DiscountService 
{
    use DiscountPopulatorTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectRepository
     */
    private $subscriptionRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StripeClient
     */
    private $stripeClient;

    /**
     * @param ObjectManager               $objectManager
     * @param ObjectRepository            $subscriptionRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param StripeClient                $stripeClient
     */
    public function __construct(
        ObjectManager $objectManager,
        ObjectRepository $subscriptionRepository,
        CustomerRepositoryInterface $customerRepository,
        StripeClient $stripeClient
    ) {
        $this->objectManager          = $objectManager;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->customerRepository     = $customerRepository;
        $this->stripeClient           = $stripeClient;
    }

    /**
     * Create a new discount for a customer
     *
     * @param  CustomerInterface $customer
     * @param  string            $coupon
     * @return Discount
     */
    public function createToCustomer(CustomerInterface $customer, $coupon)
    {
        $stripeCustomer = $this->stripeClient->updateCustomer([
            'id'     => $customer->getStripeId(),
            'coupon' => (string) $coupon
        ]);

        $discount = new Discount();
        $customer->setDiscount($discount);

        $this->populateDiscountFromStripeResource(new Discount(), $stripeCustomer['discount']);

        $this->objectManager->persist($discount);
        $this->objectManager->flush($discount);

        return $discount;
    }

    /**
     * Create a new discount for a subscription
     *
     * @param  Subscription $subscription
     * @param  string       $coupon
     * @return Discount
     */
    public function createToSubscription(Subscription $subscription, $coupon)
    {
        $stripeSubscription = $this->stripeClient->updateSubscription([
            'id'       => $subscription->getStripeId(),
            'customer' => $subscription->getPayer()->getStripeId(),
            'coupon'   => (string) $coupon
        ]);

        $discount = new Discount();
        $subscription->setDiscount($discount);

        $this->populateDiscountFromStripeResource($discount, $stripeSubscription['discount']);

        $this->objectManager->persist($discount);
        $this->objectManager->flush($discount);
    }

    /**
     * Remove a discount from a customer
     *
     * @param  CustomerInterface $customer
     * @return void
     */
    public function removeFromCustomer(CustomerInterface $customer)
    {
        if (!($discount = $customer->getDiscount())) {
            return;
        }

        $this->stripeClient->deleteCustomerDiscount([
            'customer' => $customer->getStripeId()
        ]);

        $this->objectManager->remove($discount);
        $this->objectManager->flush();
    }

    /**
     * Remove a discount from a subscription
     *
     * @param  Subscription $subscription
     * @return void
     */
    public function removeFromSubscription(Subscription $subscription)
    {
        $payer = $subscription->getPayer();

        if (!($discount = $payer->getDiscount())) {
            return;
        }

        $this->stripeClient->deleteCustomerDiscount([
            'customer'     => $payer->getStripeId(),
            'subscription' => $subscription->getStripeId()
        ]);

        $this->objectManager->remove($discount);
        $this->objectManager->flush();
    }

    /**
     * @param  array $stripeEvent
     * @return void
     */
    public function syncFromStripeEvent(array $stripeEvent)
    {
        if (!fnmatch('customer.discount.*', $stripeEvent['type'])) {
            return;
        }

        // The discount may have been either attached from Stripe UI or programatically, so we must make
        // to handle the case properly

        $stripeDiscount = $stripeEvent['data']['object'];

        // Retrieve the discount based on whether it is attached to a customer or customer + subscription
        $subscription = $customer = null;

        if (null !== $stripeDiscount['subscription']) {
            $subscription = $this->subscriptionRepository->findOneBy(['stripeId' => $stripeDiscount['subscription']]);
            $discount     = $subscription->getDiscount();
        } else {
            $customer = $this->customerRepository->findOneByStripeId($stripeDiscount['customer']);
            $discount = $customer->getDiscount();
        }

        // If discount already exists, and that the event is about a creation, we can skip directly as it has already
        // been saved server side
        if ($discount !== null && $stripeEvent['type'] === 'customer.discount.created') {
            return;
        }

        // If event is about deletion, then we just remove it
        if ($stripeEvent['type'] === 'customer.discount.deleted') {
            $this->objectManager->remove($discount);
            $this->objectManager->flush();

            return;
        }

        $discount = $discount ?: new Discount();
        $this->populateDiscountFromStripeResource($discount, $stripeDiscount);

        if (null !== $stripeDiscount['subscription']) {
            $subscription->setDiscount($discount);
        } else {
            $customer->setDiscount($discount);
        }

        $this->objectManager->persist($discount);
        $this->objectManager->flush($discount);
    }
}