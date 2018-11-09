# InventoryConfiguration module

The `InventoryConfiguration` module implements logic for inventory management configuration.

This module is part of the new inventory infrastructure. The
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI (Multi-Source Inventory) project in more detail.

## Installation details

This module is installed as part of Magento Open Source. Unless a custom implementation for
`InventoryConfigurationApi` is provided by a 3rd-party module, the module cannot be deleted or disabled.

## Extension points and service contracts

All public interfaces related to this module are located in the `InventoryConfigurationApi` module. 
Use the interfaces defined in `InventoryConfigurationApi` to extend this module.
