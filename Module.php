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

namespace ZfrCash;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use ZfrCash\Listener\WebhookListener;
use ZfrCash\Options\ModuleOptions;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        /* @var $application \Zend\Mvc\Application */
        $application    = $event->getTarget();
        $serviceManager = $application->getServiceManager();

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceManager->get(ModuleOptions::class);

        if ($moduleOptions->getRegisterListeners()) {
            $eventManager = $application->getEventManager();
            $eventManager->attach($serviceManager->get(WebhookListener::class));
        }
    }
}
