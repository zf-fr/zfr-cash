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
 * Interface for a Stripe customer
 *
 * Most often, your user class will implement this interface. However, you may also decide to create
 * a new class that implements this interface, in order to keep all the "billing-related" information in
 * another class
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
interface CustomerInterface
{
    /**
     * Set the Stripe customer identifier
     *
     * @param  string $stripeId
     * @return void
     */
    public function setStripeId($stripeId);

    /**
     * Get the Stripe customer identifier
     *
     * @return string
     */
    public function getStripeId();

    /**
     * Set the default card for the customer
     *
     * @param  Card|null $card
     * @return Card|null
     */
    public function setCard(Card $card = null);

    /**
     * Get the default card for the customer
     *
     * @return Card|null
     */
    public function getCard();

    /**
     * Set the discount for the customer (or remove it by setting null)
     *
     * @param  Discount|null $discount
     * @return void
     */
    public function setDiscount(Discount $discount = null);

    /**
     * Get the discount (if any) attached to the customer
     *
     * @return Discount|null
     */
    public function getDiscount();
}
