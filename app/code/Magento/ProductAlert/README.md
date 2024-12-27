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

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_ProductAlert module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_ProductAlert module.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:

- `catalog_product_view`
- `productalert_unsubscribe_email`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

More information can get at articles:

- [Product Alerts](https://experienceleague.adobe.com/docs/commerce-admin/inventory/configuration/product-alerts/alert-setup.html)
- [Product Alert Run Settings](https://experienceleague.adobe.com/docs/commerce-admin/inventory/configuration/product-alerts/alert-setup.html)

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `catalog_product_alert` - send product alerts to customers

[Learn how to configure and run cron in Magento.](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs.html).
