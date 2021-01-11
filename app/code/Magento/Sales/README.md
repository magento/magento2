# Magento_Sales module

The Magento_Sales module is responsible for order processing and appearance in system, Magento_Sales module manages next system entities and flows:

- order management,
- invoice management,
- shipment management (including tracks management),
- credit memos management.

The Magento_Sales module is required for Magento_Checkout module to perform checkout operations.

## Installation details

Before installing this module, note that the Magento_Sales is dependent on the following modules:

- Magento_Authorization
- Magento_Bundle
- Magento_Catalog
- Magento_CatalogInventory
- Magento_Checkout
- Magento_Config
- Magento_Customer
- Magento_GiftMessage
- Magento_MediaStorage
- Magento_Payment
- Magento_Quote
- Magento_Reports
- Magento_Rule
- Magento_SalesRule
- Magento_SalesSequence
- Magento_Shipping
- Magento_Store
- Magento_Tax
- Magento_Theme
- Magento_Ui
- Magento_Widget
- Magento_Wishlist

Before disabling or uninstalling this module, note these dependencies:

- Magento_Checkout
- Magento_ConfigurableProduct
- Magento_ConfigurableProductSales
- Magento_GiftMessage
- Magento_GroupedProduct
- Magento_OfflineShipping
- Magento_Paypal
- Magento_Reports
- Magento_SalesAnalytics
- Magento_SalesInventory
- Magento_SalesRule
- Magento_TestModuleFakePaymentMethod
- Magento_Vault
- Magento_Weee

Refer to [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html) for more information.

## Structure

`Cron/` - directory that contains logic for clean expired quotes, grid async insert, and send emails.
`CustomerData/` - It contains list of 5 salable products from the last placed order.
`Exception/` - directory that contains exception classes.

For more information about the typical file structure of a module, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Sales module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Sales module.

### Events

The module dispatches the following events:

- `adminhtml_customer_orders_add_action_renderer` event in the `\Magento\Sales\Block\Adminhtml\Reorder\Renderer\Action::render()` method. Parameters:
    - `renderer` is an Action object (`\Magento\Sales\Block\Adminhtml\Reorder\Renderer\Action` class).
    - `row` is a Data object (`\Magento\Framework\DataObject` class).
- `admin_sales_order_address_update` event in the `\Magento\Sales\Controller\Adminhtml\Order\AddressSave::execute()` method. Parameters:
    - `order_id` is an order id.
- `adminhtml_sales_order_create_process_data_before` event in the `\Magento\Sales\Controller\Adminhtml\Order\Create::_processActionData()` method. Parameters:
    - `eventData` is an array contains `order_create_model`, `request_model`, and `session`.
- `adminhtml_sales_order_create_process_item_after` event in the `\Magento\Sales\Controller\Adminhtml\Order\Create::_processActionData()` method. Parameters:
    - `eventData` is an array contains `order_create_model`, `request_model`, and `session`.
- `adminhtml_sales_order_create_process_data` event in the `\Magento\Sales\Controller\Adminhtml\Order\Create::_processActionData()` method. Parameters:
    - `eventData` is an array contains `order_create_model`, and `request`.
- `adminhtml_sales_order_creditmemo_register_before` event in the `\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader::load()` method. Parameters:
    - `creditmemo` is a CreditmemoInterface object (`\Magento\Sales\Api\Data\CreditmemoInterface` class).
    - `input` is a credit memo.
- `checkout_submit_all_after` event in the `\Magento\Sales\Model\AdminOrder\Create::createOrder()` method. Parameters:
    - `order` is an Order object (`\Magento\Sales\Model\Order` class).
    - `quote` is a Quote object (`\Magento\Quote\Model\Quote` class).
- `email_shipment_set_template_vars_before` event in the `\Magento\Sales\Model\Order\Email\Sender\ShipmentSender::send()` method. Parameters:
    - `sender` is a ShipmentSender object (`\Magento\Sales\Model\Order\Email\Sender\ShipmentSender` class).
    - `transport` is a transport object data.
    -  `transportObject` is a transport object.
