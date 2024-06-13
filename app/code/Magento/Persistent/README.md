# Magento_Persistent module

This module enables setting a long-term cookie containing an internal ID (random hash to prevent brute force) of persistent session for customers. Persistent session data is kept in the database, so it's not deleted after a few days and can be maintained for as long as needed. The database session stores the customer ID and some data from the real customer session that needs to be synchronized (e.g., number of items in the shopping cart). For registered customers, this information is synchronized to the persistent session if they select the "Remember me" checkbox during their first login.

## Installation

Before installing this module, note that this module is dependent on the following modules:

- `Magento_Checkout`
- `Magento_PageCache`

The Magento_Persistent module creates the `persistent_session` table in the database.

This module modifies the following tables in the database:

- `quote` - adds column `is_persistent`

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_Persistent module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Persistent module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Events

The module dispatches the following events:

#### Controller

- `persistent_session_expired` event in the `\Magento\Persistent\Controller\Index\UnsetCookie::execute` method

#### Observer

- `persistent_session_expired` event in the `\Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver::execute` method

For information about an event, see [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/#events).

### Layouts

For more information about a layout, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

You can get more information at the following articles:

- [Persistent Shopping Cart](https://experienceleague.adobe.com/en/docs/commerce-admin/config/customers/persistent-shopping-cart)
- [Persistent Cart](https://experienceleague.adobe.com/en/docs/commerce-admin/stores-sales/point-of-purchase/cart/cart-persistent)

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `persistent_clear_expired` - clear expired persistent sessions

[Learn how to configure and run cron](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs).
