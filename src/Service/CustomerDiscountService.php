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
use ZfrCash\Entity\CustomerDiscount;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Populator\DiscountPopulatorTrait;
use ZfrCash\Repository\CustomerRepositoryInterface;
use ZfrStripe\Client\StripeClient;
use ZfrStripe\Exception\NotFoundException as StripeNotFoundException;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class CustomerDiscountService 
{
    use DiscountPopulatorTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectRepository
     */
    private $customerDiscountRepository;

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
     * @param ObjectRepository            $customerDiscountRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param StripeClient                $stripeClient
     */
    public function __construct(
        ObjectManager $objectManager,
        ObjectRepository $customerDiscountRepository,
        CustomerRepositoryInterface $customerRepository,
        StripeClient $stripeClient
    ) {
        $this->objectManager              = $objectManager;
        $this->customerDiscountRepository = $customerDiscountRepository;
        $this->customerRepository         = $customerRepository;
        $this->stripeClient               = $stripeClient;
    }

    /**
     * Create a discount for a given customer by attaching a coupon
     *
     * @param  CustomerInterface $customer
     * @param  string            $coupon
     * @return CustomerDiscount
     */
    public function createForCustomer(CustomerInterface $customer, $coupon)
    {
        // If the customer already have a coupon, we update it instead
        if ($discount = $customer->getDiscount()) {
            return $this->changeCoupon($discount, $coupon);
        }

        $stripeCustomer = $this->stripeClient->updateCustomer([
            'id'     => $customer->getStripeId(),
            'coupon' => $coupon
        ]);

        $discount = new CustomerDiscount();
        $discount->setCustomer($customer);

        $this->populateDiscountFromStripeResource($discount, $stripeCustomer['discount']);

        $this->objectManager->persist($discount);
        $this->objectManager->flush();

        return $discount;
    }

    /**
     * Change the coupon for a given customer discount
     *
     * @param  CustomerDiscount $customerDiscount
     * @param  string           $coupon
     * @return CustomerDiscount
     */
    public function changeCoupon(CustomerDiscount $customerDiscount, $coupon)
    {
        $stripeCustomer = $this->stripeClient->updateCustomer([
            'id'     => $customerDiscount->getCustomer()->getStripeId(),
            'coupon' => $coupon
        ]);

        $this->populateDiscountFromStripeResource($customerDiscount, $stripeCustomer['discount']);

        $this->objectManager->flush();

        return $customerDiscount;
    }

    /**
     * Remove a discount from a customer
     *
     * @param CustomerDiscount $discount
     */
    public function remove(CustomerDiscount $discount)
    {
        $customer = $discount->getCustomer();

        try {
            $this->stripeClient->deleteCustomerDiscount([
                'customer' => $customer->getStripeId()
            ]);
        } catch (StripeNotFoundException $exception) {
            // The discount may have been removed manually from Stripe, but we still need to remove it from database
        }

        $customer->setDiscount(null);

        $this->objectManager->remove($discount);
        $this->objectManager->flush();
    }

    /**
     * Get customer discount by its ID
     *
     * @param  int $id
     * @return CustomerDiscount|null
     */
    public function getById($id)
    {
        return $this->customerDiscountRepository->find($id);
    }

    /**
     * Get one customer discount by its customer
     *
     * @param  CustomerInterface $customer
     * @return CustomerDiscount|null
     */
    public function getOneByCustomer(CustomerInterface $customer)
    {
        return $this->customerDiscountRepository->findOneBy(['customer' => $customer]);
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

        $customer = $this->customerRepository->findOneByStripeId($stripeDiscount['customer']);

        if (null === $customer) {
            return;
        }

        $discount = $this->customerDiscountRepository->findOneBy(['customer' => $customer]);

        if (null === $discount) {
            $discount = new CustomerDiscount();
            $discount->setCustomer($customer);

            $this->objectManager->persist($discount);
        }

        $this->populateDiscountFromStripeResource($discount, $stripeDiscount);

        $this->objectManager->persist($discount);
        $this->objectManager->flush();
    }
}