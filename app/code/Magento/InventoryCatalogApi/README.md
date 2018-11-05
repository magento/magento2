# InventoryCatalogApi module

The `InventoryCatalogApi` module provides service contracts for default source and stock providers as well as bulk operations. 

This module is part of the MSI (Multi-Source Inventory) project. The 
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html
describes the MSI project in more detail.

## Installation details

This module is installed as part of Magento Open Source. It cannot be disabled.

## Extensibility

The `InventoryCatalogApi` module contains several extension points.

### Public APIs

Public APIs are defined in the `Api` directory.

### REST endpoints

The `etc/webapi.xml` file defines endpoints for assigning, unassigning, and transferring sources in bulk.
