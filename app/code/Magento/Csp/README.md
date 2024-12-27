# Magento_Csp module

Magento_Csp implements Content Security Policies for Magento. Allows CSP configuration for Merchants,
provides a way for extension and theme developers to configure CSP headers for their extensions.

## Extensibility

Extension developers can interact with the Magento_Csp module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Csp module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.
