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

namespace ZfrCashTest\Populator;

use PHPUnit_Framework_TestCase;
use ZfrCash\Entity\Card;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Populator\CardPopulatorTrait;

class CardPopulatorTraitTest extends PHPUnit_Framework_TestCase
{
    public function testPopulator()
    {
        $customer = $this->getMock(CustomerInterface::class);

        $card = new Card();
        $card->setOwner($customer);

        $populator = $this->getMockForTrait(CardPopulatorTrait::class);

        $reflMethod = new \ReflectionMethod($populator, 'populateCardFromStripeResource');
        $reflMethod->setAccessible(true);

        $stripeCard = [
            'id'        => 'card_abc',
            'brand'     => 'visa',
            'exp_month' => 2,
            'exp_year'  => 2018,
            'last4'     => '0234',
            'country'   => 'fr'
        ];

        $reflMethod->invoke($populator, $card, $stripeCard);

        $this->assertEquals($stripeCard['id'], $card->getStripeId());
        $this->assertEquals($stripeCard['brand'], $card->getBrand());
        $this->assertEquals($stripeCard['exp_month'], $card->getExpMonth());
        $this->assertEquals($stripeCard['exp_year'], $card->getExpYear());
        $this->assertEquals($stripeCard['last4'], $card->getLast4());
        $this->assertEquals($stripeCard['country'], $card->getCountry());
        $this->assertSame($customer, $card->getOwner(), 'Make sure populator does not overwrite association');
    }
}