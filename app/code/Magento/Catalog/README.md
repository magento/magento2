# Magento_Catalog module

**Magento_Catalog** module functionality is represented by the following sub-systems:
- Products Management. It includes CRUD operation of the product, product media, product attributes, etc...
- Category Management. It includes CRUD operation of category, category attributes

**Magento_Catalog** module provides a mechanism for creating a new product type in the system and API filtering that allows limiting product selection with advanced filters.

## Installation details

The Magento_Catalog module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Pricing/` - the directory that contains solutions for calculation of the product's price.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Catalog module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Catalog module.

### Events

#### Block events:
- `shortcut_buttons_container` event in the `\Magento\Catalog\Block\ShortcutButtons::_beforeToHtml` method. Parameters:
    - `container` - is a `$this` object (`\Magento\Catalog\Block\ShortcutButtons` class).
    - `is_catalog_product` - flag is a product catalog(`boolean` type).
    - `or_position` - show, or position value(`string` type).
- `adminhtml_catalog_category_tree_is_moveable` event in the `\Magento\Catalog\Block\Adminhtml\Category\Tree::_isCategoryMoveable` method. Parameters:
    - `options` - category options, after set `is_moveable` as true, (`\Magento\Framework\DataObject` class).
- `adminhtml_catalog_category_tree_can_add_root_category` event in the `\Magento\Catalog\Block\Adminhtml\Category\Tree::canAddRootCategory` method. Parameters:
    - `category` - a category data(`array` type).
    - `options` - an options, where `is_allow` is as true (`\Magento\Framework\DataObject` class).
    - `store` current store ID(`int` type).
- `adminhtml_catalog_category_tree_can_add_sub_category` event in the `\Magento\Catalog\Block\Adminhtml\Category\Tree::canAddSubCategory` method. Parameters:
    - `category` - category data(`array` type).
    - `options` - options, where `is_allow` is as true (`\Magento\Framework\DataObject` class).
    - `store` - current store ID(`int` type).
- `adminhtml_catalog_product_edit_element_types` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Attributes::_getAdditionalElementTypes`, `\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_getAdditionalElementTypes` methods. Parameters:
    - `response` - response before add it to the result of additional product types (`\Magento\Framework\DataObject` class).
- `adminhtml_catalog_product_edit_prepare_form` event in the `\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm` method. Parameters:
    - `form` - form of adding new attribute(`\Magento\Framework\Data\Form` class).
    - `layout` - attributes block layout(`\Magento\Framework\View\LayoutInterface` class).
- `adminhtml_catalog_product_edit_prepare_form` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Attributes::_prepareForm` method. Parameters:
    - `form` - form of adding new attribute(`\Magento\Framework\Data\Form` class).
- `product_attribute_form_build` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Advanced::_prepareForm` method. Parameters:
    - `form` - product attribute form at the advanced tab(`\Magento\Framework\Data\Form` class).
- `product_attribute_form_build_main_tab` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Main::_prepareForm` method. Parameters:
    - `form` - form object(`\Magento\Framework\Data\Form` class)
- `product_attribute_form_build_front_tab` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front::_prepareForm` method. Parameters:
    - `form` - form object(`\Magento\Framework\Data\Form` class).
- `adminhtml_catalog_product_attribute_edit_frontend_prepare_form` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front::_prepareForm` method. Parameters:
    - `form` adminhtml product attribute edit from(`\Magento\Framework\Data\Form` class).
- `adminhtml_product_attribute_types` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Main::processFrontendInputTypes` method. Parameters:
    - `response` - product attribute types data(`\Magento\Framework\DataObject` class).
