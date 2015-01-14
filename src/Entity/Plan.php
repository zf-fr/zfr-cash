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
use ZfrCash\Exception\InvalidArgumentException;

/**
 * Wrapper around a Stripe plan
 *
 * This entity encapsulates most Stripe attributes, but adds its own set of properties. For instance, it
 * supports a "features" array that you can display in your pricing pages, as well as a searchable "limits"
 * collection that you can use it if you need to attribute a specific billable resource to a plan according
 * to some pre-defined limits (like a maximum number of private repositories)
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class Plan
{
    const INTERVAL_DAY   = 'day';
    const INTERVAL_WEEK  = 'week';
    const INTERVAL_MONTH = 'month';
    const INTERVAL_YEAR  = 'year';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $stripeId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $interval;

    /**
     * @var int
     */
    protected $intervalCount;

    /**
     * @var int|null
     */
    protected $trialPeriodDays;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var array
     */
    protected $features = [];

    /**
     * @var array|\Doctrine\Common\Collections\Collection
     */
    protected $limits = [];

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->limits = new ArrayCollection();
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @param  string $interval
     * @throws InvalidArgumentException
     */
    public function setInterval($interval)
    {
        $validIntervals = [self::INTERVAL_DAY, self::INTERVAL_WEEK, self::INTERVAL_MONTH, self::INTERVAL_YEAR];

        if (!in_array($interval, $validIntervals, true)) {
            throw new InvalidArgumentException(sprintf(
                'An invalid plan interval has been given ("%s")',
                $interval
            ));
        }

        $this->interval = (string) $interval;
    }

    /**
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $intervalCount
     */
    public function setIntervalCount($intervalCount)
    {
        $this->intervalCount = (int) $intervalCount;
    }

    /**
     * @return int
     */
    public function getIntervalCount()
    {
        return $this->intervalCount;
    }

    /**
     * @param int|null $trialPeriodDays
     */
    public function setTrialPeriodDays($trialPeriodDays = null)
    {
        $this->trialPeriodDays = (int) $trialPeriodDays;
    }

    /**
     * @return int|null
     */
    public function getTrialPeriodDays()
    {
        return $this->trialPeriodDays;
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

    /**
     * @param  string $feature
     * @return bool
     */
    public function hasFeature($feature)
    {
        return in_array($feature, $this->features, true);
    }

    /**
     * @param array $features
     */
    public function setFeatures(array $features)
    {
        $this->features = $features;
    }

    /**
     * @param  string $feature
     * @return void
     */
    public function addFeature($feature)
    {
        $this->features[] = $feature;
    }

    /**
     * @param  string $feature
     * @return void
     */
    public function removeFeature($feature)
    {
        $key = array_search($feature, $this->features, true);

        if ($key !== false) {
            unset($this->features[$key]);
        }
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param array $limits
     */
    public function setLimits(array $limits = [])
    {
        $this->limits->clear();

        foreach ($limits as $key => $value) {
            $limit = new PlanLimit($this);
            $limit->setKey($key);
            $limit->setValue($value);

            $this->limits->add($limit);
        }
    }

    /**
     * @return array
     */
    public function getLimits()
    {
        return $this->limits->toArray();
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }
}
