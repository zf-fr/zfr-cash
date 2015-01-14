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
 * Interface for a Stripe customer where VAT is taken into account
 *
 * This interface extends the CustomerInterface and adds the ability to automatically charge VAT for
 * european countries, according to new 2015 regulation rules
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
interface VatCustomerInterface extends CustomerInterface
{
    /**
     * Set the VAT number (or null to remove it)
     *
     * @param  string|null $vatNumber
     * @return string|null
     */
    public function setVatNumber($vatNumber = null);

    /**
     * Get the VAT number
     *
     * @return string|null
     */
    public function getVatNumber();

    /**
     * Set the VAT country (2 letters ISO-code, or null to remove it)
     *
     * @param  string|null $vatCountry
     * @return string|null
     */
    public function setVatCountry($vatCountry);

    /**
     * Get the VAT country (2 letters ISO-code)
     *
     * @return string|null
     */
    public function getVatCountry();
}
