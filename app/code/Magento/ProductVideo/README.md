# Magento_ProductVideo module

This module implements functionality related with linking video files from external resources to product.

## Installation

Before installing this module, note that the Magento_ProductAlert is dependent on the following modules:
- `Magento_Catalog`
- `Magento_Backend`

The Magento_ProductVideo module creates the `catalog_product_entity_media_gallery_value_video` table in the database.

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_ProductVideo module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_ProductVideo module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` and `view/adminhtml/layout` directories:
- `view/adminhtml/layout`
    - `catalog_product_new`
- `view/frontend/layout`
    - `catalog_product_view`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

This module extends following ui components located in the `view/adminhtml/ui_component` directory:
- `product_form`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

## Additional information

More information can get at articles:
- [Learn how to add Product Video](https://docs.magento.com/user-guide/catalog/product-video.html)
- [Learn how to configure Product Video](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/themes/product-video.html)
