# The Magento SwatchesLayeredNavigation Module

## Overview

The **Magento_SwatchesLayeredNavigation** module enables LayeredNavigation functionality for Swatch attributes

## Backward incompatible changes
No backward incompatible changes

## Dependencies
The **Magento_SwatchesLayeredNavigation** is dependent on the following modules:

- Magento_Swatches
- Magento_LayeredNavigation

## Specific Settings
The **Magento_SwatchesLayeredNavigation** module does not provide any specific settings.

## Specific Extension Points
The **Magento_SwatchesLayeredNavigation** module does not provide any specific extension points. You can extend it using the Magento extension mechanism.

## Extensibility

Extension developers can interact with the Magento_CatalogUrlRewrite module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CatalogUrlRewrite module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.