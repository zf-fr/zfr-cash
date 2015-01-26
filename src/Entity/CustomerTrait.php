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

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for a Stripe customer
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @ORM\MappedSuperclass
 */
trait CustomerTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $stripeId;

    /**
     * @var Card|null
     *
     * @ORM\OneToOne(targetEntity="ZfrCash\Entity\Card", inversedBy="owner")
     */
    protected $card;

    /**
     * @var Discount|null
     *
     * @ORM\OneToMany(targetEntity="ZfrCash\Entity\Discount", inversedBy="customer")
     */
    protected $discount;

    /**
     * @param string $stripeId
     */
    public function setStripeId($stripeId)
    {
        $this->stripeId = (string) $stripeId;
    }

    /**
     * {@inheritDoc}
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * {@inheritDoc}
     */
    public function setCard(Card $card = null)
    {
        $this->card = $card;
    }

    /**
     * {@inheritDoc}
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Discount|null $discount
     */
    public function setDiscount(Discount $discount = null)
    {
        $this->discount = $discount;
    }

    /**
     * @return Discount|null
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Check if a customer is chargeable
     *
     * It returns true if the customer has a card that has not expired. Please note that even though the card
     * seems valid, it may be rejected during payment though
     *
     * @return bool
     */
    public function isChargeable()
    {
        return ($card = $this->getCard()) && !$card->isExpired();
    }
}
