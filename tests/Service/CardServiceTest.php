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

namespace ZfrCashTest\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Tests\Models\Taxi\Car;
use PHPUnit_Framework_TestCase;
use ZfrCash\Entity\Card;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Service\CardService;
use ZfrStripe\Client\StripeClient;

class CardServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cardRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stripeClient;

    /**
     * @var CardService
     */
    private $cardService;

    public function setUp()
    {
        $this->objectManager = $this->getMock(ObjectManager::class);
        $this->cardRepository = $this->getMock(ObjectRepository::class);
        $this->stripeClient   = $this->getMock(
            StripeClient::class,
            ['updateCustomer', 'deleteCard', 'getApiVersion'],
            [],
            '',
            false
        );

        $this->cardService = new CardService($this->objectManager, $this->cardRepository, $this->stripeClient);
    }

    /**
     * @return array
     */
    public function attachProvider()
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider attachProvider
     *
     * @param bool $hasExistingCard
     */
    public function testAssertCanAttachCardForOldStripeVersion($hasExistingCard)
    {
        $this->stripeClient->expects($this->once())->method('getApiVersion')->willReturn('2015-02-16');

        $customer     = $this->getMock(CustomerInterface::class);
        $existingCard = $hasExistingCard ? new Card() : null;

        if ($hasExistingCard) {
            $existingCard->setStripeId('card_abc');
            $existingCard->setOwner($customer);

            $this->objectManager->expects($this->once())->method('remove')->with($existingCard);
            $customer->expects($this->once())->method('getCard')->willReturn($existingCard);
        }

        $stripeCustomerId = 'cus_abc';

        $customer->expects($this->any())->method('getStripeId')->willReturn($stripeCustomerId);

        $stripeCustomer = [
            'sources' => [
                'data' => [
                    [
                        'id'        => 'card_def',
                        'brand'     => 'visa',
                        'exp_month' => 2,
                        'exp_year'  => 2018,
                        'last4'     => '0234',
                        'country'   => 'FR'
                    ]
                ]
            ],
            'default_source' => 'card_def'
        ];

        $this->stripeClient->expects($this->once())
                           ->method('updateCustomer')
                           ->with(['id' => 'cus_abc', 'card' => 'tok_def'])
                           ->willReturn($stripeCustomer);

        $card = $this->cardService->attachToCustomer($customer, 'tok_def');

        $this->assertInstanceOf(Card::class, $card);
    }

    /**
     * @dataProvider attachProvider
     *
     * @param bool $hasExistingCard
     */
    public function testAssertCanAttachCardForNewVersion($hasExistingCard)
    {
        $this->stripeClient->expects($this->once())->method('getApiVersion')->willReturn('2015-02-18');

        $customer     = $this->getMock(CustomerInterface::class);
        $existingCard = $hasExistingCard ? new Card() : null;

        if ($hasExistingCard) {
            $existingCard->setStripeId('card_abc');
            $existingCard->setOwner($customer);

            $this->objectManager->expects($this->once())->method('remove')->with($existingCard);
            $customer->expects($this->once())->method('getCard')->willReturn($existingCard);
        }

        $stripeCustomerId = 'cus_abc';

        $customer->expects($this->any())->method('getStripeId')->willReturn($stripeCustomerId);

        $stripeCustomer = [
            'sources' => [
                'data' => [
                    [
                        'id'        => 'card_def',
                        'brand'     => 'visa',
                        'exp_month' => 2,
                        'exp_year'  => 2018,
                        'last4'     => '0234',
                        'country'   => 'FR'
                    ]
                ]
            ],
            'default_source' => 'card_def'
        ];

        $this->stripeClient->expects($this->once())
                           ->method('updateCustomer')
                           ->with(['id' => 'cus_abc', 'source' => 'tok_def'])
                           ->willReturn($stripeCustomer);

        $card = $this->cardService->attachToCustomer($customer, 'tok_def');

        $this->assertInstanceOf(Card::class, $card);
    }

    public function testRemoveCard()
    {
        $owner = $this->getMock(CustomerInterface::class);

        $card  = new Card();
        $card->setOwner($owner);

        $this->stripeClient->expects($this->once())->method('deleteCard');
        $owner->expects($this->once())->method('setCard')->with(null);
        $this->objectManager->expects($this->once())->method('remove')->with($card);

        $this->cardService->remove($card);
    }

    public function testDoNotSyncIfNotCardObject()
    {
        $stripeResource = ['object' => 'subscription'];

        $this->cardRepository->expects($this->never())->method('findOneBy');
        $this->objectManager->expects($this->never())->method('flush');

        $this->assertNull($this->cardService->syncFromStripeResource($stripeResource));
    }

    public function testDoNotSyncIfCardDoesNotExistLocally()
    {
        $stripeResource = [
            'object' => 'card',
            'id'     => 'card_abc'
        ];

        $this->cardRepository->expects($this->once())->method('findOneBy')->with(['stripeId' => 'card_abc'])->willReturn(null);
        $this->objectManager->expects($this->never())->method('flush');

        $this->assertNull($this->cardService->syncFromStripeResource($stripeResource));
    }
}