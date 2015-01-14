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
use ZfrCash\Entity\Discount;
use ZfrCash\Entity\Plan;
use ZfrCash\Entity\Subscription;
use ZfrCashTest\Asset\Customer;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers \ZfrCash\Entity\Subscription
 */
class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $plan     = new Plan();
        $payer    = new Customer();
        $discount = new Discount();

        $subscription = new Subscription();

        $subscription->setStripeId('sub_abc');
        $subscription->setPlan($plan);
        $subscription->setPayer($payer);
        $subscription->setDiscount($discount);
        $subscription->setQuantity(2);
        $subscription->setCurrentPeriodStart(new DateTime());
        $subscription->setCurrentPeriodEnd(new DateTime());
        $subscription->setTrialStart(new DateTime());
        $subscription->setTrialEnd(new DateTime());
        $subscription->setCancelledAt(new DateTime());
        $subscription->setEndedAt(new DateTime());
        $subscription->setStatus('active');

        $this->assertEquals('sub_abc', $subscription->getStripeId());
        $this->assertSame($plan, $subscription->getPlan());
        $this->assertSame($payer, $subscription->getPayer());
        $this->assertSame($discount, $subscription->getDiscount());
        $this->assertEquals(2, $subscription->getQuantity());
        $this->assertInstanceOf(DateTime::class, $subscription->getCurrentPeriodStart());
        $this->assertInstanceOf(DateTime::class, $subscription->getCurrentPeriodEnd());
        $this->assertInstanceOf(DateTime::class, $subscription->getTrialStart());
        $this->assertInstanceOf(DateTime::class, $subscription->getEndedAt());
        $this->assertInstanceOf(DateTime::class, $subscription->getCancelledAt());
        $this->assertInstanceOf(DateTime::class, $subscription->getEndedAt());
        $this->assertEquals('active', $subscription->getStatus());

        // Assert can remove various properties
        $subscription->setDiscount(null);
        $subscription->setTrialStart(null);
        $subscription->setTrialEnd(null);
        $subscription->setCancelledAt(null);
        $subscription->setEndedAt(null);

        $this->assertNull($subscription->getDiscount());
        $this->assertNull($subscription->getTrialStart());
        $this->assertNull($subscription->getTrialEnd());
        $this->assertNull($subscription->getCancelledAt());
        $this->assertNull($subscription->getEndedAt());
    }

    public function testDetectIsActiveStatus()
    {
        $subscription = new Subscription();
        $subscription->setStatus('active');

        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isTrialing());
        $this->assertFalse($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGrace());
    }

    public function testDetectIsTrialingStatus()
    {
        $subscription = new Subscription();
        $subscription->setStatus('trialing');

        $this->assertFalse($subscription->isActive());
        $this->assertTrue($subscription->isTrialing());
        $this->assertFalse($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGrace());
    }

    public function testDetectIsCancelledStatus()
    {
        $subscription = new Subscription();
        $subscription->setStatus('canceled');

        $this->assertFalse($subscription->isActive());
        $this->assertFalse($subscription->isTrialing());
        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGrace());
    }

    public function testDetectIsOnGraceStatus()
    {
        $subscription = new Subscription();
        $subscription->setStatus('active');

        $subscription->setCancelledAt(new DateTime());

        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isTrialing());
        $this->assertFalse($subscription->isCancelled());
        $this->assertTrue($subscription->isOnGrace());

        // When it is ended... it's not on grace anymore
        $subscription->setEndedAt(new DateTime());
        $this->assertFalse($subscription->isOnGrace());
    }
}
