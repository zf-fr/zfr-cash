# ZfrCash

[![Build Status](https://travis-ci.org/zf-fr/zfr-cash.svg?branch=master)](https://travis-ci.org/zf-fr/zfr-cash)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zf-fr/zfr-cash/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zf-fr/zfr-cash/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/zf-fr/zfr-cash/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/zf-fr/zfr-cash/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/zfr/zfr-cash/v/stable.svg)](https://packagist.org/packages/zfr/zfr-cash)
[![Total Downloads](https://poser.pugx.org/zfr/zfr-cash/downloads.svg)](https://packagist.org/packages/zfr/zfr-cash)

ZfrCash is a high level Zend Framework 2 module that simplify how you handle payments. It internally uses Stripe as
the payment gateway, using [ZfrStripe](https://github.com/zf-fr/zfr-stripe).

Here are a few features of what ZfrCash allows:

* Provides clean interfaces to represent a Stripe customer and a billable object (object that could receive a subscription).
* Clean services to create customers, cards, discounts, plans and subscriptions.
* Provides a basic listener that keep in sync subscriptions, cards and discounts.
* Easily extensible through events

## Dependencies

* ZF2: >= 2.2
* Doctrine ORM: >= 2.5 (we are using some new features that only ship with Doctrine ORM 2.5)
* DoctrineORMModule: >= 0.9
* ZfrStripe

While we only use Doctrine Common interfaces, I suspect it won't work with Doctrine ODM as it needs things like entity resolvers.

## Installation

To install ZfrCash, use composer:

```sh
php composer.phar require zfr/zfr-cash:~1.0
```

Enable ZfrCash in your `application.config.php`, then copy the file
`vendor/zfr/zfr-cash/config/zfr_cash.local.php.dist` to the
`config/autoload` directory of your application (don't forget to remove the
`.dist` extension from the file name!).

## Key concepts

Before diving into ZfrCash, you need to be familiar with some concepts that are used throughout this module:

* Customer: a Customer (any object that implements `ZfrCash\Entity\CustomerInterface`) is a customer in sense of
Stripe. A customer is associated to a Stripe customer identifier, a credit card (optional) and a discount (optional).

* Billable object: once you have a customer, you can create one or multiple recurring subscriptions. ZfrCash
introduces the concept of a billable object (any object that implements the `ZfrCash\Entity\BillableInterface`
interface). A billable object is simply an object that holds a subscription (which, in turns, holds a payer - a customer -).

ZfrCash is flexible enough to allow a lot of different use cases. Here are multiple examples

### One subscription per user

If your business is based on one Stripe subscription per user (the user itself *subscribes* to a plan), then you
could make your user implements the two ZfrCash interfaces:

```php
use ZfrCash\Entity\BillableInterface;
use ZfrCash\Entity\BillableTrait;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\CustomerTrait;

class User implements CustomerInterface, BillableInterface
{
    use CustomerTrait;
    use BillableTrait;
}
```

The two traits comes with sane default mapping.

### Multiple subscriptions

Stripe supports multiple subscriptions, and ZfrCash makes it easy to support this use case. For instance, you may
 want to price per project, each new project resulting in a new, separate subscription (but paid by the same person).
 In this case, the billable object will be the project, will the user will stay the Customer.

The User now only implements CustomerInterface:

```php
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\CustomerTrait;

class User implements CustomerInterface
{
    use CustomerTrait;
}
```

While the project implements the BillableInterface:

```php
use ZfrCash\Entity\BillableInterface;
use ZfrCash\Entity\BillableTrait;

class Project implements BillableInterface
{
    use BillableTrait;
}
```

## Usage

### Configuration

While ZfrCash tries to do as much as possible automatically, it requires some configuration on your end.

#### Module config

The first thing you need is to copy the `zfr_cash.local.php.dist` file to your `application/autoload` folder. There
is one mandatory option:

* `object_manager`: if you are using Doctrine ORM, there are great chances that you should specify `doctrine.entitymanager.orm_default`.

#### Specify the Doctrine resolver

In your application, add the following config:

```php
return [
    'doctrine' => [
        'entity_resolver' => [
            'orm_default' => [
                'resolvers' => [
                    CustomerInterface::class => YourCustomerClass::class,
                    BillableInterface::class => YourBillableClass::class
                ]
            ]
        ]
    ]
]
```

This actually maps the two ZfrCash interfaces to concrete implementations in your code.

#### Implementing repositories interface

For ZfrCash to work properly, you must create two custom Doctrine repositories:

* The repository for the class implementing `CustomerInterface` must implements `ZfrCash\Repository\CustomerRepositoryInterface`.
* The repository for the class implementing `BillableInterface` must implements `ZfrCash\Repository\BillableRepositoryInterface`

To create a custom repository, you need to set the `repositoryClass` option in your Doctrine 2 mapping. Here is an
example for a User class implementing the `CustomerInterface`:

```php
/**
 * @ORM\Entity(repositoryClass="User\Repository\UserRepository")
 */
class User implements CustomerInterface
{
	use CustomerTrait;
}
```

Where your repository implements the given interface:

```php
namespace User\Repository;

use Doctrine\ORM\EntityRepository;
use ZfrCash\Repository\CustomerRepositoryInterface;

class UserRepository extends EntityRepository implements CustomerRepositoryInterface
{
	public function findOneByStripeId($stripeId)
	{
		return $this->findOneBy(['stripeId' => $stripeId]);
	}
}
```

Do the same for the billable object (that may be actually the same repository if you use the one subscription per
customer architecture).

#### Setting the routes

By default, ZfrCash creates two routes that will listen to some events triggered by Stripe.

* `/stripe/test-listener`: will listen to test events
* `/stripe/live-listener`: will listen to live events

You can change the URL if you want, but those are sane defaults. ZfrCash is smart enough to detect the if the
incoming events match the given route. For instance, if a live event reaches a test listener, it will do
nothing (it uses the API key prefix to see if it matches).

Whenever ZfrCash receives an event from Stripe, it will do an additional API request to Stripe to validate the
webhook and ensure no one is trying to hack you. However, if your application run into a controlled environment (for
instance if you filter incoming requests by Stripe IPs), you can disable this behaviour by setting the
`validate_webhooks` option to false in your config:

```php
return [
	'zfr_cash' => [
		'validate_webhooks' => false
	]
];
```

By default, ZfrCash will listen to the following events, and take some actions:

* `customer.discount.created`, `customer.discount.updated`, `customer.discount.deleted`: ZfrCash synchronizes the
various discount events (both for subscription discounts and customer discounts). This means you can create/update/remove
a discount right into the Stripe UI, and it will be automatically persisted into your database. Alternatively, you can
also create/update/remove a discount in your code, using services.
* `customer.card.updated`: since January 2015, Stripe can automatically updates your Stripe customers' card, without
them to manually update their card. As a consequence, ZfrCash automatically update the card. Alternatively, you can
create or remove a card into your code.
* `customer.subscription.updated`, `customer.subscription.deleted`: when your subscription renews, ZfrCash
automatically updates the various subscription properties (like `current_period_start` and `current_period_end`).
It also deletes the subscription for your database if it is deleted from Stripe. Alternatively, you can create,
update and remove subscriptions into your code using services.
* `plan.created`, `plan.updated`, `plan.deleted`: whenever you create, update or delete a plan from Stripe, it is
automatically added into your database.

If you don't want ZfrCash to keep your database in sync, you can disable this behaviour by setting the
`register_listeners` to false in your config:

```php
return [
	'zfr_cash' => [
		'register_listeners' => false
	]
];
```

#### Configuring Stripe to send events

Now that ZfrCash is configured, we need to configure Stripe so that it correctly sends the events into your
application. To do that, go to your Stripe dashbord.

In the top right screen, click on your account name, and "Account Settings". Open the "Webhooks" tab.

You can add either test listener or live listener. Click on "Add URL". Carefully select the right mode, and
enter the right URL. For instance, for Live URL: `https://www.mysite.com/stripe/live-listener`.

We do not recommend you to send all the events, as Stripe is quite chatty, it can add some stress to your server.
At the minimum, we recommend you to listen to the events that ZfrCash listen. Some other interesting events
include: `invoice.payment_succeeded`, `invoice.payment_failed`... In a later section, you will learn how you can
hook your own code.

Before going to production, be sure to try your code in test mode, and see if ZfrCash reacts correctly in your case.

### Listening to other Stripe Events

While ZfrCash only provides behaviour for basic events, Stripe sends a lot of other events. For instance, you may
want to send an email whenever a payment for a recurring payment fail. To do that, you must listen to the
`ZfrCash\Event\WebhookEvent::WEBHOOK_RECEIVED` event.

The first step is to create your listener class:

```php
namespace Application\Listener;

use ZfrCash\Controller\WebhookListenerController;
use ZfrCash\Event\WebhookEvent;

class CustomStripeListener extends AbstractListenerAggregate
{
	public function attachAggregate(EventManagerInterface $eventManager)
	{
		$sharedManager = $eventManager->getSharedManager();
		$sharedManager->attach(WebhookListenerController::class, WebhookEvent::WEBHOOK_RECEIVED, [$this, 'handleStripeEvent']);
	}
	
	/**
	 * @param WebhookEvent $event
	 */
	public function handleStripeEvent(WebhookEvent $event)
	{
		$stripeEvent = $event->getStripeEvent(); // This is the full Stripe event
		
		switch ($stripeEvent['type']) {
			case 'invoice.payment_failed':
				// Do something...
				break;
		}
	}
}
```

Finally, you need to register your listener. In your Module.php class:

```php
public function onBootstrap(EventInterface $event)
{
    /* @var $application \Zend\Mvc\Application */
    $application    = $event->getTarget();
    $serviceManager = $application->getServiceManager();

    $eventManager = $application->getEventManager();
    $eventManager->attach(new CustomStripeListener());
}
```

### Using the CustomerService

You can retrieve the CustomerService using the `ZfrCash\Service\CustomerService` key in your service manager.

#### create

The customer service is a built-in service that allows you to create a Stripe customer. This service automatically
creates a Stripe customer on Stripe, and save the various properties in your database. You can optionally create a
customer with a card and/or discount in one call.

Most of the time, the customer will be your user class. That's why ZfrCash expects that you pass it an object
implementing `ZfrCash\Entity\CustomerInterface`. Here is a simple usage:

```php
class UserController extends AbstractActionController
{
	public function createAction()
	{
		// Create your user... that implements CustomerInterface
		$cardToken = $this->params()->fromQuery('card_token');
		$discount  = $this->params()->fromQuery('discount');
		
		$user = $this->customerService->create($user, [
			'card'     => $cardToken,
			'discount' => $discount,
			'email'    => $user->getEmail()
		]);
	}
}
```

Supported options are:

* `email`: set the email Stripe attribute
* `description`: set the description Stripe attribute
* `card`: can either be a card token (created using Stripe.JS) or a full hash containing card properties.
* `coupon`: a coupon to attach to the customer
* `metadata`: a key value that set the metadata Stripe attributes
* `idempotency_key`: a key that is used to prevent an operation for being executed twice

All of those properties are optional.

#### getByStripeId

If you have a customer Stripe ID, you can retrieve the full customer using a Stripe identifier:

```php
$customer = $this->customerService->getByStripeAd('cus_abc');
```

### Using the card service

The card service allows you to create and remove a card from a customer.

You can retrieve the CardService using the `ZfrCash\Service\CardService` key in your service manager.

#### attachToCustomer

If you want to replace the default credit card of a customer, you can use the `attachToCustomer` method. It will
automatically delete the previous card both from Stripe and your database, and attach the new one:

```php
$card = $cardService->attachToCustomer($customer, $cardToken);

// $card is the new card
```

The second parameter can either be a card token (created using Stripe.JS) or a hash of card attributes.

#### remove

You can remove a card (both from Stripe and your database) using the `remove` method:

```php
$cardService->remove($card);
```

### Using the subscription service

The subscription service is used to create, update and remove any subscription.

You can retrieve the SubscriptionService using the `ZfrCash\Service\SubscriptionService` key in your service manager.

#### create

The main operation is to create a subscription. A subscription is paid by a subscription, for a billable resource
(which may be the same, if you have one subscription per customer). The method accepts a customer, a billable, a
plan, and options. Supported options are:

* `tax_percent`: allow to set a tax that will be applied in addition of normal plan price
* `quantity`: set a quantity for the plan
* `coupon`: set a coupon for the given subscription only
* `trial_end`: a DateTime that represents that allows to manually set an trial date
* `application_fee_percent`: if you are creating subscription on behalf of other through Stripe Connect
* `billing_cycle_anchor`: a DateTime that defines when to start the recurring payments
* `metadata`: any pair of metadata
* `idempotency_key`: a key that is used to prevent an operation for being executed twice

For instance:

```php
$subscription = $subscriptionService->create($customer, $billable, $plan, [
	'quantity'  => 2,
	'trial_end' => (new DateTime())->modify('+7 days')
]);
```

Internally, ZfrCash will create the subscription on Stripe, save it in your database, and make the different
connections between the payer, the subscription and the billable resource.

> Note: the `idempotency_key` is a new feature that Stripe recently added, and that ZfrCash already supports. Basically,
it allows to prevent an operation from being executed twice. For instance, let's say that you are using the subscription
service to create a subscription in a delayed job executed by a worker. The job is executed, the subscription service
correctly create the subscription (and the customer starts paying), but the job just fails after that for any reason (a
HTTP call has timed out, the server has been shut down...). The job is therefore automatically reinserted for later
processing... but the problem is that the subscription will be created again, and your customer will pay twice! To avoid
this issue, you can pass an `idempotency_key` (that can be anything, but in this example, the unique identifier of the
job is a good candidate). During 24 hours, if you try to create a subscription with the exact same idempotency key,
Stripe will return the exact same response, without create a new subscription each time!

#### cancel

You can cancel a subscription through the service. The method accepts an optional second parameter (false by default),
that allows to cancel the subscription at the end of the current period, instead of stopping it now (the default).

If the cancel is set to cancel the subscription now, it will automatically remove it from your database.

#### modifyPlan / modifyQuantity

You can also modify an existing subscription, either the plan or the quantity:

```php
// Update the plan
$anotherPlan = ...;
$subscription = $this->subscriptionService->modifyPlan($subscription, $anotherPlan);

// Update the quantity
$subscription = $this->subscriptionService->modifyQuantity($subscription, 4);
```

As always, ZfrCash will make the API call to Stripe, and update your database.

#### getters

The service also has several getters you can use:

* `getById`: get a subscription by its ID
* `getByStripeId`: get the subscription by its Stripe ID
* `getByCustomer`: get all the subscriptions for the given customer

### Using the plan service

The plan service allows you to update and remove a plan.

You can retrieve the PlanService using the `ZfrCash\Service\PlanService` key in your service manager.

#### update

You can use this method to update the plan name (not recommended) or the metadata plan. The metadata plan (up
to 20 key/values) can be used for things like having plan limits encoded in the API.

#### deactivate

ZfrCash does not allow to remove plans from database. Instead, it justs allow to deactivate a plan. The reason is that
you may still have subscription link to a plan, and removing it may corrupt your data.

Instead, when you deactivate a plan (or when you remove it from Stripe and that ZfrCash handles the event), it is just
soft-deleted.

#### syncFromStripe

When you use ZfrCash for the first time, you may have plans already created on Stripe. Instead of manually creating
all your plans into your database, you can use the `syncFromStripe` method. It will retrieve all the created plans
from your Stripe account, and create them locally in your database.

> Whenever a new plan is imported or created from an event, it is deactivated by default (to avoid leaking and make
a newly created plan already visible by your customers). You are responsible for activating it yourself.

### Using the customer discount service

The customer discount service allows you to handle customer discount (in Stripe, a discount created for a customer
will be applied to ALL recurring payments).

You can retrieve the CustomerDiscountService using the `ZfrCash\Service\CustomerDiscountService` key in your service
manager.

#### createForCustomer

You can create a new discount for a customer by passing a coupon code. It will automatically make the call to Stripe,
and update your database. If the customer already has a coupon, it will update it instead:

```php
$discount = $this->customerDiscountService->createForCustomer($customer, 'COUPON_15');
```

#### changeCoupon

You can update the coupon of an existing discount using the `changeCoupon` method. It will automatically make the
API call to Stripe, and update your database:

```php
$discount = $this->customerDiscountService->changeCoupon($discount, 'COUPON_30');
```

#### removeCoupon

Finally, you can remove an existing coupon. This will delete it from Stripe and your database:

```php
$this->customerDiscountService->remove($discount);
```

#### getters

Finally, the service offers several getters you can use:

* `getById`: get the discount by its id
* `getByCustomer`: get the discount of a given customer

### Using the subscription discount service

The subscription discount service allows you to handle subscription discount (in Stripe, a discount created for a
subscription will ONLY be applied for recurring payments of a given subscription).

You can retrieve the SubscriptionDiscountService using the `ZfrCash\Service\SubscriptionDiscountService` key in
your service manager.

#### createForSubscription

You can create a new discount for a subscription by passing a coupon code. It will automatically make the call to
Stripe, and update your database. If the subscription already has a coupon, it will update it instead:

```php
$discount = $this->subscriptionDiscountService->createForSubscription($customer, 'COUPON_15');
```

#### changeCoupon

You can update the coupon of an existing discount using the `changeCoupon` method. It will automatically make the
API call to Stripe, and update your database:

```php
$discount = $this->subscriptionDiscountService->changeCoupon($discount, 'COUPON_30');
```

#### removeCoupon

Finally, you can remove an existing coupon. This will delete it from Stripe and your database:

```php
$this->subscriptionDiscountService->remove($discount);
```

#### getters

Finally, the service offers several getters you can use:

* `getById`: get the discount by its id
* `getBySubscription`: get the discount of a given subscription