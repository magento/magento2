# Magento_GoogleAnalytics module

This module implements the integration with the Google Analytics service.

## Installation

Before installing this module, note that the Magento_GoogleAnalytics is dependent on the Magento_Store module.

Before disabling or uninstalling this module, note that the Magento_GoogleOptimizer module depends on this module

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_GoogleAnalytics module. For more information about the Magento extension mechanism, see [Magento plugins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_GoogleAnalytics module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:
- `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

[Learn how to configure Google Analytics](https://docs.magento.com/user-guide/marketing/google-universal-analytics.html).
