# Magento_Persistent module

This module enables setting a long-term cookie containing internal id (random hash - to exclude brute
force) of persistent session for customer. Persistent session data is kept in DB - so it's not deleted in some days and is kept for
as much time as we need. DB session keeps customerId + some data from real customer session that we want to sync (e.g.
num items in shopping cart). For registered customer this info is synced to persistent session if choose "Remember me"
checkbox during first login.

## Installation

Before installing this module, note that the Magento_Persistent is dependent on the following modules:
- `Magento_Checkout`
- `Magento_PageCache`

The Magento_Persistent module creates the `persistent_session` table in the database.

This module modifies the following tables in the database:
- `quote` - adds column `is_persistent`

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Persistent module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Persistent module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Events

The module dispatches the following events:

#### Controller

- `persistent_session_expired` event in the `\Magento\Persistent\Controller\Index\UnsetCookie::execute` method

#### Observer

- `persistent_session_expired` event in the `\Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver::execute` method

For information about an event in Magento 2, see [Events and observers](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

More information can get at articles:
- [Persistent Shopping Cart](https://docs.magento.com/user-guide/configuration/customers/persistent-shopping-cart.html)
- [Persistent Cart](https://docs.magento.com/user-guide/sales/cart-persistent.html)

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:
- `persistent_clear_expired` - clear expired persistent sessions

[Learn how to configure and run cron in Magento.](http://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html).
