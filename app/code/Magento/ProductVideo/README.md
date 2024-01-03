# Magento_ProductVideo module

This module implements functionality related with linking video files from external resources to product.

## Installation

Before installing this module, note that the Magento_ProductAlert is dependent on the following modules:

- `Magento_Catalog`
- `Magento_Backend`

The Magento_ProductVideo module creates the `catalog_product_entity_media_gallery_value_video` table in the database.

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_ProductVideo module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_ProductVideo module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` and `view/adminhtml/layout` directories:

- `view/adminhtml/layout`
    - `catalog_product_new`
- `view/frontend/layout`
    - `catalog_product_view`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

This module extends following ui components located in the `view/adminhtml/ui_component` directory:

- `product_form`

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

## Additional information

More information can get at articles:

- [Learn how to add Product Video](https://experienceleague.adobe.com/docs/commerce-admin/catalog/products/digital-assets/product-video.html)
- [Learn how to configure Product Video](https://developer.adobe.com/commerce/frontend-core/guide/themes/product-video/)
