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
 * Represents an abstract discount
 *
 * A discount may be applied either to a customer or a subscription
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
abstract class AbstractDiscount
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var DateTime
     */
    protected $startedAt;

    /**
     * @var DateTime|null
     */
    protected $endAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->coupon = new Coupon();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Coupon $coupon
     */
    public function setCoupon(Coupon $coupon)
    {
        $this->coupon = $coupon;
    }

    /**
     * @return Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @param DateTime $startedAt
     */
    public function setStartedAt(DateTime $startedAt)
    {
        $this->startedAt = clone $startedAt;
    }

    /**
     * @return DateTime
     */
    public function getStartedAt()
    {
        return clone $this->startedAt;
    }

    /**
     * @param DateTime|null $endAt
     */
    public function setEndAt(DateTime $endAt = null)
    {
        if (null !== $endAt) {
            $endAt = clone $endAt;
        }

        $this->endAt = $endAt;
    }

    /**
     * @return DateTime|null
     */
    public function getEndAt()
    {
        return $this->endAt ? clone $this->endAt : null;
    }
}
