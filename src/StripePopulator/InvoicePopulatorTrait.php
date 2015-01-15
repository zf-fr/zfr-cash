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

namespace ZfrCash\StripePopulator;

use DateTime;
use ZfrCash\Entity\Invoice;
use ZfrCash\Entity\LineItem;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
trait InvoicePopulatorTrait
{
    /**
     * @param Invoice $invoice
     * @param array   $stripeInvoice
     */
    protected function populateInvoiceFromStripeResource(Invoice $invoice, array $stripeInvoice)
    {
        $invoice->setStripeId($stripeInvoice['id']);
        $invoice->setCreatedAt((new DateTime())->setTimestamp($stripeInvoice['date']));
        $invoice->setPeriodStart((new DateTime())->setTimestamp($stripeInvoice['period_start']));
        $invoice->setPeriodEnd((new DateTime())->setTimestamp($stripeInvoice['period_end']));
        $invoice->setStartingBalance($stripeInvoice['starting_balance']);
        $invoice->setEndingBalance($stripeInvoice['ending_balance']);
        $invoice->setSubtotal($stripeInvoice['subtotal']);
        $invoice->setTotal($stripeInvoice['total']);
        $invoice->setApplicationFee($stripeInvoice['application_fee']);
        $invoice->setTax(isset($stripeInvoice['tax']) ? $stripeInvoice['tax'] : 0); // Tax is not always in Stripe payload
        $invoice->setTaxPercent($stripeInvoice['tax_percent']);
        $invoice->setAmountDue($stripeInvoice['amount_due']);
        $invoice->setCurrency($stripeInvoice['currency']);
        $invoice->setClosed($stripeInvoice['closed']);
        $invoice->setPaid($stripeInvoice['paid']);
        $invoice->setForgiven($stripeInvoice['forgiven']);
        $invoice->setAttemptCount($stripeInvoice['attempt_count']);
        $invoice->setDescription($stripeInvoice['description']);
    }
}