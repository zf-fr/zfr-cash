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
use ZfrCash\Entity\Subscription;
use ZfrCash\Entity\SubscriptionDiscount;
use ZfrCash\Populator\DiscountPopulatorTrait;
use ZfrStripe\Client\StripeClient;
use ZfrStripe\Exception\NotFoundException as StripeNotFoundException;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class SubscriptionDiscountService
{
    use DiscountPopulatorTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectRepository
     */
    private $subscriptionDiscountRepository;

    /**
     * @var ObjectRepository
     */
    private $subscriptionRepository;

    /**
     * @var StripeClient
     */
    private $stripeClient;

    /**
     * @param ObjectManager    $objectManager
     * @param ObjectRepository $subscriptionDiscountRepository
     * @param ObjectRepository $subscriptionRepository
     * @param StripeClient     $stripeClient
     */
    public function __construct(
        ObjectManager $objectManager,
        ObjectRepository $subscriptionDiscountRepository,
        ObjectRepository $subscriptionRepository,
        StripeClient $stripeClient
    ) {
        $this->objectManager                  = $objectManager;
        $this->subscriptionDiscountRepository = $subscriptionDiscountRepository;
        $this->subscriptionRepository         = $subscriptionRepository;
        $this->stripeClient                   = $stripeClient;
    }

    /**
     * Create a discount for a given subscription by attaching a coupon
     *
     * @param  Subscription $subscription
     * @param  string       $coupon
     * @return SubscriptionDiscount
     */
    public function createForSubscription(Subscription $subscription, $coupon)
    {
        // If the subscription already have a coupon, we update it instead
        if ($discount = $subscription->getDiscount()) {
            return $this->changeCoupon($discount, $coupon);
        }

        $stripeSubscription = $this->stripeClient->updateSubscription([
            'id'       => $subscription->getStripeId(),
            'customer' => $subscription->getPayer()->getStripeId(),
            'coupon'   => $coupon
        ]);

        $discount = new SubscriptionDiscount();
        $discount->setCustomer($subscription->getPayer());
        $discount->setSubscription($subscription);

        $this->populateDiscountFromStripeResource($discount, $stripeSubscription['discount']);

        $this->objectManager->persist($discount);
        $this->objectManager->flush();

        return $discount;
    }

    /**
     * Change the coupon for a given subscription discount
     *
     * @param  SubscriptionDiscount $subscriptionDiscount
     * @param  string               $coupon
     * @return SubscriptionDiscount
     */
    public function changeCoupon(SubscriptionDiscount $subscriptionDiscount, $coupon)
    {
        $stripeSubscription = $this->stripeClient->updateSubscription([
            'id'     => $subscriptionDiscount->getCustomer()->getStripeId(),
            'coupon' => $coupon
        ]);

        $this->populateDiscountFromStripeResource($subscriptionDiscount, $stripeSubscription['discount']);

        $this->objectManager->flush();

        return $subscriptionDiscount;
    }

    /**
     * Remove a discount from a subscription
     *
     * @param SubscriptionDiscount $discount
     */
    public function remove(SubscriptionDiscount $discount)
    {
        $subscription = $discount->getSubscription();
        $customer     = $subscription->getPayer();

        try {
            $this->stripeClient->deleteSubscriptionDiscount([
                'customer'     => $customer->getStripeId(),
                'subscription' => $subscription->getStripeId()
            ]);
        } catch (StripeNotFoundException $exception) {
            // The discount may have been removed manually from Stripe, but we still need to remove it from database
        }

        $subscription->setDiscount(null);

        $this->objectManager->remove($discount);
        $this->objectManager->flush();
    }

    /**
     * Get subscription discount by its ID
     *
     * @param  int $id
     * @return SubscriptionDiscount|null
     */
    public function getById($id)
    {
        return $this->subscriptionDiscountRepository->find($id);
    }

    /**
     * Get one subscription discount by its subscription
     *
     * @param  Subscription $subscription
     * @return SubscriptionDiscount|null
     */
    public function getOneByCustomer(Subscription $subscription)
    {
        return $this->subscriptionDiscountRepository->findOneBy(['subscription' => $subscription]);
    }

    /**
     * Sync (only for creation and update) a discount
     *
     * @param  array $stripeDiscount
     * @return void
     */
    public function syncFromStripeResource(array $stripeDiscount)
    {
        if ($stripeDiscount['object'] !== 'discount') {
            return;
        }

        /** @var Subscription|null $subscription */
        $subscription = $this->subscriptionRepository->findOneBy(['stripeId' => $stripeDiscount['subscription']]);

        if (null === $subscription) {
            return;
        }

        $discount = $this->subscriptionDiscountRepository->findOneBy(['subscription' => $subscription]);

        if (null === $discount) {
            $discount = new SubscriptionDiscount();
            $discount->setCustomer($subscription->getPayer());
            $discount->setSubscription($subscription);

            $this->objectManager->persist($discount);
        }

        $this->populateDiscountFromStripeResource($discount, $stripeDiscount);

        $this->objectManager->persist($discount);
        $this->objectManager->flush();
    }
}