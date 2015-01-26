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

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrCash\Controller\WebhookListenerController;
use ZfrCash\Factory\WebhookListenerControllerFactory;
use ZfrCash\Options\ModuleOptions;
use ZfrStripe\Client\StripeClient;

class WebhookListenerControllerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $stripeClient  = $this->getMock(StripeClient::class, [], [], '', false);
        $moduleOptions = new ModuleOptions();

        $serviceLocator = $this->getMock(ServiceLocatorInterface::class);
        $serviceLocator->expects($this->at(0))->method('get')->with(StripeClient::class)->willReturn($stripeClient);
        $serviceLocator->expects($this->at(1))->method('get')->with(ModuleOptions::class)->willReturn($moduleOptions);

        $pluginManager = $this->getMock(AbstractPluginManager::class, [], [], '', false);
        $pluginManager->expects($this->once())->method('getServiceLocator')->willReturn($serviceLocator);

        $factory  = new WebhookListenerControllerFactory();
        $instance = $factory->createService($pluginManager);

        $this->assertInstanceOf(WebhookListenerController::class, $instance);
    }
}