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

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_OfflinePayments module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_OfflinePayments module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:
- `checkout_index_index`
- `multishipping_checkout_billing`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

[Learn how to configure Offline Payment Methods](https://docs.magento.com/user-guide/payment/offline-payment-methods.html).
