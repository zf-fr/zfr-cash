# ZfrCash

ZfrCash is a high level Zend Framework 2 module that simplify how you handle payments. It internally uses
Stripe as the payment gateway, using [ZfrStripe](https://github.com/zf-fr/zfr-stripe).

Here are a few features of what ZfrCash allows:

* Provides clean interfaces to represent a Stripe customer and a billable object (object that could receive
a subscription).
* Clean services to create customers, cards, discounts, invoices, plans and subscriptions.
* Provides entities for some of Stripe resources, and keep them in sync with Stripe through webhooks.
* Easily extensible: you can attach listener to Stripe webhook event, and perform additional actions based on
webhooks.
* Auto VAT support: if you have a european business, ZfrCash takes care of all the VAT handling mess (for new
2015 regulations).
* Optional features: ZfrCash can optionally export to PDF all your closed invoices to Amazon S3 for durable storage.

## Dependencies

* ZF2: >= 2.2
* Doctrine ORM: >= 2.5 (we are using some new features that only ship with Doctrine ORM 2.5)
* ZfrStripe

While we only use Doctrine Common interfaces, I suspect it won't work with Doctrine ODM as it needs things like
entity resolvers.

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

* Customer: a Customer (any object that implements `ZfrCash\Entity\CustomerInterface`) is a customer in sense of Stripe.
A customer is associated to a Stripe customer identifier, a credit card (optional) and an optional discount.

* Billable object: once you have a customer, you can create one or multiple recurring subscriptions. ZfrCash introduces
the concept of a billable object (any object that implements the `ZfrCash\Entity\BillableInterface` interface). A
billable object is simply an object that holds a subscription (which, in turns, holds a payer - a customer -).

ZfrCash is flexible enough to allow a lot of different use cases. Here are multiple examples

### One subscription per user

If your business is based on one Stripe subscription per user (the user itself *subscribes* to a plan), then you could
make your user implements the two ZfrCash interfaces:

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

### One subscription per user with a different model for billing

The customer interface and billable interface adds several fields to your user model. If you want to keep your
models clearly separated, you could introduce a new model that will holds all the billings information, and your
user model only composes it:

UserBillingInfo class:

```php
use ZfrCash\Entity\BillableInterface;
use ZfrCash\Entity\BillableTrait;
use ZfrCash\Entity\CustomerInterface;
use ZfrCash\Entity\CustomerTrait;

class UserBillingInfo implements CustomerInterface, BillableInterface
{
    use CustomerTrait;
    use BillableTrait;
}
```

User class:

```php
class User
{
    /**
     * @ORM\OneToOne(targetEntity="UserBillingInfo")
     */
    protected $userBillingInfo;
}
```

### Multiple subscriptions

Stripe supports multiple subscriptions, and ZfrCash makes it easy to support this use case. For instance, you
may want to price per project, each new project resulting in a new, separate subscription (but paid by the same
person). In this case, the billable object will be the project, will the user will stay the Customer.

## Usage

### Configuration

While ZfrCash tries to do as much as possible automatically, it requires some configuration on your end.

#### Module config

The first thing you need is to copy the `zfr_cash.local.php.dist` file to your `application/autoload`
folder. There is one mandatory option:

* `object_manager`: if you are using Doctrine ORM, there are great chances that you should specify
`doctrine.entitymanager.orm_default`.

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