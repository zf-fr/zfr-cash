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
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\StripePopulator\CardPopulatorTrait;
use ZfrStripe\Client\StripeClient;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class CardService
{
    use CardPopulatorTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StripeClient
     */
    private $stripeClient;

    /**
     * @param ObjectManager $objectManager
     * @param StripeClient  $stripeClient
     */
    public function __construct(ObjectManager $objectManager, StripeClient $stripeClient)
    {
        $this->objectManager = $objectManager;
        $this->stripeClient  = $stripeClient;
    }

    /**
     * Create a new card, and attach it to a customer
     *
     * If customer had a previous default card, then the old card is deleted
     *
     * @param  CustomerInterface $customer
     * @param  string            $cardToken
     * @return Card
     */
    public function attachToCustomer(CustomerInterface $customer, $cardToken)
    {
        $stripeCard = $this->stripeClient->createCard(['card' => (string) $cardToken]);

        // If the customer had a previous card, we must remove it before saving the new one
        if (null !== $customer->getCard()) {
            $this->objectManager->remove($customer->getCard());
        }

        $card = new Card();
        $card->setCustomer($customer);

        $this->populateCardFromStripeResource($card, $stripeCard);

        $this->objectManager->persist($card);
        $this->objectManager->flush();

        return $card;
    }

    /**
     * Remove an existing card
     *
     * @param  Card $card
     * @return void
     */
    public function remove(Card $card)
    {
        $this->objectManager->remove($card);
        $this->objectManager->flush();
    }
}