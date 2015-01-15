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
use ZfrCash\Entity\Card;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\Discount;
use ZfrCash\Entity\VatCustomerInterface;
use ZfrCash\Repository\CustomerRepositoryInterface;
use ZfrCash\StripePopulator\CardPopulatorTrait;
use ZfrCash\StripePopulator\DiscountPopulatorTrait;
use ZfrStripe\Client\StripeClient;

/**
 * Service that allows to create a Stripe customer
 *
 * Customers are assumed to be created on application-side, so any customer created in Stripe UI are
 * not managed, and are not sync neither
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class CustomerService 
{
    use CardPopulatorTrait;
    use DiscountPopulatorTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

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
     * @param CustomerRepositoryInterface $customerRepository
     * @param StripeClient                $stripeClient
     */
    public function __construct(
        ObjectManager $objectManager,
        CustomerRepositoryInterface $customerRepository,
        StripeClient $stripeClient
    ) {
        $this->objectManager      = $objectManager;
        $this->customerRepository = $customerRepository;
        $this->stripeClient       = $stripeClient;
    }

    /**
     * Create a new Stripe customer object
     *
     * You can pass an optional card token (created using Stripe.js), an optional coupon and some additional
     * options. Supported options are:
     *
     *    - email: set as the "email" field in Stripe
     *    - description: set as the "description" field in Stripe
     *    - metadata: set as the "metadata" field in Stripe
     *
     * @param  string|null $cardToken
     * @param  string|null $coupon
     * @param  array       $options
     * @return CustomerInterface
     */
    public function create($cardToken = null, $coupon = null, array $options)
    {
        $stripeCustomer = $this->stripeClient->createCustomer(array_filter([
            'card'        => $cardToken,
            'coupon'      => $coupon,
            'description' => isset($options['description']) ? $options['description'] : null,
            'email'       => isset($options['email']) ? $options['email'] : null,
            'metadata'    => isset($options['metadata']) ? $options['metadata'] : null
        ]));

        $className = $this->customerRepository->getClassName();

        /** @var CustomerInterface $customer */
        $customer = new $className;
        $customer->setStripeId($stripeCustomer['id']);

        if (!empty($stripeCustomer['cards']['data'])) {
            $card = new Card();
            $card->setCustomer($customer);

            $this->populateCardFromStripeResource($card, current($stripeCustomer['cards']['data']));
        }

        if (null !== $stripeCustomer['discount']) {
            $discount = new Discount();
            $discount->setCustomer($customer);

            $this->populateDiscountFromStripeResource($discount, $stripeCustomer['discount']);
        }

        $customer->setCard();
        $customer->setDiscount();

        $this->objectManager->persist($customer);
        $this->objectManager->flush();

        return $customer;
    }
}