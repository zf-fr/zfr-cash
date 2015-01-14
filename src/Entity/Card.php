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
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class Card
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
    protected $customer;

    /**
     * @var string
     */
    protected $brand;

    /**
     * @var int
     */
    protected $expMonth;

    /**
     * @var int
     */
    protected $expYear;

    /**
     * @var string
     */
    protected $last4;

    /**
     * @var string
     */
    protected $country;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $stripeId
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
     * @param CustomerInterface $customer
     */
    public function setCustomer(CustomerInterface $customer)
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
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = (string) $brand;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param int $expMonth
     */
    public function setExpMonth($expMonth)
    {
        $this->expMonth = (int) $expMonth;
    }

    /**
     * @return int
     */
    public function getExpMonth()
    {
        return $this->expMonth;
    }

    /**
     * @param int $expYear
     */
    public function setExpYear($expYear)
    {
        $this->expYear = (int) $expYear;
    }

    /**
     * @return int
     */
    public function getExpYear()
    {
        return $this->expYear;
    }

    /**
     * @param string $last4
     */
    public function setLast4($last4)
    {
        $this->last4 = (string) $last4;
    }

    /**
     * @return string
     */
    public function getLast4()
    {
        return $this->last4;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = (string) $country;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
}
