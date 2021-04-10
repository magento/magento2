# Magento_Fedex module

This module implements the integration with the FedEx shipping carrier.

## Installation details

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Fedex module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Fedex module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:
- `checkout_cart_index`
- `checkout_index_index`

## Additional information

You can get more information about delivery method in magento at the articles:
- [FedEx Configuration Settings](https://docs.magento.com/user-guide/shipping/fedex.html)
- [Delivery Methods Configuration](https://docs.magento.com/user-guide/configuration/sales/delivery-methods.html)
- [Add custom shipping carrier](https://devdocs.magento.com/guides/v2.4/howdoi/checkout/checkout-add-custom-carrier.html)
