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

namespace ZfrCash\Event;

use Zend\EventManager\Event;
use ZfrCash\Entity\Invoice;

/**
 * Event that is triggered for invoices
 *
 * Currently, only the event "INVOICE_CLOSED" is triggered, which allows you to send an email or generate
 * a PDF whenever an invoice is closed
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class InvoiceEvent extends Event
{
    /**
     * Constants
     */
    const INVOICE_CLOSED = 'invoice.closed';

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}