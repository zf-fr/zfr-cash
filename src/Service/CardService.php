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
use ZfrCash\Populator\CardPopulatorTrait;
use ZfrStripe\Client\StripeClient;
use ZfrStripe\Exception\NotFoundException as StripeNotFoundException;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
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
     * @var ObjectRepository
     */
    private $cardRepository;

    /**
     * @var StripeClient
     */
    private $stripeClient;

    /**
     * @param ObjectManager $objectManager
     * @param ObjectRepository $cardRepository
     * @param StripeClient $stripeClient
     */
    public function __construct(
        ObjectManager $objectManager,
        ObjectRepository $cardRepository,
        StripeClient $stripeClient
    ) {
        $this->objectManager  = $objectManager;
        $this->cardRepository = $cardRepository;
        $this->stripeClient   = $stripeClient;
    }

    /**
     * @param  CustomerInterface $customer
     * @param  string|array      $card
     * @return Card
     */
    public function attachToCustomer(CustomerInterface $customer, $card)
    {
        if ($previousCard = $customer->getCard()) {
            $this->objectManager->remove($previousCard);
        }

        // This call always set the new card as the default one
        $stripeCustomer = $this->stripeClient->updateCustomer([
            'id'   => $customer->getStripeId(),
            'card' => $card
        ]);

        // Extract the main card from the list of cards
        $stripeDefaultCard = [];

        foreach ($stripeCustomer['cards']['data'] as $stripeCard) {
            if ($stripeCard['id'] === $stripeCustomer['default_card']) {
                $stripeDefaultCard = $stripeCard;
                break;
            }
        }

        $newCard = new Card();
        $newCard->setOwner($customer);

        $this->populateCardFromStripeResource($newCard, $stripeDefaultCard);

        $this->objectManager->persist($newCard);
        $this->objectManager->flush();

        // The new card has been set, so we can safely delete the old one from Stripe if existing
        if (null !== $previousCard) {
            try {
                $this->stripeClient->deleteCard([
                    'id'       => $previousCard->getStripeId(),
                    'customer' => $customer->getStripeId()
                ]);
            } catch (StripeNotFoundException $exception) {
                // The card may have been removed manually from Stripe, but we still need to remove it from database
            }
        }

        return $newCard;
    }

    /**
     * Remove the card from the customer
     *
     * @param  Card $card
     * @return void
     */
    public function remove(Card $card)
    {
        $owner = $card->getOwner();

        try {
            $this->stripeClient->deleteCard([
                'id'       => $card->getStripeId(),
                'customer' => $owner->getStripeId(),
            ]);
        } catch (StripeNotFoundException $exception) {
            // The card may have been removed manually from Stripe, but we still need to remove it from database
        }

        $owner->setCard(null);

        $this->objectManager->remove($card);
        $this->objectManager->flush();
    }

    /**
     * Get card by its ID
     *
     * @param  int $id
     * @return Card|null
     */
    public function getById($id)
    {
        return $this->cardRepository->find($id);
    }

    /**
     * Get one card by its customer
     *
     * @param  CustomerInterface $customer
     * @return Card|null
     */
    public function getOneByCustomer(CustomerInterface $customer)
    {
        return $this->cardRepository->findOneBy(['customer' => $customer]);
    }

    /**
     * Get one card by its Stripe ID
     *
     * @param  string $stripeId
     * @return Card|null
     */
    public function getOneByStripeId($stripeId)
    {
        return $this->cardRepository->findOneBy(['stripeId' => (string) $stripeId]);
    }

    /**
     * Sync (only for update) an existing card
     *
     * @param  array $stripeCard
     * @return void
     */
    public function syncFromStripeResource(array $stripeCard)
    {
        if ($stripeCard['object'] !== 'card') {
            return;
        }

        // For cards, we only UPDATE cards
        $card = $this->cardRepository->findOneBy(['stripeId' => $stripeCard['id']]);

        if (null === $card) {
            return; // We cannot handle it
        }

        $this->populateCardFromStripeResource($card, $stripeCard);

        $this->objectManager->flush();
    }
}