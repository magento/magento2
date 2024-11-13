# Magento_GoogleAnalytics module

This module implements the integration with the Google Analytics service.

## Installation

Before installing this module, note that the Magento_GoogleAnalytics is dependent on the Magento_Store module.

Before disabling or uninstalling this module, note that the Magento_GoogleOptimizer module depends on this module

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_GoogleAnalytics module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_GoogleAnalytics module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:

- `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

[Learn how to configure Google Analytics](https://experienceleague.adobe.com/docs/commerce-admin/marketing/google-tools/google-analytics.html).
