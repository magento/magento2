# Magento_LayeredNavigation module

This module introduces Layered Navigation UI for Catalog (faceted search).

This module can be removed from Magento installation without impact on the application.

## Installation

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_LayeredNavigation module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_LayeredNavigation module.

### Layouts

This module introduces the following layout handles in the `view/frontend/layout` directory:

- `catalog_category_view_type_layered`
- `catalog_category_view_type_layered_without_children`
- `catalogsearch_result_index`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

This module extends following ui components located in the `view/adminhtml/ui_component` directory:

- `product_attribute_add_form`
- `product_attributes_grid`
- `product_attributes_listing`

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

### Public APIs

- `\Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface`
    - render filter

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

## Additional information

### Page Layout

This module modifies the following page_layout in the `view/frontend.page_layout` directory:

- `1columns` - moves block `catalog.leftnav` into the `content.top` container
- `2columns-left` - moves block `catalog.leftnav` into the `sidebar.main"` container
- `2columns-right` - moves block `catalog.leftnav` into the `sidebar.main"` container
- `3columns` - moves block `catalog.leftnav` into the `sidebar.main"` container
- `empty` - moves block `catalog.leftnav` into the `category.product.list.additional` container

More information can be found in:

- [Learn more about Layered Navigation](https://experienceleague.adobe.com/docs/commerce-admin/catalog/catalog/navigation/navigation-layered.html)
- [Learn how to Configuring Layered Navigation](https://experienceleague.adobe.com/docs/commerce-admin/catalog/catalog/navigation/navigation-layered.html#configure-layered-navigation)
