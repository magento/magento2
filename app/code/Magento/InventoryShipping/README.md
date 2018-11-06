# InventoryShipping module

The `InventoryShipping` module integrates MSI business logic into Magento's shipping logic.

## Installation details
 
This module is installed as part of Magento Open Source. It cannot be disabled.

## Extension points and service contracts

All public interfaces related to this module are located in the `InventorySourceDeductionApi` and 
`InventorySourceSelectionApi` modules. 
Use the interfaces defined in those modules to extend this module.

## Additional information

The `InventoryShipping` module defines the following events:

* `sales_order_shipment_save_after`
* `sales_order_invoice_save_after`
