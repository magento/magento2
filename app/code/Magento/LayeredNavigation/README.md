# Magento_LayeredNavigation module

This module introduces Layered Navigation UI for Catalog (faceted search).

This module can be removed from Magento installation without impact on the application.

## Installation

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_LayeredNavigation module. For more information about the Magento extension mechanism, see [Magento plugins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_LayeredNavigation module.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:
- `catalog_category_view_type_layered`
- `catalog_category_view_type_layered_without_children`
- `catalogsearch_result_index`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

This module extends following ui components located in the `view/adminhtml/ui_component` directory:
- `product_attribute_add_form`
- `product_attributes_grid`
- `product_attributes_listing`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

- `\Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface`
    - render filter

For information about a public API in Magento 2, see [Public interfaces & APIs](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).

## Additional information

More information can get at articles:
- [Learn more about Layered Navigation](https://docs.magento.com/user-guide/catalog/navigation-layered.html)
- [Learn how to Configuring Layered Navigation](https://docs.magento.com/user-guide/catalog/navigation-layered-configuration.html)
