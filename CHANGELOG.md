# Changelog

## 1.2.3

* Fix a bug where discount webhook were not correctly handled, which resulted in updates not being updated
* Fix a bug where subscription webhook were not correctly handled, which resulted in updates not being updated

## 1.2.2

* Fix logic for the `isExpired` method on card entity

## 1.2.1

* Fix a bug when using the method `getOneByCustomer` on card service

## 1.2.0

* Update mapping so that when fetching a subscription, plan and (eventual) discount are loaded eagerly instead of
being lazy-loaded. The reason is that most of the time, the subscription is fetched in order to be shown with all
the information linked to it.

## 1.1.1

* Revert uppercase for country code

## 1.1.0

* Add compatibility with the new `2015-02-18` Stripe API version. This change removes the "card" concept in favour
of a more abstract "source" concept. ZfrCash will automatically use the right calls based on your set API version.

## 1.0.0

* First official release

## 1.0.0-beta.4

* Updated the `CustomerTrait` to allow null values for Stripe ID. This is useful if you are using a unique user
table to hold the Stripe ID, but not all users have a Stripe ID

## 1.0.0-beta.3

* Fix mapping

## 1.0.0-beta.2

* Add a validator for VIES european codes

## 1.0.0-beta.1

* Initial release
