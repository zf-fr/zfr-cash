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

namespace ZfrCashTest\Factory;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrCash\Entity\Card;
use ZfrCash\Factory\CardServiceFactory;
use ZfrCash\Options\ModuleOptions;
use ZfrCash\Service\CardService;
use ZfrStripe\Client\StripeClient;

class CardServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $moduleOptions  = new ModuleOptions(['object_manager' => 'my_object_manager']);
        $objectManager  = $this->getMock(ObjectManager::class);
        $stripeClient   = $this->getMock(StripeClient::class, [], [], '', false);
        $cardRepository = $this->getMock(ObjectRepository::class);

        $objectManager->expects($this->once())->method('getRepository')->with(Card::class)->willReturn($cardRepository);

        $serviceLocator = $this->getMock(ServiceLocatorInterface::class);

        $serviceLocator->expects($this->at(0))->method('get')->with(ModuleOptions::class)->willReturn($moduleOptions);
        $serviceLocator->expects($this->at(1))->method('get')->with('my_object_manager')->willReturn($objectManager);
        $serviceLocator->expects($this->at(2))->method('get')->with(StripeClient::class)->willReturn($stripeClient);

        $factory  = new CardServiceFactory();
        $instance = $factory->createService($serviceLocator);

        $this->assertInstanceOf(CardService::class, $instance);
    }
}