- `sales_convert_order_to_quote` event in the `\Magento\Sales\Model\AdminOrder\Create::initFromOrder()` method. Parameters:
    - `order` is an Order object (`\Magento\Sales\Model\Order` class).
    - `quote_item` is a Quote object (`\Magento\Quote\Model\Quote` class).
- `sales_convert_order_item_to_quote_item` event in the `\Magento\Sales\Model\AdminOrder\Create::initFromOrderItem()` method. Parameters:
    - `order_item` is an ordered Item object (`\Magento\Sales\Model\Order\Item` class).
    - `quote` is a Quote Item object (`\Magento\Quote\Model\Quote\Item` class).
- `sales_order_creditmemo_refund` event in the `\Magento\Sales\Model\Order\Creditmemo\RefundOperation::execute()` method. Parameters:
    - `creditmemo` is a CreditmemoInterface object (`\Magento\Sales\Api\Data\CreditmemoInterface` class).
- `sales_order_payment_place_start` event in the `\Magento\Sales\Model\Order\Payment::place()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).
- `sales_order_payment_place_end` event in the `\Magento\Sales\Model\Order\Payment::place()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).
- `sales_order_payment_pay` event in the `\Magento\Sales\Model\Order\Payment::pay()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).
    - `invoice` is a Invoice object (`\Magento\Sales\Model\Order\Invoice` class).
- `sales_order_payment_cancel_invoice` event in the `\Magento\Sales\Model\Order\Payment::cancelInvoice()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).
    - `invoice` is a Invoice object (`\Magento\Sales\Model\Order\Invoice` class).
- `sales_order_payment_void` event in the `\Magento\Sales\Model\Order\Payment::Void()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).
    - `invoice` is a Invoice object (`\Magento\Sales\Model\Order\Invoice` class).
- `sales_order_payment_refund` event in the `\Magento\Sales\Model\Order\Payment::refund()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).
    - `creditmemo` is a CreditmemoInterface object (`\Magento\Sales\Api\Data\CreditmemoInterface` class).
