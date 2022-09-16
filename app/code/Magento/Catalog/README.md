#Magento_Catalog
Magento_Catalog module functionality is represented by the following sub-systems:
 - Products Management. It includes CRUD operation of product, product media, product attributes, etc...
 - Category Management. It includes CRUD operation of category, category attributes

Catalog module provides mechanism for creating new product type in the system.
Catalog module provides API filtering that allows to limit product selection with advanced filters.

## Structure

  [Learn about a typical file structure for a Magento 2 module]
  (https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html).

## Observer
This module observes the following events:
   `etc/events.xml`
	   `magento_catalog_api_data_productinterface_save_before` event in
	   `Magento\Framework\EntityManager\Observer\BeforeEntitySave` file.
	   `magento_catalog_api_data_productinterface_save_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntitySave` file.
	   `magento_catalog_api_data_productinterface_delete_before` event in
	   `Magento\Framework\EntityManager\Observer\BeforeEntityDelete` file.
	   `magento_catalog_api_data_productinterface_delete_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntityDelete` file.
	   `magento_catalog_api_data_productinterface_load_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntityLoad` file.
	   `magento_catalog_api_data_categoryinterface_save_before` event in
	   `Magento\Framework\EntityManager\Observer\BeforeEntitySave` file.
	   `magento_catalog_api_data_categoryinterface_save_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntitySave` file.
	   `magento_catalog_api_data_categoryinterface_save_after` event in
	   `Magento\Catalog\Observer\InvalidateCacheOnCategoryDesignChange` file.
	   `magento_catalog_api_data_categoryinterface_delete_before` event in
	   `Magento\Framework\EntityManager\Observer\BeforeEntityDelete` file.
	   `magento_catalog_api_data_categoryinterface_delete_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntityDelete` file.
	   `magento_catalog_api_data_categoryinterface_load_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntityLoad` file.
	   `magento_catalog_api_data_categorytreeinterface_save_before` event in
	   `Magento\Framework\EntityManager\Observer\BeforeEntitySave` file.
	   `magento_catalog_api_data_categorytreeinterface_save_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntitySave` file.
	   `magento_catalog_api_data_categorytreeinterface_delete_before` event in
	   `Magento\Framework\EntityManager\Observer\BeforeEntityDelete` file.
	   `magento_catalog_api_data_categorytreeinterface_delete_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntityDelete` file.
	   `magento_catalog_api_data_categorytreeinterface_load_after` event in
	   `Magento\Framework\EntityManager\Observer\AfterEntityLoad` file.
	   `admin_system_config_changed_section_catalog` event in
	   `Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange` file.
	   `catalog_product_save_before` event in
	   `Magento\Catalog\Observer\SetSpecialPriceStartDate` file.
	   `store_save_after` event in
	   `Magento\Catalog\Observer\SynchronizeWebsiteAttributesOnStoreChange` file.
	   `catalog_product_save_commit_after` event in
	   `Magento\Catalog\Observer\ImageResizeAfterProductSave` file.
	   `catalog_category_prepare_save` event in
	   `Magento\Catalog\Observer\CategoryDesignAuthorization` file.
   
    `/etc/frontend/events.xml`
	   `customer_login` event in
	   `Magento\Catalog\Observer\Compare\BindCustomerLoginObserver` file.
		`customer_logout` event in
	   `Magento\Catalog\Observer\Compare\BindCustomerLogoutObserver` file.
   
    `/etc/adminhtml/events.xml`
		`cms_wysiwyg_images_static_urls_allowed` event in
	   `Magento\Catalog\Observer\CatalogCheckIsUsingStaticUrlsAllowedObserver` file.
		`catalog_category_change_products` event in
	   `Magento\Catalog\Observer\CategoryProductIndexer` file.
		`category_move` event in
	   `Magento\Catalog\Observer\FlushCategoryPagesCache` 