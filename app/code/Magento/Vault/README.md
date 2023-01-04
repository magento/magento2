# Magento_Vault module

The Magento_Vault module implements the integration with the Vault payment gateway and makes it available as a payment method in Magento.

## Installation details

The Magento_Vault module is dependent on the following modules:

- Magento_Checkout
- Magento_Customer
- Magento_Payment
- Magento_Quote
- Magento_Sales
- Magento_Store
- Magento_Theme

Before disabling or uninstalling this module, note the following dependencies:

- Magento_Paypal

For information about enabling or disabling a module, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Vault module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Vault module.

### Events

The module dispatches the following events:

- `payment_method_assign_data_vault` event in the `\Magento\Vault\Model\Method\Vault::assignData()` method. Parameters:
    - `method` is a method code (`\Magento\Vault\Model\Method\Vault` class).
    - `payment_model` is a payment information model object (`\Magento\Payment\Model\InfoInterface` class).

For more information about the event, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/frantend/layout`:
    - `checkout_index_index`
    - `customer_account`
    - `vault_cards_listaction`

For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).   

### Public APIs

`\Magento\Vault\Api\PaymentMethodListInterface`:

    - Contains methods to retrieve vault payment methods.
    - This interface is consistent with \Magento\Payment\Api\PaymentMethodListInterface
    
Read a detailed description of the [Magento_Sales API](https://devdocs.magento.com/guides/v2.4/mrg/ce/Sales/services.html).
 
For information about a public API, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).
