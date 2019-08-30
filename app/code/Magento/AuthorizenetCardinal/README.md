# Magento_AuthorizenetCardinal module

Use the Magento_AuthorizenetCardinal module to enable 3D Secure 2.0 support for AuthorizenetAcceptjs payment integrations.

## Structure

`Gateway/` - the directory that contains payment gateway command interfaces and service classes.

For information about typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_AuthorizenetCardinal module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_AuthorizenetCardinal module.

### Events
   
This module observes the following events:

- `payment_method_assign_data_authorizenet_acceptjs` event in the `Magento\AuthorizenetCardinal\Observer\DataAssignObserver` file.

For information about an event in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/events-and-observers.html#events).
