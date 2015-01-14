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

namespace ZfrCash\Entity;

/**
 * A line item is a sub-object contains inside an invoice. It allows to show a detailed
 * breakdown of invoices
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class LineItem
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Invoice $invoice
     */
    public function setInvoice(Invoice $invoice)
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

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = (int) $amount;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = (string) $currency;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description = null)
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
