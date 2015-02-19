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
use ZfrCash\Service\CardService;
use ZfrCash\Service\CustomerDiscountService;
use ZfrCash\Service\CustomerService;
use ZfrCash\Service\DiscountService;
use ZfrCash\Service\InvoiceService;
use ZfrCash\Service\PlanService;
use ZfrCash\Service\SubscriptionDiscountService;
use ZfrCash\Service\SubscriptionService;

/**
 * Listener that synchronizes Stripe data with our own database data
 *
 * This listener only listens to a small amount of meaningful events, but you are encouraged to listen to other
 * events (like "invoice.payment_successful" if you need to save an invoice)
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
        $sharedManager->attach(
            WebhookListenerController::class,
            WebhookEvent::WEBHOOK_RECEIVED,
            [$this, 'dispatchWebhook']
        );
    }

    /**
     * @internal
     * @param  WebhookEvent $event
     * @return string
     */
    public function dispatchWebhook(WebhookEvent $event)
    {
        $stripeEvent = $event->getStripeEvent();
        $eventType   = $stripeEvent['type'];

        switch ($eventType) {
            case 'customer.discount.created':
            case 'customer.discount.updated':
            case 'customer.discount.deleted':
                return $this->handleDiscountEvent($event->getStripeEvent());

            case 'customer.card.updated':
            case 'customer.source.updated': // Compatibility for Stripe API >= 2015-02-18
                return $this->handleCardEvent($event->getStripeEvent());

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                return $this->handleSubscriptionEvent($event->getStripeEvent());

            case 'plan.created':
            case 'plan.updated':
            case 'plan.deleted':
                return $this->handlePlanEvent($event->getStripeEvent());

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
    private function handleDiscountEvent(array $stripeEvent)
    {
        $stripeDiscount = $stripeEvent['data']['object'];

        if (null === $stripeDiscount['subscription']) {
            return $this->handleCustomerDiscountEvent($stripeEvent);
        } else {
            return $this->handleSubscriptionDiscountEvent($stripeEvent);
        }
    }

    /**
     * Handle a customer discount Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    private function handleCustomerDiscountEvent(array $stripeEvent)
    {
        /** @var CustomerDiscountService $customerDiscountService */
        $customerDiscountService = $this->serviceLocator->get(CustomerDiscountService::class);
        $stripeDiscount          = $stripeEvent['data']['object'];

        if ($stripeEvent['type'] === 'customer.discount.deleted') {
            /** @var CustomerService $customerService */
            $customerService = $this->serviceLocator->get(CustomerService::class);
            $customer        = $customerService->getOneByStripeId($stripeDiscount['customer']);

            if (null !== $customer && ($discount = $customer->getDiscount())) {
                $customerDiscountService->remove($discount);
            }
        }

        $customerDiscountService->syncFromStripeResource($stripeEvent);

        return 'Event has been properly processed';
    }

    /**
     * Handle a subscription discount Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    private function handleSubscriptionDiscountEvent(array $stripeEvent)
    {
        /** @var SubscriptionDiscountService $subscriptionDiscountService */
        $subscriptionDiscountService = $this->serviceLocator->get(SubscriptionDiscountService::class);
        $stripeDiscount              = $stripeEvent['data']['object'];

        if ($stripeEvent['type'] === 'customer.discount.deleted') {
            /** @var SubscriptionService $subscriptionService */
            $subscriptionService = $this->serviceLocator->get(SubscriptionService::class);
            $subscription        = $subscriptionService->getOneByStripeId($stripeDiscount['subscription']);

            if (null !== $subscription && ($discount = $subscription->getDiscount())) {
                $subscriptionDiscountService->remove($discount);
            }
        }

        $subscriptionDiscountService->syncFromStripeResource($stripeEvent);

        return 'Event has been properly processed';
    }

    /**
     * Handle a card Stripe event
     *
     * @internal
     * @param  array $stripeEvent
     * @return string
     */
    private function handleCardEvent(array $stripeEvent)
    {
        /** @var CardService $cardService */
        $cardService = $this->serviceLocator->get(CardService::class);
        $cardService->syncFromStripeResource($stripeEvent['data']['object']);

        return 'Event has been properly processed';
    }

    /**
     * Handle a subscription Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    private function handleSubscriptionEvent(array $stripeEvent)
    {
        /** @var SubscriptionService $subscriptionService */
        $subscriptionService = $this->serviceLocator->get(SubscriptionService::class);
        $subscriptionService->syncFromStripeResource($stripeEvent);

        return 'Event has been properly processed';
    }

    /**
     * Handle a plan Stripe event
     *
     * @param  array $stripeEvent
     * @return string
     */
    private function handlePlanEvent(array $stripeEvent)
    {
        /** @var PlanService $planService */
        $planService = $this->serviceLocator->get(PlanService::class);
        $stripePlan  = $stripeEvent['data']['object'];

        if ($stripeEvent['type'] === 'plan.deleted') {
            $plan = $planService->getByStripeId($stripePlan['id']);

            if (null !== $plan) {
                $planService->deactivate($plan);
            }
        } else {
            $planService->syncFromStripeResource($stripePlan);
        }

        return 'Event has been properly processed';
    }
}
