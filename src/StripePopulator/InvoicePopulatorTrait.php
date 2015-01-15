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
        $invoice->setSubtotal($stripeInvoice['subtotal']);
        $invoice->setTotal($stripeInvoice['total']);
        $invoice->setTax(isset($stripeInvoice['tax']) ? $stripeInvoice['tax'] : 0);
        $invoice->setTaxPercent(isset($stripeInvoice['tax_percent']) ? $stripeInvoice['tax_percent'] : 0);
        $invoice->setAmountDue($stripeInvoice['amount_due']);
        $invoice->setCurrency($stripeInvoice['currency']);
        $invoice->setClosed($stripeInvoice['closed']);
        $invoice->setPaid($stripeInvoice['paid']);
        $invoice->setForgiven($stripeInvoice['forgiven']);
        $invoice->setAttemptCount($stripeInvoice['attempt_count']);
        $invoice->setDescription($stripeInvoice['description']);

        // Create the latest 10 line items for details. Note that an invoice may have more line items,
        // but if it contains thousands of items it may quickly break your database. Also note that we save
        // the line items ONLY when the invoice is considered as closed. The reason is that until the invoice
        // is closed, new line items may be introduced, and we do not want to save them until then

        if ($stripeInvoice['closed'] && empty($invoice->getLineItems())) {
            $lineItems = [];

            foreach ($stripeInvoice['lines']['data'] as $lineItemData) {
                $lineItem = new LineItem();
                $lineItem->setAmount($lineItemData['amount']);
                $lineItem->setDescription($lineItemData['description']);
                $lineItem->setCurrency($lineItemData['currency']);

                if ($lineItemData['type'] === 'subscription') {
                    $lineItem->setDescription(sprintf(
                        'Usage on %s x %s',
                        $lineItem['quantity'],
                        $lineItem['plan']['name']
                    ));
                }

                $lineItems[] = $lineItem;
            }

            $invoice->setLineItems($lineItems);
        }
    }
}