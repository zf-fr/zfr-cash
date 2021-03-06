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
use ZfrCash\Entity\Card;
use ZfrCash\Entity\CustomerDiscount;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Populator\CardPopulatorTrait;
use ZfrCash\Populator\DiscountPopulatorTrait;
use ZfrCash\Repository\CustomerRepositoryInterface;
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
     * Note that you are responsible to pass your own instance of a customer. This is more logical because
     * most of the time, the customer will be your own user class, and there is high probability that you have
     * already created it elsewhere
     *
     * You can pass an optional card token (created using Stripe.js), an optional coupon and some additional
     * options. Supported options are:
     *
     *    - card: the Card token (or an array of card properties) - only for API version older than 2015-02-18
     *    - source: a Card token (or an array of card properties) - only for API version newer or equal than 2015-02-18
     *    - coupon: an optional coupon
     *    - email: set as the "email" field in Stripe
     *    - description: set as the "description" field in Stripe
     *    - metadata: set as the "metadata" field in Stripe
     *    - idempotency_key: a key that is used to prevent an operation for being executed twice
     *
     * @param  CustomerInterface $customer
     * @param  array             $options
     * @return CustomerInterface
     */
    public function create(CustomerInterface $customer, array $options = [])
    {
        $apiVersion = $this->stripeClient->getApiVersion();
        $payload    = [
            'coupon'          => isset($options['coupon']) ? $options['coupon'] : null,
            'description'     => isset($options['description']) ? $options['description'] : null,
            'email'           => isset($options['email']) ? $options['email'] : null,
            'metadata'        => isset($options['metadata']) ? $options['metadata'] : null,
            'idempotency_key' => isset($options['idempotency_key']) ? $options['idempotency_key'] : null
        ];

        if ($apiVersion < '2015-02-18') {
            $payload['card'] = isset($options['card']) ? $options['card'] : null;
        } else {
            $payload['source'] = isset($options['source']) ? $options['source'] : null;
        }

        $stripeCustomer = $this->stripeClient->createCustomer(array_filter($payload));

        // If an idempotency key is given, this means that the user explicitly want to protect the POST operation,
        // hence this means that the subscription may have already been created, if that's the case we just return it
        if (isset($options['idempotency_key'])) {
            if ($existingCustomer = $this->customerRepository->findOneByStripeId($stripeCustomer['id'])) {
                return $existingCustomer;
            }
        }

        $customer->setStripeId($stripeCustomer['id']);

        if (!empty($stripeCustomer['sources']['data'])) {
            $card = new Card();
            $customer->setCard($card);

            $this->populateCardFromStripeResource($card, current($stripeCustomer['sources']['data']));
        }

        if (null !== $stripeCustomer['discount']) {
            $discount = new CustomerDiscount();
            $customer->setDiscount($discount);

            $this->populateDiscountFromStripeResource($discount, $stripeCustomer['discount']);
        }

        $this->objectManager->persist($customer);
        $this->objectManager->flush();

        return $customer;
    }

    /**
     * Get one customer by its Stripe ID
     *
     * @param  string $stripeId
     * @return CustomerInterface|null
     */
    public function getOneByStripeId($stripeId)
    {
        return $this->customerRepository->findOneByStripeId($stripeId);
    }
}
