# ZfrCash

ZfrCash is a high level Zend Framework 2 module that simplify how you handle payments. It internally uses
Stripe as the payment gateway, using [ZfrStripe](https://github.com/zf-fr/zfr-stripe).

## Installation

To install ZfrCash, use composer:

```sh
php composer.phar require zfr/zfr-cash:~1.0
```

Enable ZfrCash in your `application.config.php`, then copy the file
`vendor/zfr/zfr-cash/config/zfr_cash.local.php.dist` to the
`config/autoload` directory of your application (don't forget to remove the
`.dist` extension from the file name!).

## Features

* Clean interfaces to represent a billable object and a Stripe customer
* Services to create subscriptions, plans and customers
* Comes with sane defaults to answer to common Stripe events (like `customer.subscription.updated`)
* Can optionally add a tax for each subscription based on Stripe metadata (for EU companies that have to handle VAT)
* Maintain bi-directional data consistency

## Usage

### Configuration

You first need to configure ZfrCash, by copying the `zfr_cash.local.php.dist` file to your `application/autoload`
folder. There are some options that you can interact with, but the most important ones are:

* `object_manager`: ZfrCash is built around Doctrine interfaces. If you are using Doctrine ORM, there are great
chances that you should specify `doctrine.entitymanager.orm_default`.
* `customer_class`: this is the FQCN of your class that implements the `StripeCustomerInterface` interface.
* `validate_webhooks`: for security reasons, Stripe recommends to validate incoming Stripe webhooks by fetching the
original event, at the expense of one additional API call. We recommend you to stay with the default value, but you
may consider disabling the validation if you are doing some IP filtering to only allow Stripe IPs.
* `register_listeners`: this option automatically registers listeners for some specific Stripe events. For instance,
whenever a `customer.subscription.updated` event is received, ZfrCash will automatically update various properties like
`currentPeriodStart` and `currentPeriodEnd`. Of course, you must make sure to have properly configured your
Stripe account to [send webhooks](https://stripe.com/docs/webhooks).
* VAT: there are a few options that are related to VAT. An entire section is dedicated to those properties later
in the documentation.

#### Specify the Doctrine resolver



### Webhook listener

ZfrCash comes with a controller


* customer.discount.created
* customer.discount.updated
* customer.discount.deleted
* customer.subscription.updated
* plan.created
* plan.updated
* plan.deleted
* invoice.created
* invoice.payment_succeeded
* invoice.payment_failed
* invoice.updated