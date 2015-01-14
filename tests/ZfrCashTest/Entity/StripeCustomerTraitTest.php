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

namespace ZfrCashTest\Entity;

use ZfrCash\Entity\Card;
use ZfrCash\Entity\Discount;
use ZfrCash\Entity\Invoice;
use ZfrCash\Entity\Subscription;
use ZfrCashTest\Asset\Customer;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers \ZfrCash\Entity\StripeCustomerTrait
 */
class StripeCustomerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $card         = new Card();
        $discount     = new Discount();
        $invoice      = new Invoice();
        $subscription = new Subscription();

        $customer = new Customer();

        $customer->setStripeId('cus_abc');
        $customer->setCard($card);
        $customer->setBalance(-500);
        $customer->setDiscount($discount);
        $customer->addInvoice($invoice);
        $customer->addSubscription($subscription);

        $this->assertSame($card, $customer->getCard());
        $this->assertEquals('cus_abc', $customer->getStripeId());
        $this->assertEquals(-500, $customer->getBalance());
        $this->assertSame($discount, $customer->getDiscount());
        $this->assertCount(1, $customer->getInvoices());
        $this->assertCount(1, $customer->getSubscriptions());

        // Assert properly maintains bi-directional associations
        $this->assertSame($customer, $invoice->getPayer());
        $this->assertSame($customer, $subscription->getPayer());

        // Make sure can remove card, discount and subscription
        $customer->setCard(null);
        $customer->setDiscount(null);
        $customer->removeSubscription($subscription);

        $this->assertNull($customer->getCard());
        $this->assertNull($customer->getDiscount());
        $this->assertEmpty($customer->getSubscriptions());
    }
}
