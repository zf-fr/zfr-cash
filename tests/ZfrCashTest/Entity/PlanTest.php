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

namespace ZfrCashTest\Entity;

use DateTime;
use ZfrCash\Exception\InvalidArgumentException;
use ZfrCash\Entity\Plan;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers \ZfrCash\Entity\Plan
 */
class PlanTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $features = ['notifications', 'real-time'];

        $plan = new Plan();

        $plan->setAmount(500);
        $plan->setCurrency('usd');
        $plan->setCreatedAt(new DateTime());
        $plan->setFeatures($features);
        $plan->setInterval('week');
        $plan->setIntervalCount(2);
        $plan->setName('My Plan');
        $plan->setStripeId('my-plan');
        $plan->setTrialPeriodDays(15);
        $plan->setStatementDescription('Foo');
        $plan->setIsActive(true);
        $plan->setIsDeleted(false);

        $this->assertEquals(500, $plan->getAmount());
        $this->assertInstanceOf(DateTime::class, $plan->getCreatedAt());
        $this->assertEquals($features, $plan->getFeatures());
        $this->assertEquals('week', $plan->getInterval());
        $this->assertEquals(2, $plan->getIntervalCount());
        $this->assertEquals('My Plan', $plan->getName());
        $this->assertEquals('my-plan', $plan->getStripeId());
        $this->assertEquals(15, $plan->getTrialPeriodDays());
        $this->assertEquals('Foo', $plan->getStatementDescription());
        $this->assertTrue($plan->isActive());
        $this->assertFalse($plan->isDeleted());

        // Test the features check
        $this->assertTrue($plan->hasFeature('notifications'));
        $plan->removeFeature('notifications');
        $this->assertFalse($plan->hasFeature('notifications'));
        $this->assertTrue($plan->hasFeature('real-time'));
    }

    public function testThrowExceptionIfInvalidInterval()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $plan = new Plan();
        $plan->setInterval('invalid');
    }
}
