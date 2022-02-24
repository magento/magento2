# Magento_Csp module
Magento_Csp implements Content Security Policies for Magento. Allows CSP configuration for Merchants,
provides a way for extension and theme developers to configure CSP headers for their extensions.

## Extensibility

Extension developers can interact with the Magento_Csp module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Csp module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.
