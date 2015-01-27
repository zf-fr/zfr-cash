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

namespace ZfrCashTest\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrCash\Controller\WebhookListenerController;
use ZfrCash\Event\WebhookEvent;
use ZfrCash\Listener\WebhookListener;
use ZfrCash\Options\ModuleOptions;

class WebhookListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocator;

    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    /**
     * @var WebhookListener
     */
    private $webhookListener;

    public function setUp()
    {
        $this->serviceLocator = $this->getMock(ServiceLocatorInterface::class);
        $this->moduleOptions  = new ModuleOptions();

        $this->serviceLocator->expects($this->at(0))->method('get')->with(ModuleOptions::class)->willReturn($this->moduleOptions);

        $this->webhookListener = new WebhookListener($this->serviceLocator);
    }

    public function testAttachToRightEvents()
    {
        $sharedManager = $this->getMock(SharedEventManagerInterface::class);

        $eventManager = $this->getMock(EventManagerInterface::class);
        $eventManager->expects($this->once())->method('getSharedManager')->willReturn($sharedManager);

        $sharedManager->expects($this->once())
                      ->method('attach')
                      ->with(WebhookListenerController::class, WebhookEvent::WEBHOOK_RECEIVED, [$this->webhookListener, 'dispatchWebhook']);

        $this->webhookListener->attach($eventManager);
    }
}