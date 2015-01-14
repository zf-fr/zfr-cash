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

namespace ZfrCash\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrCash\Controller\WebhookListenerController;
use ZfrCash\Event\WebhookEvent;
use ZfrCash\Options\ModuleOptions;
use ZfrCash\Service\DiscountService;
use ZfrCash\Service\InvoiceService;
use ZfrCash\Service\PlanService;
use ZfrCash\Service\SubscriptionService;
use ZfrStripe\Client\StripeClient;

/**
 * Listener that synchronizes Stripe data with our own database data
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 */
final class WebhookListener extends AbstractListenerAggregate
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    /**
     * Constructor
     *
     * NOTE: while this is not the best practice to inject the whole service locator, we mainly do that for
     * performance reasons, because this listener may use a lot of different services depending on the event type
     * received, and we do not want to create tons of services at each requests for nothing
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->moduleOptions  = $serviceLocator->get(ModuleOptions::class);
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $eventManager)
    {
        $sharedManager = $eventManager->getSharedManager();
        $sharedManager->attach(WebhookListenerController::class, WebhookEvent::WEBHOOK_RECEIVED, [$this, 'dispatchWebhook']);
    }

    /**
     * @internal
     * @param  WebhookEvent $event
     * @return string
     */
    public function dispatchWebhook(WebhookEvent $event)
    {
        switch ($event->getEventName()) {
            case 'customer.discount.created':
            case 'customer.discount.updated':
            case 'customer.discount.deleted':
                return $this->handleDiscountEvent($event->getEvent());

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                return $this->handleSubscriptionEvent($event->getEvent());

            case 'plan.created':
            case 'plan.updated':
            case 'plan.deleted':
                return $this->handlePlanEvent($event->getEvent());

            case 'invoice.created':
            case 'invoice.updated':
            case 'invoice.payment_succeeded':
            case 'invoice.payment_failed':
                return $this->handleInvoiceEvent($event->getEvent());

            default:
                return ''; // Any other event is not handled by default
        }
    }

    /**
     * Handle a discount Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    public function handleDiscountEvent(array $stripeEvent)
    {
        /** @var DiscountService $discountService */
        $discountService = $this->serviceLocator->get(DiscountService::class);
        $discountService->syncFromStripeEvent($stripeEvent);

        return 'Event has been properly processed';
    }

    /**
     * Handle a subscription Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    public function handleSubscriptionEvent(array $stripeEvent)
    {
        /** @var SubscriptionService $subscriptionService */
        $subscriptionService = $this->serviceLocator->get(SubscriptionService::class);
        $subscriptionService->syncFromStripeEvent($stripeEvent);

        return 'Event has been properly processed';
    }

    /**
     * Handle a plan Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    public function handlePlanEvent(array $stripeEvent)
    {
        /** @var PlanService $planService */
        $planService = $this->serviceLocator->get(PlanService::class);
        $planService->syncFromStripeEvent($stripeEvent);

        return 'Event has been properly processed';
    }

    /**
     * Handle an invoice Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    public function handleInvoiceEvent(array $stripeEvent)
    {
        /** @var InvoiceService $invoiceService */
        $invoiceService = $this->serviceLocator->get(InvoiceService::class);
        $invoiceService->syncFromStripeEvent($stripeEvent);

        return 'Event has been properly processed';
    }
}