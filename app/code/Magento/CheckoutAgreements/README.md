# Magento_CheckoutAgreements module

Magento_CheckoutAgreements module provides the ability to add a web store agreement that customers must accept before purchasing
products from the store. The customer will need to accept the terms and conditions in the Order Review section of the
checkout process to be able to place an order if the Terms and Conditions functionality is enabled.

Magento_CheckoutAgreements module extend Magento_Checkout module by adding agreement functionality on the checkout.

## Installation details

Before disabling or uninstalling this module, note that the following modules depend on this module:

- Magento_Paypal
- Magento_CheckoutAgreementsGraphQl

For information about a module enabling or disabling in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_CheckoutAgreements module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CheckoutAgreements module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Layouts

The module introduces layout handles in the `view/frontend/layout` directory.

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### Public APIs

`\Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface`:

- get the list of checkout agreements by search criteria.

`\Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface`:

- get data object for specified checkout agreement ID and store
- get lists of active checkout agreements
- create or update new checkout agreements with data object values
- remove checkout agreement by the object
- remove checkout agreement by id

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).
