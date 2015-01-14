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

namespace ZfrCash\StripePopulator;

use DateTime;
use ZfrCash\Entity\Subscription;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
trait SubscriptionPopulatorTrait
{
    /**
     * Populate a Subscription object from Stripe resource data
     *
     * @param  Subscription $subscription
     * @param  array        $stripeSubscription
     * @return void
     */
    protected function populateSubscriptionFromStripeResource(Subscription $subscription, array $stripeSubscription)
    {
        $subscription->setStripeId($stripeSubscription['id']);
        $subscription->setQuantity($stripeSubscription['quantity']);
        $subscription->setTaxPercent($stripeSubscription['tax_percent']);
        $subscription->setCurrentPeriodEnd((new DateTime())->setTimestamp($stripeSubscription['current_period_start']));
        $subscription->setCurrentPeriodEnd((new DateTime())->setTimestamp($stripeSubscription['current_period_end']));
        $subscription->setStatus($stripeSubscription['status']);

        if (null !== $stripeSubscription['trial_start']) {
            $subscription->setTrialStart((new DateTime())->setTimestamp($stripeSubscription['trial_start']));
            $subscription->setTrialEnd((new DateTime())->setTimestamp($stripeSubscription['trial_end']));
        }

        if (null !== $stripeSubscription['ended_at']) {
            $subscription->setEndedAt((new DateTime())->setTimestamp($stripeSubscription['ended_at']));
        }

        if (null !== $stripeSubscription['canceled_at']) {
            $subscription->setCancelledAt((new DateTime())->setTimestamp($stripeSubscription['canceled_at']));
        }
    }
}