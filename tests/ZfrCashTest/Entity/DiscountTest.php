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
use ZfrCash\Entity\Coupon;
use ZfrCash\Entity\Discount;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers \ZfrCash\Entity\Coupon
 * @covers \ZfrCash\Entity\Discount
 */
class DiscountTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $coupon = new Coupon();
        $coupon->setCode('AZERTY');
        $coupon->setAmountOff(50);

        $discount = new Discount();
        $discount->setStartedAt(new DateTime());
        $discount->setEndAt(new DateTime());
        $discount->setCoupon($coupon);

        $this->assertEquals('AZERTY', $coupon->getCode());
        $this->assertEquals(50, $coupon->getAmountOff());

        $this->assertSame($coupon, $discount->getCoupon());
        $this->assertInstanceOf(DateTime::class, $discount->getStartedAt());
        $this->assertInstanceOf(DateTime::class, $discount->getEndAt());
    }
}
