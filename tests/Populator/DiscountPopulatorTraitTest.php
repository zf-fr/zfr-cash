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
use ZfrCash\Entity\CustomerDiscount;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\Subscription;
use ZfrCash\Entity\SubscriptionDiscount;
use ZfrCash\Populator\DiscountPopulatorTrait;

class DiscountPopulatorTraitTest extends PHPUnit_Framework_TestCase
{
    public function testPopulatorWithCustomerDiscount()
    {
        $customer = $this->getMock(CustomerInterface::class);

        $discount = new CustomerDiscount();
        $discount->setCustomer($customer);

        $populator = $this->getMockForTrait(DiscountPopulatorTrait::class);

        $reflMethod = new \ReflectionMethod($populator, 'populateDiscountFromStripeResource');
        $reflMethod->setAccessible(true);

        $stripeDiscount = [
            'coupon' => [
                'id'          => 'FOO',
                'amount_off'  => 5,
                'currency'    => 'eur',
                'percent_off' => null
            ],
            'start' => time(),
            'end'   => time()
        ];

        $reflMethod->invoke($populator, $discount, $stripeDiscount);

        $this->assertEquals($stripeDiscount['coupon']['id'], $discount->getCoupon()->getCode());
        $this->assertEquals($stripeDiscount['coupon']['amount_off'], $discount->getCoupon()->getAmountOff());
        $this->assertEquals($stripeDiscount['coupon']['currency'], $discount->getCoupon()->getCurrency());
        $this->assertEquals($stripeDiscount['coupon']['percent_off'], $discount->getCoupon()->getPercentOff());
        $this->assertInstanceOf(DateTime::class, $discount->getStartedAt());
        $this->assertInstanceOf(DateTime::class, $discount->getEndAt());
        $this->assertSame($customer, $discount->getCustomer(), 'Make sure populator does not overwrite association');
    }

    public function testPopulatorWithSubscriptionDiscount()
    {
        $customer     = $this->getMock(CustomerInterface::class);
        $subscription = new Subscription();

        $discount = new SubscriptionDiscount();
        $discount->setCustomer($customer);
        $discount->setSubscription($subscription);

        $populator = $this->getMockForTrait(DiscountPopulatorTrait::class);

        $reflMethod = new \ReflectionMethod($populator, 'populateDiscountFromStripeResource');
        $reflMethod->setAccessible(true);

        $stripeDiscount = [
            'coupon' => [
                'id'          => 'FOO',
                'amount_off'  => 5,
                'currency'    => 'eur',
                'percent_off' => null
            ],
            'start' => time(),
            'end'   => time()
        ];

        $reflMethod->invoke($populator, $discount, $stripeDiscount);

        $this->assertEquals($stripeDiscount['coupon']['id'], $discount->getCoupon()->getCode());
        $this->assertEquals($stripeDiscount['coupon']['amount_off'], $discount->getCoupon()->getAmountOff());
        $this->assertEquals($stripeDiscount['coupon']['currency'], $discount->getCoupon()->getCurrency());
        $this->assertEquals($stripeDiscount['coupon']['percent_off'], $discount->getCoupon()->getPercentOff());
        $this->assertInstanceOf(DateTime::class, $discount->getStartedAt());
        $this->assertInstanceOf(DateTime::class, $discount->getEndAt());
        $this->assertSame($customer, $discount->getCustomer(), 'Make sure populator does not overwrite association');
        $this->assertSame($subscription, $discount->getSubscription(), 'Make sure populator does not overwrite association');
    }
}