- `adminhtml_catalog_product_attribute_set_toolbar_main_html_before` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main::_toHtml` method. Parameters:
    - `block` is a `$this` object(`\Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main` class).
- `adminhtml_catalog_product_attribute_set_main_html_before` event in the `\Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main::_toHtml` method. Parameters:
    - `block` is a `$this` object(`\Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main` class).
- `adminhtml_catalog_product_edit_tab_attributes_create_html_before` event in the `\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create::_toHtml` method. Parameters:
    - `block` is a `$this` object(`\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create` class).
- `adminhtml_catalog_product_form_prepare_excluded_field_list` event in the `\Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Attributes::_prepareForm` method. Parameters:
    - `object` is a `$this` object(`\Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Attributes` class).
- `catalog_product_gallery_prepare_layout` event in the `\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content::_prepareLayout` method. Parameters:
    - `block` is a `$this` object(`\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content` class).
- `catalog_product_option_price_configuration_after` event in the `\Magento\Catalog\Block\Product\View\Options::getJsonConfig` method. Parameters:
    - `configObj` - product options(`\Magento\Framework\DataObject` class).
- `catalog_block_product_status_display` event in the `\Magento\Catalog\Block\Product\AbstractProduct::displayProductStockStatus` method. Parameters:
    - `status` - product status, where `display_status` is set as true (`\Magento\Framework\DataObject` class).
- `catalog_block_product_list_collection` event in the `\Magento\Catalog\Block\Product\ListProduct::initializeProductCollection` method. Parameters:
    - `collection` - product collection(`\Magento\Catalog\Model\ResourceModel\Product\Collection` class).
- `catalog_product_upsell` event in the `\Magento\Catalog\Block\Product\ProductList\Upsell::_prepareData` method. Parameters:
    - `product` - current product object(`\Magento\Catalog\Model\Product` class).
    - `collection` - upsell product collection(`\Magento\Catalog\Model\ResourceModel\Product\Collection` class).
- `catalog_product_view_config` event in the `\Magento\Catalog\Block\Product\View::getJsonConfig` method. Parameters:
    - `response_object` - object to set additional options in the config(`\Magento\Framework\DataObject` class).
- `rss_catalog_category_xml_callback` event in the `\Magento\Catalog\Block\Rss\Category::getRssData` method. Parameters:
    - `product` - product object, before check is allow in rss(`\Magento\Catalog\Model\Product` class).
- `rss_catalog_new_xml_callback` event in the `\Magento\Catalog\Block\Rss\Product\NewProducts::getRssData` method. Parameters:
    - `row` - product data(`array` type).
    - `product` - product objet before check is allow in rss(`\Magento\Catalog\Model\Product` class).
- `rss_catalog_special_xml_callback` event in the `\Magento\Catalog\Block\Rss\Product\Special::getRssData` method. Parameters:
    - `row` - product data(`array` type).
    - `product` - product objet, before check is allow in rss(`\Magento\Catalog\Model\Product` class).

#### Controller events:

- `catalog_controller_category_delete` event in the `\Magento\Catalog\Controller\Adminhtml\Category\Delete::execute` method. Parameters:
    - `category` - before remove, a category object(`\Magento\Catalog\Api\Data\CategoryInterface` class).
- `category_prepare_ajax_response` event in the `\Magento\Catalog\Controller\Adminhtml\Category::ajaxRequestResponse` method. Parameters:
    - `response` - before return response object(`\Magento\Framework\DataObject` class).
    - `controller` is a `$this` object(`\Magento\Catalog\Controller\Adminhtml\Category` class).
- `catalog_category_prepare_save` event in the `\Magento\Catalog\Controller\Adminhtml\Category\Save::execute` method. Parameters:
    - `category` - before the save, a category object(`\Magento\Catalog\Api\Data\CategoryInterface` class).
    - `request` - request data (`\Magento\Framework\App\RequestInterface` class).
- `catalog_product_edit_action` event in the `\Magento\Catalog\Controller\Adminhtml\Product\Edit::execute` method. Parameters:
    - `product` - current product object (`\Magento\Catalog\Model\Product` class).
- `catalog_product_gallery_upload_image_after` event in the `\Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload::execute` method. Parameters:
    - `result` - uploader objet result after the save(`array` type).
    - `action` is a `$this` object (`\Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload` class).
- `catalog_product_new_action` event in the `\Magento\Catalog\Controller\Adminhtml\Product\NewAction::execute` method. Parameters:
    - `product` - new product object (`\Magento\Catalog\Model\Product` class).
- `controller_action_catalog_product_save_entity_after` event in the `\Magento\Catalog\Controller\Adminhtml\Product\Save::execute` method. Parameters:
    - `controller` is a `$this` object (`\Magento\Catalog\Controller\Adminhtml\Product\Save` class).
    - `product` - before the save, product object (`\Magento\Catalog\Model\Product` class).
- `catalog_controller_category_init_after` event in the `\Magento\Catalog\Controller\Category\View::_initCategory` method. Parameters:
    - `category` current category object (`\Magento\Catalog\Api\Data\CategoryInterface` class).
    - `controller_action` is a `$this` object (`\Magento\Catalog\Controller\Category\View` class).
- `catalog_product_compare_add_product` event in the `\Magento\Catalog\Controller\Product\Compare\Add::execute` method. Parameters:
    - `product` - after add to compare list, product object (`\Magento\Catalog\Model\Product` class).
- `catalog_product_compare_remove_product` event in the `\Magento\Catalog\Controller\Product\Compare\Remove::execute` method. Parameters:
    - `product` - after the remove from compare list, product object (`\Magento\Catalog\Model\Product` class).

##### Helper events:

- `catalog_controller_product_init_before` event in the `\Magento\Catalog\Helper\Product::initProduct` method. Parameters:
    - `controller_action` - controller object (`\Magento\Framework\App\Action\Action` class).
    - `params` - params of product init(`\Magento\Framework\DataObject ` class).
- `catalog_controller_product_init_after` event in the `\Magento\Catalog\Helper\Product::initProduct` method. Parameters:
    - `product` - init product object(`\Magento\Catalog\Model\Product` class).
    - `controller_action` - controller object(`\Magento\Framework\App\Action\Action` class).
    - `catalog_controller_product_view` event in the `\Magento\Catalog\Helper\Product\View::prepareAndRender` method. Parameters:
    - `product` - before a renderer, product object(`\Magento\Catalog\Model\Product` class).

#### Model events:

- `catalog_product_attribute_update_before` event in the `\Magento\Catalog\Model\Product\Action::updateAttributes` method. Parameters:
    - `attributes_data` - product attribute data(`array` type).
    - `product_ids` - product ID list(`array` type).
    - `store_id` - product's store id(`int` type).
- `catalog_product_to_website_change` event in the `\Magento\Catalog\Model\Product\Action::updateWebsites` method. Parameters:
    - `products` - products ID list after updates website(`array` type).
- `catalog_category_move_before` event in the `\Magento\Catalog\Model\Category::move` method. Parameters:
    - `category` is a `$this` object (`\Magento\Catalog\Model\Category` class).
    - `parent` - a parent category object(`\Magento\Catalog\Model\Category` class).
    - `category_id` - a current category ID(`int` type).
    - `prev_parent_id` - a parent category ID, was set before(`int` type).
    - `parent_id` - a current category ID, should be set(`int` type).
- `catalog_category_move_after` event in the `\Magento\Catalog\Model\Category::move` method. Parameters:
    - `category` is a `$this` object (`\Magento\Catalog\Model\Category` class).
    - `parent` - a parent category object(`\Magento\Catalog\Model\Category` class).
    - `category_id` - a current category ID(`int` type).
    - `prev_parent_id` - a parent category ID, was set before(`int` type).
    - `parent_id` - a current category ID, was already set(`int` type).
- `category_move` event in the `\Magento\Catalog\Model\Category::move` method after commit action. Parameters:
    - `category` is a `$this` object (`\Magento\Catalog\Model\Category` class).
    - `parent` - a parent category object(`\Magento\Catalog\Model\Category` class).
    - `category_id` - current category ID(`int` type).
    - `prev_parent_id` - parent category ID(`int` type), was set before.
    - `parent_id` - current category ID(`int` type), was already set.
- `clean_cache_by_tags` event in the `\Magento\Catalog\Model\Category::move` method. Parameters:
    - `object` is a `$this` object(`\Magento\Catalog\Model\Category` class).
- `clean_cache_by_tags` event in the `\Magento\Catalog\Model\Indexer\Category\Product\Action\Rows::execute`, `\Magento\Catalog\Model\Indexer\Product\Category\Action\Rows::execute` methods. Parameters:
    - `object`- after refresh entities index, a cache context object(`Magento\Framework\Indexer\CacheContext` class).
- `catalog_product_validate_before` event in the `\Magento\Catalog\Model\Product::validate` method. Parameters:
    - `data_object` is a `$this` object(`\Magento\Catalog\Model\Product` class).
    - `object` is a `$this` object(`\Magento\Catalog\Model\Product` class).
- `catalog_product_validate_after` event in the `\Magento\Catalog\Model\Product::validate` method. Parameters:
    - `data_object` is a `$this` object (`\Magento\Catalog\Model\Product` class).
    - `object` is a `$this` object(`\Magento\Catalog\Model\Product` class).
- `catalog_product_is_salable_before` event in the `\Magento\Catalog\Model\Product` method. Parameters:
    - `product` is a `$this` object(`\Magento\Catalog\Model\Product` class).
- `catalog_product_is_salable_after` event in the `\Magento\Catalog\Model\Product` method. Parameters:
    - `product` is a `$this` object(`\Magento\Catalog\Model\Product` class).
    - `salable` - product saleable data(`\Magento\Framework\DataObject` class).
- `adminhtml_product_attribute_types` event in the `\Magento\Catalog\Model\Product\Attribute\Source\Inputtype::toOptionArray` method. Parameters:
    - `response` - product attribute types data(`\Magento\Framework\DataObject` class).
- `catalog_product_get_final_price` event in the `\Magento\Catalog\Model\Product\Type\Price::getFinalPrice` method. Parameters:
    - `product` - product object(`\Magento\Catalog\Model\Product` class).
    - `qty` - a product qty(`int` type).
- `catalog_category_change_products` event in the `\Magento\Catalog\Model\ResourceModel\Category::_saveCategoryProducts` method. Parameters:
    - `category` - on save category object(`\Magento\Catalog\Model\Category` class).
    - `product_ids` - product ID list(`array` type.)
- `catalog_category_delete_after_done` event in the `\Magento\Catalog\Model\ResourceModel\Category::delete` method. Parameters:
    - `category` - deleted category object(`\Magento\Catalog\Model\Category` class).
- `catalog_category_collection_load_before` event in the `\Magento\Catalog\Model\ResourceModel\Category\Collection::_beforeLoad`, `\Magento\Catalog\Model\ResourceModel\Category\Flat\Collection::_beforeLoad` methods. Parameters:
    - `category_collection` is a `$this` object(`\Magento\Catalog\Model\ResourceModel\Category\Collection` class).
- `catalog_category_collection_load_after` event in the `\Magento\Catalog\Model\ResourceModel\Category\Collection::_afterLoad`, `\Magento\Catalog\Model\ResourceModel\Category\Flat\Collection::_afterLoad` methods. Parameters:
    - `category_collection` is a `$this` object(`\Magento\Catalog\Model\ResourceModel\Category\Collection` class).
- `catalog_category_collection_add_is_active_filter` event in the `\Magento\Catalog\Model\ResourceModel\Category\Collection::addIsActiveFilter`, `\Magento\Catalog\Model\ResourceModel\Category\Flat\Collection::addIsActiveFilter` methods. Parameters:
    - `category_collection` is a `$this` object(`\Magento\Catalog\Model\ResourceModel\Category\Collection` class).
- `catalog_category_tree_init_inactive_category_ids` event in the `\Magento\Catalog\Model\ResourceModel\Category\Flat::_initInactiveCategoryIds`, `\Magento\Catalog\Model\ResourceModel\Category\Tree::_initInactiveCategoryIds` methods. Parameters:
    - `tree` is a `$this` object.
- `catalog_category_flat_loadnodes_before` event in the `\Magento\Catalog\Model\ResourceModel\Category\Flat::_loadNodes` method. Parameters:
    - `select` - before fetch category nodes, a select object(`\Magento\Framework\DB\Select` class).
- `catalog_product_delete_after_done` event in the `\Magento\Catalog\Model\ResourceModel\Product::delete` method. Parameters:
    - `product` - deleted product object(`\Magento\Catalog\Model\Product` class).
- `catalog_prepare_price_select` event in the `\Magento\Catalog\Model\ResourceModel\Product\Collection::_preparePriceExpressionParameters` method. Parameters:
    - `select` - select object(`\Magento\Framework\DB\Select` class).
    - `table` - table name(`string` type).
    - `store_id` - current store ID(`int` type).
    - `response_object` - response additional calculation object(`\Magento\Framework\DataObject` class).
- `catalog_product_collection_load_after` event in the `\Magento\Catalog\Model\ResourceModel\Product\Collection::_afterLoad` method. Parameters:
    - `tree` is a `$this` object(`\Magento\Catalog\Model\ResourceModel\Product\Collection` class).
- `catalog_product_collection_before_add_count_to_categories` event in the `\Magento\Catalog\Model\ResourceModel\Product\Collection::addCountToCategories` method. Parameters:
    - `collection` is a `$this` object.
- `catalog_product_collection_apply_limitations_after` event in the `\Magento\Catalog\Model\ResourceModel\Product\Collection::_applyProductLimitations` method. Parameters:
    - `collection` is a `$this` object.
- `catalog_product_compare_item_collection_clear` event in the `\Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection::clear` method.
- `prepare_catalog_product_index_select` event in the `\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav::_prepareRelationIndexSelect`, `\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal::_prepareIndex`, `\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source::_prepareSelectIndex`, `\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source::_prepareMultiselectIndex`, `\Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice::getSelect`, `\Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice::getQuery` methods. Parameters:
    - `select` - select object (`\Magento\Framework\DB\Select` class).
    - `entity_field` - entity ID column(`\Zend_Db_Expr` class).
    - `website_field` - website ID column(`\Zend_Db_Expr` class).
    - `store_field` - store ID column(`\Zend_Db_Expr` class).
- `rss_catalog_notify_stock_collection_select` event in the `\Magento\Catalog\Model\Rss\Product\NotifyStock::getProductsCollection` method. Parameters:
    - `collection` - products' collection object(`\Magento\Catalog\Model\ResourceModel\Product\Collection` class)

#### Plugin events:

- `clean_cache_by_tags` event in the `\Magento\Catalog\Plugin\Model\Product\Action\UpdateAttributesFlushCache::afterUpdateAttributes`, `\Magento\Catalog\Plugin\Model\Product\Action\UpdateAttributesFlushCache::afterUpdateWebsites` methods. Parameters:
    - `object` - cache context object(`Magento\Framework\Indexer\CacheContext` class).

### Layouts

The module introduces layout handles in the `view/adminhtml/layout`, `view/base/layout` and `view/frontend/layout` directories.

For more information about a layout in Magento 2, see the [Layout documentation](http://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend product and category updates using the configuration files located in the `view/adminhtml/ui_component` and `view/frontend/ui_component` directories.

For information about a UI component in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

#### Product service

- `\Magento\Catalog\Api\ProductRepositoryInterface`:
    - get a product by SKU or ID
    - get a product list by search criteria
    - saving a product data
    - remove a product by ID

- `\Magento\Catalog\Api\ProductAttributeTypesListInterface`:
    - get product attributes list.

- `\Magento\Catalog\Api\ProductAttributeRepositoryInterface`:
    - get an attribute by code
    - get an attribute list by search criteria
    - saving an attribute
    - remove an attribute by ID

- `\Magento\Catalog\Api\CategoryAttributeRepositoryInterface`:
    - get a category attribute by code
    - get a category list by search criteria

- `\Magento\Catalog\Api\CategoryAttributeOptionManagementInterface`:
    - get a category attribute option list by attribute code

- `\Magento\Catalog\Api\ProductTypeListInterface`:
    - get a product type interfaces list

- `\Magento\Catalog\Api\AttributeSetRepositoryInterface`:
    - get an attribute set by ID
    - get an attribute set list by search criteria
    - save an attribute set
    - remove an attribute set by ID

- `\Magento\Catalog\Api\ProductAttributeManagementInterface`:
    - get an attributes list by attribute set ID
    - assign or unassign attributes to attribute set

- `\Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface`:
    - get attribute group list by search criteria
    - save attribute group
    - remove attribute group by ID

- `\Magento\Catalog\Api\ProductAttributeOptionManagementInterface`:
    - get an attribute options by attribute ID
    - add an option to attribute
    - remove an option from attribute by attribute code and option ID

- `\Magento\Catalog\Api\ProductAttributeOptionUpdateInterface`:
    - update attribute option

- `\Magento\Catalog\Api\ProductMediaAttributeManagementInterface`:
    - get media attribute list by attribute set name

- `\Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface`:
    - update or create media gallery attribute
    - remove media gallery product attribute by product SKU and entry ID
    - get media gallery attributes list by product SKU

#### Product price

- `\Magento\Catalog\Api\ProductTierPriceManagementInterface`:
    - get a tier prices list by product SKU and customer group ID
    - add a tier price by SKU, customer group ID, price value, and QTY
    - remove a product tier price by SKU, customer group ID, and QTY

- `\Magento\Catalog\Api\TierPriceStorageInterface`:
    - get storage prices by SKU list
    - update prices by price interface list
    - remove existing tier prices and replace them with the new ones
    - delete product tier prices

- `\Magento\Catalog\Api\BasePriceStorageInterface`:
    - get base product prices by SKU list
    - update base prices

- `\Magento\Catalog\Api\CostStorageInterface`:
    - get product costs by sku list
    - add or update product cost
    - remove product cost

- `\Magento\Catalog\Api\SpecialPriceStorageInterface`:
    - get product's special price
    - add or update product's special price
    - delete product's special price

#### Category service

- `\Magento\Catalog\Api\CategoryRepositoryInterface`
    - save category
    - get category by ID
    - remove by identifier

- `\Magento\Catalog\Api\CategoryManagementInterface`:
    - get category list by root category ID
    - move category

- `\Magento\Catalog\Api\CategoryListInterface`:
    - get category list by search criteria

#### Product custom options

- `\Magento\Catalog\Api\ProductCustomOptionTypeListInterface`:
    - get custom option types

- `Magento\Catalog\Api\ProductCustomOptionRepositoryInterface`:
    - get the list of custom options for a specific product
    - get custom option for a specific product
    - save custom option
    - delete custom option by identifier

#### Product Links

- `\Magento\Catalog\Api\ProductLinkTypeListInterface`:
    - get information about available product link types
    - provide a list of the product link type attributes

- `\Magento\Catalog\Api\ProductLinkManagementInterface`:
    - provide the list of links for a specific product
    - assign a product link to another product

- `\Magento\Catalog\Api\ProductLinkRepositoryInterface`:
    - save product link
    - delete product link

#### Category product links

- `Magento\Catalog\Api\CategoryLinkManagementInterface`:
    - get products assigned to category by category ID

- `Magento\Catalog\Api\CategoryLinkRepositoryInterface`:
    - assign a product to the required category
    - remove the product assignment from the category by category ID and SKU

#### Product website links

- `Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface`:
    - assign a product to the website
    - remove the website assignment from the product ID product SKU

- `Magento\Catalog\Api\ProductRenderListInterface`:
    - get list of product render info

For information about a public API in Magento 2, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).

## Additional information

More information can get at articles:
- [Catalog Configurations](https://docs.magento.com/user-guide/configuration/catalog.html)
- [Catalog Page Description](https://docs.magento.com/user-guide/quick-tour/catalog-page.html)
- [Catalog User guide](https://docs.magento.com/user-guide/catalog.html)
- [Product Attributes](https://docs.magento.com/user-guide/stores/attributes-product.html)
- [EAV And Extension Attributes](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/attributes.html)
- [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `catalog_index_refresh_price` - add products to changes list with price which depends on date
- `catalog_product_flat_indexer_store_cleanup` - delete all product flat tables for not existing stores
- `catalog_product_outdated_price_values_cleanup` - delete all price values for non-admin stores if `PRICE_SCOPE` is set to global.
- `catalog_product_frontend_actions_flush` - flush frontend actions deprecates by lifetime
- `catalog_product_attribute_value_synchronize` - synchronizes website attribute values if needed
