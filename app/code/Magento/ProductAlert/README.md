# Magento_ProductAlert module

This module enables product alerts, which allow customers to sign up for emails about product price or stock status changes.

## Customer account — My Product Alerts

When price and/or stock alerts are enabled in configuration, customers see a **My Product Alerts** link in the account navigation. The page lists current price and stock subscriptions with per-row and unsubscribe-all actions. Old URLs `productalert/customer/price` and `productalert/customer/stock` redirect to the combined index. Email unsubscribe URLs stay compatible with existing links (`unsubscribe/price`, `priceAll`, `stockAll`, and stock single via `unsubscribe/email`).


## Installation

Before installing this module, note that this module is dependent on the following modules:

- `Magento_Catalog`
- `Magento_Customer`

The Magento_ProductAlert module creates the following tables in the database:

- `product_alert_price`
- `product_alert_stock`

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.

The Magento_ProductAlert module contains the recurring script. The script's modifications don't need to be manually reverted upon uninstallation.

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_ProductAlert module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_ProductAlert module.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:

- `catalog_product_view`
- `customer_account`
- `productalert_customer_index`
- `productalert_unsubscribe_email`

For more information about a layout, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

You can get more information at the following articles:

- [Product Alerts](https://experienceleague.adobe.com/en/docs/commerce-admin/inventory/configuration/product-alerts/alert-setup)
- [Product Alert Run Settings](https://experienceleague.adobe.com/en/docs/commerce-admin/inventory/configuration/product-alerts/alert-setup)

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `catalog_product_alert` - send product alerts to customers.

[Learn how to configure and run cron](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs).
