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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity that represents an invoice
 *
 * It replicates most Stripe invoice's attributes, but adds a few useful things like VAT number tied to
 * an invoice, an optional export URL (to a PDF for instance)
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class Invoice
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $stripeId;

    /**
     * @var CustomerInterface
     */
    protected $payer;

    /**
     * @var DateTime
     */
    protected $periodStart;

    /**
     * @var DateTime
     */
    protected $periodEnd;

    /**
     * @var int
     */
    protected $startingBalance;

    /**
     * @var int
     */
    protected $endingBalance;

    /**
     * @var int
     */
    protected $subtotal;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    protected $applicationFee;

    /**
     * @var int
     */
    protected $tax;

    /**
     * @var float
     */
    protected $taxPercent;

    /**
     * @var int
     */
    protected $amountDue;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var bool
     */
    protected $closed;

    /**
     * @var bool
     */
    protected $paid;

    /**
     * @var bool
     */
    protected $forgiven;

    /**
     * @var int
     */
    protected $attemptCount = 0;

    /**
     * @var LineItem[]|\Doctrine\Common\Collections\Collection
     */
    protected $lineItems;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $vatNumber;

    /**
     * @var string|null
     */
    protected $vatCountry;

    /**
     * @var string|null
     */
    protected $exportUrl;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lineItems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $stripeId
     */
    public function setStripeId($stripeId)
    {
        $this->stripeId = (string) $stripeId;
    }

    /**
     * @return string
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * @param CustomerInterface $payer
     */
    public function setPayer(CustomerInterface $payer)
    {
        $this->payer = $payer;
    }

    /**
     * @return CustomerInterface
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @param DateTime $periodStart
     */
    public function setPeriodStart(DateTime $periodStart)
    {
        $this->periodStart = clone $periodStart;
    }

    /**
     * @return DateTime
     */
    public function getPeriodStart()
    {
        return clone $this->periodStart;
    }

    /**
     * @param DateTime $periodEnd
     */
    public function setPeriodEnd(DateTime $periodEnd)
    {
        $this->periodEnd = clone $periodEnd;
    }

    /**
     * @return DateTime
     */
    public function getPeriodEnd()
    {
        return clone $this->periodEnd;
    }

    /**
     * @param int $startingBalance
     */
    public function setStartingBalance($startingBalance)
    {
        $this->startingBalance = (int) $startingBalance;
    }

    /**
     * @return int
     */
    public function getStartingBalance()
    {
        return $this->startingBalance;
    }

    /**
     * @param int $endingBalance
     */
    public function setEndingBalance($endingBalance)
    {
        $this->endingBalance = (int) $endingBalance;
    }

    /**
     * @return int
     */
    public function getEndingBalance()
    {
        return $this->endingBalance;
    }

    /**
     * @param int $subtotal
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = (int) $subtotal;
    }

    /**
     * @return int
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = (int) $total;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $applicationFee
     */
    public function setApplicationFee($applicationFee)
    {
        $this->applicationFee = (int) $applicationFee;
    }

    /**
     * @return int
     */
    public function getApplicationFee()
    {
        return $this->applicationFee;
    }

    /**
     * @param int $tax
     */
    public function setTax($tax)
    {
        $this->tax = (int) $tax;
    }

    /**
     * @return int
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $taxPercent
     */
    public function setTaxPercent($taxPercent)
    {
        $this->taxPercent = (float) $taxPercent;
    }

    /**
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->taxPercent;
    }

    /**
     * @param int $amountDue
     */
    public function setAmountDue($amountDue)
    {
        $this->amountDue = (int) $amountDue;
    }

    /**
     * @return int
     */
    public function getAmountDue()
    {
        return $this->amountDue;
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
     * @param bool $closed
     */
    public function setClosed($closed)
    {
        $this->closed = (bool) $closed;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $paid
     */
    public function setPaid($paid)
    {
        $this->paid = (bool) $paid;
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return $this->paid;
    }

    /**
     * @param bool $forgiven
     */
    public function setForgiven($forgiven)
    {
        $this->forgiven = (bool) $forgiven;
    }

    /**
     * @return bool
     */
    public function isForgiven()
    {
        return $this->forgiven;
    }

    /**
     * @param int $attemptCount
     */
    public function setAttemptCount($attemptCount)
    {
        $this->attemptCount = (int) $attemptCount;
    }

    /**
     * @return int
     */
    public function getAttemptCount()
    {
        return $this->attemptCount;
    }

    /**
     * @param LineItem[] $lineItems
     */
    public function setLineItems(array $lineItems)
    {
        foreach ($lineItems as $lineItem) {
            $lineItem->setInvoice($this);
        }

        $this->lineItems = $lineItems;
    }

    /**
     * @return LineItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems->toArray();
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

    /**
     * @param string|null $vatNumber
     */
    public function setVatNumber($vatNumber = null)
    {
        $this->vatNumber = $vatNumber;
    }

    /**
     * @return string|null
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * @param string|null $vatCountry
     */
    public function setVatCountry($vatCountry = null)
    {
        $this->vatCountry = $vatCountry;
    }

    /**
     * @return string|null
     */
    public function getVatCountry()
    {
        return $this->vatCountry;
    }

    /**
     * @param string $exportUrl
     */
    public function setExportUrl($exportUrl)
    {
        $this->exportUrl = (string) $exportUrl;
    }

    /**
     * @return string|null
     */
    public function getExportUrl()
    {
        return $this->exportUrl;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = clone $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return clone $this->createdAt;
    }
}
