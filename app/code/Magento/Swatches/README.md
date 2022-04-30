# Magento_Swatches

**Magento_Swatches** module is replacing default product attributes text values with swatch images, for more convenient product displaying and selection.

###Layouts

The module interacts with the following layout handles:

`view/adminhtml/layout` directory:
 - `catalog_product_attribute_edit.xml`
 - `catalog_product_attribute_edit_popup.xml`
 - `catalog_product_form.xml`
 - `catalog_product_superconfig_config.xml`

The module interacts with the following layout handles in the `view/frontend/layout` directory:
 - `catalog_category_view.xml`
 - `catalog_product_view_type_configurable.xml`
 - `catalog_widget_product_list.xml`
 - `catalogsearch_advanced_result.xml`
 - `catalogsearch_result_index.xml`
 - `checkout_cart_configure_type_configurable.xml`
 - `wishlist_index_configure_type_configurable.xml`

## Extensibility

Extension developers can interact with the Magento_CatalogUrlRewrite module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CatalogUrlRewrite module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

