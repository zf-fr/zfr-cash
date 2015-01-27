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

namespace ZfrCashTest\Populator;

use DateTime;
use PHPUnit_Framework_TestCase;
use ZfrCash\Entity\Plan;
use ZfrCash\Entity\PlanMetadata;
use ZfrCash\Populator\PlanPopulatorTrait;

class PlanPopulatorTraitTest extends PHPUnit_Framework_TestCase
{
    public function testPopulator()
    {
        $plan = new Plan();

        // We add default metadata to ensure they will be overwritten
        $metadatum = new PlanMetadata();
        $metadatum->setPlan($plan);
        $metadatum->setKey('baz');
        $metadatum->setValue('boum');

        $plan->addMetadata($metadatum);

        $populator = $this->getMockForTrait(PlanPopulatorTrait::class);

        $reflMethod = new \ReflectionMethod($populator, 'populatePlanFromStripeResource');
        $reflMethod->setAccessible(true);

        $stripePlan = [
            'id'                => 'bronze',
            'name'              => 'Bronze',
            'amount'            => 2000,
            'currency'          => 'eur',
            'interval'          => 'month',
            'interval_count'    => 1,
            'trial_period_days' => 30,
            'created'           => time(),
            'metadata'          => [
                'foo' => 'bar',
                'bar' => 'baz'
            ]
        ];

        $reflMethod->invoke($populator, $plan, $stripePlan);

        $this->assertEquals($stripePlan['id'], $plan->getStripeId());
        $this->assertEquals($stripePlan['name'], $plan->getName());
        $this->assertEquals($stripePlan['amount'], $plan->getAmount());
        $this->assertEquals($stripePlan['currency'], $plan->getCurrency());
        $this->assertEquals($stripePlan['interval'], $plan->getInterval());
        $this->assertEquals($stripePlan['interval_count'], $plan->getIntervalCount());
        $this->assertEquals($stripePlan['trial_period_days'], $plan->getTrialPeriodDays());
        $this->assertInstanceOf(DateTime::class, $plan->getCreatedAt());

        $metadata = $plan->getMetadata();

        $this->assertCount(2, $metadata);

        $firstMetadata = array_shift($metadata);
        $this->assertSame($plan, $firstMetadata->getPlan());
        $this->assertEquals('foo', $firstMetadata->getKey());
        $this->assertEquals('bar', $firstMetadata->getValue());

        $secondMetadata = array_shift($metadata);
        $this->assertSame($plan, $secondMetadata->getPlan());
        $this->assertEquals('bar', $secondMetadata->getKey());
        $this->assertEquals('baz', $secondMetadata->getValue());
    }
}