# Magento_OfflinePayments module

This module implements the payment methods which do not require interaction with a payment gateway (so called offline methods).
These methods are the following:

- Bank transfer
- Cash on delivery
- Check / Money Order
- Purchase order

## Installation

Before installing this module, note that this module is dependent on the following modules:

- `Magento_Store`
- `Magento_Catalog`

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_OfflinePayments module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_OfflinePayments module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:

- `checkout_index_index`
- `multishipping_checkout_billing`

For more information about a layout, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

[Learn how to configure Offline Payment Methods](https://experienceleague.adobe.com/en/docs/commerce-admin/stores-sales/payments/payments#offline-payment-methods).
