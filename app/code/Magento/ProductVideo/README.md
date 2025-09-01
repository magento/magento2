# Magento_ProductVideo module

This module implements functionality related to linking video files from external resources to products.

## Installation

Before installing this module, note that it is dependent on the following modules:

- `Magento_Catalog`
- `Magento_Backend`

The Magento_ProductVideo module creates the `catalog_product_entity_media_gallery_value_video` table in the database.

All database schema changes made by this module are rolled back when the module gets disabled and the `setup:upgrade` command is run.

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_ProductVideo module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_ProductVideo module.

A lot of functionality in the module is in JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` and `view/adminhtml/layout` directories:

- `view/adminhtml/layout`
  - `catalog_product_new`
- `view/frontend/layout`
  - `catalog_product_view`

For more information about a layout, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

This module extends the following UI components located in the `view/adminhtml/ui_component` directory:

- `product_form`

For information about a UI component, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

## Additional information

You can get more information at the following articles:

- [Learn how to add Product Video](https://experienceleague.adobe.com/en/docs/commerce-admin/catalog/products/digital-assets/product-video)
- [Learn how to configure Product Video](https://developer.adobe.com/commerce/frontend-core/guide/themes/product-video/)
