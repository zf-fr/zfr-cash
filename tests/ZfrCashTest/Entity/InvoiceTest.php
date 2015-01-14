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

use DateTime;
use ZfrCash\Entity\Discount;
use ZfrCash\Entity\Invoice;
use ZfrCash\Entity\LineItem;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers \ZfrCash\Entity\Invoice
 */
class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $discount = new Discount();

        $lineItems = [];
        $lineItems[] = new LineItem();

        $invoice = new Invoice();
        $invoice->setStripeId('in_abc');
        $invoice->setAmountDue(400);
        $invoice->setCreatedAt(new DateTime());
        $invoice->setCurrency('usd');
        $invoice->setDescription('One single invoice');
        $invoice->setDiscount($discount);
        $invoice->setPeriodStart(new DateTime());
        $invoice->setPeriodEnd(new DateTime());
        $invoice->setSubtotal(500);
        $invoice->setTotal(400);
        $invoice->setLineItems($lineItems);

        $this->assertEquals('in_abc', $invoice->getStripeId());
        $this->assertEquals(400, $invoice->getAmountDue());
        $this->assertInstanceOf(DateTime::class, $invoice->getCreatedAt());
        $this->assertEquals('usd', $invoice->getCurrency());
        $this->assertEquals('One single invoice', $invoice->getDescription());
        $this->assertSame($discount, $invoice->getDiscount());
        $this->assertInstanceOf(DateTime::class, $invoice->getPeriodStart());
        $this->assertInstanceOf(DateTime::class, $invoice->getPeriodEnd());
        $this->assertEquals(500, $invoice->getSubtotal());
        $this->assertEquals(400, $invoice->getTotal());
        $this->assertCount(1, $invoice->getLineItems());

        $this->assertSame($invoice, $invoice->getLineItems()[0]->getInvoice());
    }
}
