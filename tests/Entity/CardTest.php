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

use PHPUnit_Framework_TestCase;
use ZfrCash\Entity\Card;

class CardTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function expirationProvider()
    {
        return [
            [
                'exp_month'  => 1,
                'exp_year'   => 2013,
                'is_expired' => true
            ],
            [
                'exp_month'  => 4,
                'exp_year'   => 2999,
                'is_expired' => false
            ]
        ];
    }

    /**
     * @dataProvider expirationProvider
     */
    public function testExpirationDate($expMonth, $expYear, $isExpired)
    {
        $card = new Card();
        $card->setExpMonth($expMonth);
        $card->setExpYear($expYear);

        $this->assertEquals($isExpired, $card->isExpired());
    }
}