- `sales_order_payment_cancel_creditmemo` event in the `\Magento\Sales\Model\Order\Payment::cancelCreditmemo()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).
    - `creditmemo` is a CreditmemoInterface object (`\Magento\Sales\Api\Data\CreditmemoInterface` class).
- `sales_order_payment_cancel` event in the `\Magento\Sales\Model\Order\Payment::cancelInvoice()` method. Parameters:
    - `payment` is a Payment object (`\Magento\Sales\Model\Order\Payment` class).

For information about the event system, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `sales_creditmemo_exportcsv`
    - `sales_creditmemo_exportexcel`
    - `sales_creditmemo_grid`
    - `sales_creditmemo_index`
    - `sales_creditmemo_item_price`
    - `sales_invoice_exportcsv`
    - `sales_invoice_exportexcel`
    - `sales_invoice_grid`
    - `sales_invoice_index`
    - `sales_invoice_item_price`
    - `sales_order_addcomment`
    - `sales_order_address`
    - `sales_order_create_customer_block`
    - `sales_order_create_index`
    - `sales_order_create_item_price`
    - `sales_order_create_load_block_billing_address`
    - `sales_order_create_load_block_billing_method`
    - `sales_order_create_load_block_comment`
    - `sales_order_create_load_block_customer_grid`
    - `sales_order_create_load_block_data`
    - `sales_order_create_load_block_form_account`
    - `sales_order_create_load_block_giftmessage`
    - `sales_order_create_load_block_header`
    - `sales_order_create_load_block_items`
    - `sales_order_create_load_block_json`
    - `sales_order_create_load_block_message`
    - `sales_order_create_load_block_newsletter`
    - `sales_order_create_load_block_plain`
    - `sales_order_create_load_block_search`
    - `sales_order_create_load_block_search_grid`
    - `sales_order_create_load_block_shipping_address`
    - `sales_order_create_load_block_shipping_method`
    - `sales_order_create_load_block_sidebar`
    - `sales_order_create_load_block_sidebar_cart`
    - `sales_order_create_load_block_sidebar_compared`
    - `sales_order_create_load_block_sidebar_pcompared`
    - `sales_order_create_load_block_sidebar_pviewed`
    - `sales_order_create_load_block_sidebar_reorder`
    - `sales_order_create_load_block_sidebar_viewed`
    - `sales_order_create_load_block_sidebar_wishlist`
    - `sales_order_create_load_block_totals`
    - `sales_order_creditmemo_addcomment`
    - `sales_order_creditmemo_grid_block`
    - `sales_order_creditmemo_new`
    - `sales_order_creditmemo_updateqty`
    - `sales_order_creditmemo_view`
    - `sales_order_creditmemos`
    - `sales_order_edit_index`
    - `sales_order_exportcsv`
    - `sales_order_exportexcel`
    - `sales_order_grid`
    - `sales_order_index`
    - `sales_order_invoice_addcomment`
    - `sales_order_invoice_grid_block`
    - `sales_order_invoice_new`
    - `sales_order_invoice_updateqty`
    - `sales_order_invoice_view`
    - `sales_order_invoices`
    - `sales_order_item_price`
    - `sales_order_shipment_grid_block`
    - `sales_order_shipments`
    - `sales_order_status_assign`
    - `sales_order_status_edit`
    - `sales_order_status_index`
    - `sales_order_status_new`
    - `sales_order_transactions`
    - `sales_order_transactions_grid_block`
    - `sales_order_view`
    - `sales_shipment_exportcsv`
    - `sales_shipment_exportexcel`
    - `sales_shipment_index`
    - `sales_transaction_child_block`
    - `sales_transactions_grid`
    - `sales_transactions_grid_block`
    - `sales_transactions_index`
    - `sales_transactions_view`
- `view/frantend/layout`:
    - `checkout_index_index`
    - `customer_account`
    - `customer_account_index`
    - `default`
    - `sales_email_item_price`
    - `sales_email_order_creditmemo_items`
    - `sales_email_order_creditmemo_renderers`
    - `sales_email_order_invoice_items`
    - `sales_email_order_invoice_renderers`
    - `sales_email_order_items`
    - `sales_email_order_renderers`
    - `sales_email_order_shipment_items`
    - `sales_email_order_shipment_renderers`
    - `sales_email_order_shipment_track`
    - `sales_guest_creditmemo`
    - `sales_guest_form`
    - `sales_guest_invoice`
    - `sales_guest_print`
    - `sales_guest_printcreditmemo`
    - `sales_guest_printinvoice`
    - `sales_guest_printshipment`
    - `sales_guest_reorder`
    - `sales_guest_shipment`
    - `sales_guest_view`
    - `sales_order_creditmemo`
    - `sales_order_creditmemo_renderers`
    - `sales_order_guest_info_links`
    - `sales_order_history`
    - `sales_order_info_links`
    - `sales_order_invoice`
    - `sales_order_invoice_renderers`
    - `sales_order_item_price`
    - `sales_order_item_renderers`
    - `sales_order_print`
    - `sales_order_print_creditmemo_renderers`
    - `sales_order_print_invoice_renderers`
    - `sales_order_print_renderers`
    - `sales_order_print_shipment_renderers`
    - `sales_order_printcreditmemo`
    - `sales_order_printinvoice`
    - `sales_order_printshipment`
    - `sales_order_reorder`
    - `sales_order_shipment`
    - `sales_order_shipment_renderers`
    - `sales_order_view`

For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

- `view/adminhtml/ui_component`:
    - `sales_order_creditmemo_grid`
    - `sales_order_grid`
    - `sales_order_invoice_grid`
    - `sales_order_shipment_grid`
    - `sales_order_view_creditmemo_grid`
    - `sales_order_view_invoice_grid`
    - `sales_order_view_shipment_grid`
- `view/base/ui_component`:
    - `customer_form`

For information about a UI component, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

`\Magento\Sales\Api\CreditmemoCommentRepositoryInterface`:

   - Get a specified credit memo comment by an id.
   - Get all credit memo comments that match specified search criteria.
   - Delete a specified credit memo comment.
   - Performs persist operations for a specified entity.

`\Magento\Sales\Api\CreditmemoItemRepositoryInterface`:

   - Loads a specified credit memo item.
   - Get all credit memo items that match specified search criteria.
   - Delete a specified credit memo item.
   - Performs persist operations for a specified credit memo item.

`\Magento\Sales\Api\CreditmemoManagementInterface`:

   - Cancels a specified credit memo.
   - Get all comments for a specified credit memo.
   - Notifies a user a specified credit memo.
   - Prepare creditmemo to refund and save it.

`\Magento\Sales\Api\CreditmemoRepositoryInterface`:

   - Loads a specified credit memo.
   - Get all credit memos that match specified search criteria.
   - Create credit memo instance.
   - Deletes a specified credit memo.
   - Performs persist operations for a specified credit memo.

`\Magento\Sales\Api\InvoiceCommentRepositoryInterface`:

   - Loads a specified invoice comment.
   - Get all invoice comments that match specified search criteria.
   - Deletes a specified invoice comment.
   - Performs persist operations for a specified invoice comment.

`\Magento\Sales\Api\InvoiceItemRepositoryInterface`:

   - Loads a specified invoice item.
   - Get all invoice items that match specified search criteria.
   - Deletes a specified invoice item.
   - Performs persist operations for a specified invoice item.
   
`\Magento\Sales\Api\InvoiceManagementInterface`:

   - Sets invoice capture.
   - Get all comments for a specified invoice.
   - Notifies a user a specified invoice.
   - Voids a specified invoice.
   
`\Magento\Sales\Api\InvoiceRepositoryInterface`:

   - Loads a specified invoice.
   - Get all invoices that match specified search criteria.
   - Deletes a specified invoice.
   - Performs persist operations for a specified invoice.

`\Magento\Sales\Api\OrderAddressRepositoryInterface`:

   - Loads a specified order address.
   - Get all order addresses that match specified search criteria.
   - Deletes a specified order address.
   - Performs persist operations for a specified order address.
   
`\Magento\Sales\Api\OrderItemRepositoryInterface`:

   - Loads a specified order item.
   - Get all order items that match specified search criteria.
   - Deletes a specified order item.
   - Performs persist operations for a specified order item.
   
`\Magento\Sales\Api\OrderPaymentRepositoryInterface`:

   - Creates new Order Payment instance.
   - Loads a specified order payment.
   - Get all order payments that match specified search criteria.
   - Deletes a specified order payment.
   - Performs persist operations for a specified order payment.

`\Magento\Sales\Api\OrderRepositoryInterface`:

   - Loads a specified order.
   - Get all orders that match specified search criteria.
   - Deletes a specified order.
   - Performs persist operations for a specified order.
   
`\Magento\Sales\Api\OrderStatusHistoryRepositoryInterface`:

   - Loads a specified order status comment.
   - Get all order status history comments that match specified search criteria.
   - Deletes a specified order status comment.
   - Performs persist operations for a specified order status comment.
   
`\Magento\Sales\Api\ShipmentCommentRepositoryInterface`:

   - Loads a specified shipment comment.
   - Get all shipment comments that match specific search criteria.
   - Deletes a specified shipment comment.
   - Performs persist operations for a specified shipment comment.
   
`\Magento\Sales\Api\ShipmentItemRepositoryInterface`:

   - Loads a specified shipment item.
   - Get all shipment items that match specified search criteria.
   - Deletes a specified shipment item.
   - Performs persist operations for a specified shipment item.
   
`\Magento\Sales\Api\ShipmentRepositoryInterface`:

   - Loads a specified shipment.
   - Get all shipments that match specified search criteria.
   - Deletes a specified shipment.
   - Performs persist operations for a specified shipment.
   - Creates new shipment instance.
   
`\Magento\Sales\Api\ShipmentTrackRepositoryInterface`:

   - Loads a specified shipment track.
   - Get all shipment tracks that match specified search criteria.
   - Deletes a specified shipment track.
   - Performs persist operations for a specified shipment track.
   - Deletes a specified shipment track by ID.
   
`\Magento\Sales\Api\TransactionRepositoryInterface`:

   - Loads a specified transaction.
   - Get all transactions that match specified search criteria.
   - Deletes a specified transaction.
   - Performs persist operations for a specified transaction.
   - Creates new Transaction instance.
