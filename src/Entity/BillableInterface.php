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
 * Interface that represents something that is billable
 *
 * A billable object is somewhat related to a Stripe customer, in the sense that a Stripe customer is billed
 * for one or more resource. You may make your customer billable, or if you charge "per project", you may also
 * make each of your project a separate billable interface.
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
interface BillableInterface
{
    /**
     * Set the subscription
     *
     * @param  Subscription|null $subscription
     * @return void
     */
    public function setSubscription(Subscription $subscription = null);

    /**
     * Get the subscription
     *
     * @return Subscription
     */
    public function getSubscription();
}
