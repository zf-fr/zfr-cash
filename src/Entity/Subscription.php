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

/**
 * Entity for a subscription
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class Subscription
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
     * @var Plan
     */
    protected $plan;

    /**
     * @var CustomerInterface
     */
    protected $payer;

    /**
     * @var SubscriptionDiscount|null
     */
    protected $discount;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $taxPercent;

    /**
     * @var DateTime
     */
    protected $currentPeriodStart;

    /**
     * @var DateTime
     */
    protected $currentPeriodEnd;

    /**
     * @var DateTime|null
     */
    protected $trialStart;

    /**
     * @var DateTime|null
     */
    protected $trialEnd;

    /**
     * @var DateTime|null
     */
    protected $cancelledAt;

    /**
     * @var DateTime|null
     */
    protected $endedAt;

    /**
     * @var string
     */
    protected $status;

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
     * @param Plan $plan
     */
    public function setPlan(Plan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * @return Plan
     */
    public function getPlan()
    {
        return $this->plan;
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
     * @param SubscriptionDiscount|null $discount
     */
    public function setDiscount(SubscriptionDiscount $discount = null)
    {
        $this->discount = $discount;
    }

    /**
     * @return SubscriptionDiscount|null
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (int) $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
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
     * @param DateTime $currentPeriodStart
     */
    public function setCurrentPeriodStart(DateTime $currentPeriodStart)
    {
        $this->currentPeriodStart = clone $currentPeriodStart;
    }

    /**
     * @return DateTime
     */
    public function getCurrentPeriodStart()
    {
        return clone $this->currentPeriodStart;
    }

    /**
     * @param DateTime $currentPeriodEnd
     */
    public function setCurrentPeriodEnd(DateTime $currentPeriodEnd)
    {
        $this->currentPeriodEnd = clone $currentPeriodEnd;
    }

    /**
     * @return DateTime
     */
    public function getCurrentPeriodEnd()
    {
        return clone $this->currentPeriodEnd;
    }

    /**
     * @param DateTime|null $trialStart
     */
    public function setTrialStart(DateTime $trialStart = null)
    {
        if (null !== $trialStart) {
            $trialStart = clone $trialStart;
        }

        $this->trialStart = $trialStart;
    }

    /**
     * @return DateTime|null
     */
    public function getTrialStart()
    {
        return $this->trialStart ? clone $this->trialStart : null;
    }

    /**
     * @param DateTime|null $trialEnd
     */
    public function setTrialEnd($trialEnd = null)
    {
        if (null !== $trialEnd) {
            $trialEnd = clone $trialEnd;
        }

        $this->trialEnd = $trialEnd;
    }

    /**
     * @return DateTime|null
     */
    public function getTrialEnd()
    {
        return $this->trialEnd ? clone $this->trialEnd : null;
    }

    /**
     * @param DateTime|null $cancelledAt
     */
    public function setCancelledAt(DateTime $cancelledAt = null)
    {
        if (null !== $cancelledAt) {
            $cancelledAt = clone $cancelledAt;
        }

        $this->cancelledAt = $cancelledAt;
    }

    /**
     * @return DateTime|null
     */
    public function getCancelledAt()
    {
        return $this->cancelledAt ? clone $this->cancelledAt : null;
    }

    /**
     * @param DateTime|null $endedAt
     */
    public function setEndedAt(DateTime $endedAt = null)
    {
        if (null !== $endedAt) {
            $endedAt = clone $endedAt;
        }

        $this->endedAt = $endedAt;
    }

    /**
     * @return DateTime|null
     */
    public function getEndedAt()
    {
        return $this->endedAt ? clone $this->endedAt : null;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = (string) $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isTrialing()
    {
        return $this->status === 'trialing';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * @return bool
     */
    public function isOnGrace()
    {
        return null !== $this->cancelledAt && null === $this->endedAt;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === 'canceled';
    }
}
