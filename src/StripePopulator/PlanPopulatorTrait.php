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
use ZfrCash\Entity\Plan;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
trait PlanPopulatorTrait
{
    /**
     * @param Plan  $plan
     * @param array $stripePlan
     */
    protected function populatePlanFromStripeResource(Plan $plan, array $stripePlan)
    {
        $plan->setStripeId($stripePlan['id']);
        $plan->setName($stripePlan['name']);
        $plan->setAmount($stripePlan['amount']);
        $plan->setCurrency($stripePlan['currency']);
        $plan->setInterval($stripePlan['interval']);
        $plan->setIntervalCount($stripePlan['interval_count']);
        $plan->setTrialPeriodDays($stripePlan['trial_period_days']);
        $plan->setCreatedAt((new DateTime())->setTimestamp($stripePlan['created']));
    }
}