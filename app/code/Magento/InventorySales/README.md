# InventorySales module

The `InventorySales` module integrates Inventory Management business logic into Magento's sales logic.

This module is part of the new inventory infrastructure. The
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI (Multi-Source Inventory) project in more detail.

## Installation details
 
This module is installed as part of Magento Open Source. Unless a custom implementation for `InventorySalesApi`
is provided by a 3rd-party module, the module cannot be deleted or disabled.

## Extension points and service contracts

All public interfaces related to this module are located in the `InventorySalesApi` module. 
Use the interfaces defined in `InventorySalesApi` to extend this module.
