#Magento_Bundle

Magento_Bundle module introduces new product type in the Magento application named Bundle Product.
This module is designed to extend existing functionality of Magento_Catalog module by adding new product type.

## Structure

  [Learn about a typical file structure for a Magento 2 module]
  (https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html).



## Extensibility

Extension developers can interact with the Magento_Backend module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Backend module.



### Layouts

  This module introduces the following layouts and layout handles in the directories:

  `view/base/layout`
    `catalog_product_prices`

  `view/adminhtml/layout`
     `adminhtml_order_shipment_new`
     `adminhtml_order_shipment_view`
     `catalog_product_bundle`
     `catalog_product_new`
     `catalog_product_view_type_bundle`
     `customer_index_wishlist`
     `sales_order_creditmemo_new`
     `sales_order_creditmemo_updateqty`
     `sales_order_creditmemo_view`
     `sales_order_invoice_new`
     `sales_order_invoice_updateqty`
     `sales_order_invoice_view`
     `sales_order_view`

   `view/frontend/layout` 
     `catalog_product_view_type_bundle`
     `catalog_product_view_type_simple`
     `checkout_cart_configure_type_bundle`
     `checkout_cart_item_renderers`
     `checkout_onepage_review_item_renderers`
     `default`
     `sales_email_order_creditmemo_renderers`
     `sales_email_order_invoice_renderers`
     `sales_email_order_renderers`
     `sales_email_order_shipment_renderers`
     `sales_order_creditmemo_renderers`
     `sales_order_invoice_renderers`
     `sales_order_item_renderers`
     `sales_order_print_creditmemo_renderers`
     `sales_order_print_invoice_renderers`
     `sales_order_print_renderers`
     `sales_order_print_shipment_renderers`
     `sales_order_shipment_renderers`

  

### Observer
    This module observes the following events:

     `etc/events.xml`
     	`magento_bundle_api_data_optioninterface_save_before` event in `Magento\Framework\EntityManager\Observer\BeforeEntitySave` file.		
		`magento_bundle_api_data_optioninterface_save_after` event in `Magento\Framework\EntityManager\Observer\AfterEntitySave` file.
		`magento_bundle_api_data_optioninterface_delete_after` event in `Magento\Framework\EntityManager\Observer\AfterEntityDelete` file.
	    `magento_bundle_model_selection_save_after` event in `Magento\Framework\EntityManager\Observer\AfterEntitySave` file.
	    `magento_bundle_model_selection_save_before` event in `Magento\Framework\EntityManager\Observer\BeforeEntitySave` file.

	  `/etc/frontend/events.xml`
	    `catalog_product_upsell` event in `Magento\Bundle\Observer\AppendUpsellProductsObserver` file.
	    `product_option_renderer_init` event in `Magento\Bundle\Observer\InitOptionRendererObserver` file.

		`/etc/adminhtml/events.xml`
			`catalog_product_edit_action` event in `Magento\Bundle\Observer\SetAttributeTabBlockObserver` file.
		    `catalog_product_new_action` event in `Magento\Bundle\Observer\SetAttributeTabBlockObserver` file.

