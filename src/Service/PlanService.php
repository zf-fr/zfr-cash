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

namespace ZfrCash\Service;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use ZfrCash\Entity\Plan;
use ZfrCash\StripePopulator\PlanPopulatorTrait;

/**
 * Plan service
 *
 * As it is highly unflexible to hardcode plans right into code, ZfrCash makes the assumptions that you
 * use the Stripe UI to create and manage your plans. Therefore, plans cannot be created right into ZfrCash,
 * however they are being synchronized automatically whenever you update them in Stripe (change their name, for
 * instance).
 *
 * However, you can use this service to update the plan (if you need to modify the features or limits, for instance)
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class PlanService 
{
    use PlanPopulatorTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectRepository
     */
    private $planRepository;

    /**
     * Update an existing plan
     *
     * Note that you MUST NOT update Stripe properties like id, amount or name, as those won't be reflected
     * to Stripe. You are only allowed to modify features or limits
     *
     * @param  Plan $plan
     * @return Plan
     */
    public function update(Plan $plan)
    {
        $this->objectManager->flush($plan);
    }

    /**
     * Sync a plan from a Stripe event
     *
     * @param  array $stripeEvent
     * @return void
     */
    public function syncFromStripeEvent(array $stripeEvent)
    {
        // Just to be sure we do not process unwanted event
        if (!in_array($stripeEvent['type'], ['plan.created', 'plan.updated', 'plan.deleted'], true)) {
            return;
        }

        $stripePlan = $stripeEvent['data']['object'];

        // First, let's try to retrieve the plan to see if it already exists in database
        $plan = $this->planRepository->findOneBy([
            'stripeId'  => $stripePlan['id'],
            'createdAt' => (new DateTime())->setTimestamp($stripePlan['created'])
        ]);

        if (null === $plan) {
            $plan = new Plan();
            $this->objectManager->persist($plan);
        }

        $this->populatePlanFromStripeResource($plan, $stripePlan);

        $this->objectManager->flush($plan);
    }
}