# InventoryShipping module

The `InventoryShipping` module integrates MSI business logic into Magento's shipping logic.

This module is part of the MSI (Multi-Source Inventory) project. The 
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI project in more detail.

## Installation details
 
This module is installed as part of Magento Open Source. It cannot be deleted or disabled.

## Extension points and service contracts

All public interfaces related to this module are located in the `InventorySourceDeductionApi` and 
`InventorySourceSelectionApi` modules. 
Use the interfaces defined in those modules to extend this module.

## Additional information

The `InventoryShipping` module defines the following events:

* `sales_order_shipment_save_after`
* `sales_order_invoice_save_after`
