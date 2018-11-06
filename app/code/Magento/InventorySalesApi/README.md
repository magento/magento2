# InventorySalesApi module

The `InventorySalesApi` module provides service contracts for inventory management. 

This module is part of the MSI (Multi-Source Inventory) project. The 
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI project in more detail.

## Installation details

This module is installed as part of Magento Open Source. It cannot be deleted or disabled.

## Extensibility

The `InventorySalesApi` module contains several extension points.

### Public APIs

Public APIs are defined in the `Api` and `Api/Data` directories.

### REST endpoints

The `etc/webapi.xml` file defines endpoints for determining whether a salable amount of products are available for purchase.
