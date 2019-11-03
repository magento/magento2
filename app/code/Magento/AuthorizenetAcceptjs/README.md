# Magento_AuthorizenetAcceptjs module

The Magento_AuthorizenetAcceptjs module implements the integration with the Authorize.Net payment gateway and makes the latter available as a payment method in Magento.

## Installation details

Before disabling or uninstalling this module, note that the `Magento_AuthorizenetCardinal` module depends on this module.

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.3/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Gateway/` - the directory that contains payment gateway command interfaces and service classes.

For information about typical file structure of a module in Magento 2, see [Module file structure](http://devdocs.magento.com/guides/v2.3/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_AuthorizenetAcceptjs module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_AuthorizenetAcceptjs module.

### Events

This module observes the following events:

- `payment_method_assign_data_authorizenet_acceptjs` event in the `Magento\AuthorizenetAcceptjs\Observer\DataAssignObserver` file.

For information about an event in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/events-and-observers.html#events).
