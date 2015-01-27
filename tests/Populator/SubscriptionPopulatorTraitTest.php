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
use ZfrCash\Entity\Subscription;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Populator\SubscriptionPopulatorTrait;

class SubscriptionPopulatorTraitTest extends PHPUnit_Framework_TestCase
{
    public function testPopulator()
    {
        $customer = $this->getMock(CustomerInterface::class);

        $subscription = new Subscription();
        $subscription->setPayer($customer);

        $populator = $this->getMockForTrait(SubscriptionPopulatorTrait::class);

        $reflMethod = new \ReflectionMethod($populator, 'populateSubscriptionFromStripeResource');
        $reflMethod->setAccessible(true);

        $stripeSubscription = [
            'id'                   => 'sub_abc',
            'quantity'             => 2,
            'tax_percent'          => 20,
            'current_period_start' => time(),
            'current_period_end'   => time(),
            'status'               => 'active',
            'trial_start'          => time(),
            'trial_end'            => time(),
            'ended_at'             => time(),
            'canceled_at'          => time()
        ];

        $reflMethod->invoke($populator, $subscription, $stripeSubscription);

        $this->assertEquals($stripeSubscription['id'], $subscription->getStripeId());
        $this->assertEquals($stripeSubscription['quantity'], $subscription->getQuantity());
        $this->assertEquals($stripeSubscription['tax_percent'], $subscription->getTaxPercent());
        $this->assertInstanceOf(DateTime::class, $subscription->getCurrentPeriodStart());
        $this->assertInstanceOf(DateTime::class, $subscription->getCurrentPeriodEnd());
        $this->assertEquals($stripeSubscription['status'], $subscription->getStatus());
        $this->assertInstanceOf(DateTime::class, $subscription->getTrialStart());
        $this->assertInstanceOf(DateTime::class, $subscription->getTrialEnd());
        $this->assertInstanceOf(DateTime::class, $subscription->getEndedAt());
        $this->assertInstanceOf(DateTime::class, $subscription->getCancelledAt());
        $this->assertSame($customer, $subscription->getPayer(), 'Make sure populator does not overwrite association');
    }
}