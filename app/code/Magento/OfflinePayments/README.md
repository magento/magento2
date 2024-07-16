# Magento_OfflinePayments module

This module implements the payment methods which do not require interaction with a payment gateway (so called offline methods).
These methods are the following:

- Bank transfer
- Cash on delivery
- Check / Money Order
- Purchase order

## Installation

Before installing this module, note that the Magento_OfflinePayments is dependent on the following modules:

- `Magento_Store`
- `Magento_Catalog`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_OfflinePayments module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_OfflinePayments module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:

- `checkout_index_index`
- `multishipping_checkout_billing`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

[Learn how to configure Offline Payment Methods](https://experienceleague.adobe.com/docs/commerce-admin/stores-sales/payments/payments.html#offline-payment-methods).
