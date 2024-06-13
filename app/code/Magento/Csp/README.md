# Magento_Csp module

This module implements Content Security Policies. It allows CSP configuration for merchants and provides a way for extension and theme developers to configure CSP headers for their extensions.

## Extensibility

Extension developers can interact with the Magento_Csp module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Csp module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.
