# Magento_ProductAlert module

This module enables product alerts, which allow customers to sign up for emails about product price or stock status change.

## Installation

Before installing this module, note that the Magento_ProductAlert is dependent on the following modules:
- `Magento_Catalog`
- `Magento_Customer`

The Magento_ProductAlert module creates the following tables in the database:
- `product_alert_price`
- `product_alert_stock`

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.

The Magento_ProductAlert module contains the recurring script. Script's modifications don't need to be manually reverted upon uninstallation. 

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_ProductAlert module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_ProductAlert module.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:
- `catalog_product_view`
- `productalert_unsubscribe_email`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

More information can get at articles:
- [Product Alerts](https://docs.magento.com/user-guide/catalog/inventory-product-alerts.html)
- [Product Alert Run Settings](https://docs.magento.com/user-guide/catalog/inventory-product-alert-run-settings.html)

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:
- `catalog_product_alert` - send product alerts to customers

[Learn how to configure and run cron in Magento.](http://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html).

