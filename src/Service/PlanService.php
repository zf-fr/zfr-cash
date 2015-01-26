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
use ZfrCash\Populator\PlanPopulatorTrait;
use ZfrStripe\Client\StripeClient;
use ZfrStripe\Exception\NotFoundException as StripeNotFoundException;

/**
 * Service to handle plans
 *
 * Please note that for simplicity, this service does not allow to create plan. You should use
 * Stripe UI to create plans
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
     * @var StripeClient
     */
    private $stripeClient;

    /**
     * @param ObjectManager    $objectManager
     * @param ObjectRepository $planRepository
     * @param StripeClient     $stripeClient
     */
    public function __construct(
        ObjectManager $objectManager,
        ObjectRepository $planRepository,
        StripeClient $stripeClient
    ) {
        $this->objectManager  = $objectManager;
        $this->planRepository = $planRepository;
        $this->stripeClient   = $stripeClient;
    }

    /**
     * Update an existing plan
     *
     * @param  Plan $plan
     * @return Plan
     */
    public function update(Plan $plan)
    {
        // The first API call to Stripe is used to reset the Stripe metadata
        $this->stripeClient->updatePlan([
            'name'     => $plan->getName(),
            'metadata' => []
        ]);

        $metadata = [];

        foreach ($plan->getMetadata() as $metadatum) {
            $metadata[$metadatum->getKey()] = $metadatum->getValue();
        }

        if (!empty($metadata)) {
            $this->stripeClient->updatePlan([
                'metadata' => $metadata
            ]);
        }

        $this->objectManager->flush($plan);

        return $plan;
    }

    /**
     * Deactivate a plan (and optionally delete on Stripe)
     *
     * @param  Plan $plan
     * @param  bool $deleteOnStripe
     * @return Plan
     */
    public function deactivate(Plan $plan, $deleteOnStripe = false)
    {
        if ($deleteOnStripe) {
            try {
                $this->stripeClient->deletePlan([
                    'id' => $plan->getStripeId()
                ]);
            } catch (StripeNotFoundException $exception) {
                // The plan may have been removed manually from Stripe, but we still need to remove it from database
            }
        }

        $plan->setActive(false);

        $this->objectManager->flush();
    }

    /**
     * Sync all the plans from Stripe (useful when installing ZfrCash for the first time)
     *
     * If you have a lot of plan, this can be quite expensive, so be sure to do this only as an install process
     *
     * @return void
     */
    public function syncFromStripe()
    {
        $planIterator = $this->stripeClient->getPlansIterator();

        foreach ($planIterator as $stripePlan) {
            $createdAt = (new DateTime())->setTimestamp($stripePlan['created']);
            $plan      = $this->planRepository->findOneBy(['stripeId' => $stripePlan['id'], 'createdAt' => $createdAt]);

            if (null === $plan) {
                $plan = new Plan();
                $this->objectManager->persist($plan);
            }

            $this->populatePlanFromStripeResource($plan, $stripePlan);
        }

        $this->objectManager->flush();
    }

    /**
     * Get a plan by its ID
     *
     * @param  int $id
     * @return Plan|null
     */
    public function getById($id)
    {
        return $this->planRepository->find($id);
    }

    /**
     * Get a plan by its Stripe ID
     *
     * @param  string $stripeId
     * @param  bool   $active
     * @return Plan|null
     */
    public function getByStripeId($stripeId, $active = true)
    {
        return $this->planRepository->findOneBy(['stripeId' => (string) $stripeId, 'active' => (bool) $active]);
    }

    /**
     * Get all plans (by default only the active ones)
     *
     * @param  bool $onlyActive
     * @return Plan[]
     */
    public function getAll($onlyActive = true)
    {
        if ($onlyActive) {
            return $this->planRepository->findBy(['active' => true]);
        }

        return $this->planRepository->findAll();
    }

    /**
     * Sync (for creation and update) an existing plan
     *
     * @param  array $stripePlan
     * @return void
     */
    public function syncFromStripeResource(array $stripePlan)
    {
        if ($stripePlan['object'] !== 'plan') {
            return;
        }

        $createdAt = (new DateTime())->setTimestamp($stripePlan['created']);
        $plan      = $this->planRepository->findOneBy(['stripeId' => $stripePlan['id'], 'createdAt' => $createdAt]);

        if (null === $plan) {
            $plan = new Plan();
            $this->objectManager->persist($plan);
        }

        $this->populatePlanFromStripeResource($plan, $stripePlan);

        $this->objectManager->flush();
    }
}