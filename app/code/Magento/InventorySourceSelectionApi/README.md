# InventorySourceSelectionApi module

The `InventorySourceSelectionApi` module provides service contracts for source selection algorithms (SSA).

This module is part of the MSI (Multi-Source Inventory) project. The 
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI project in more detail.

## Installation details

This module is installed as part of Magento Open Source. It cannot be deleted or disabled.

## Extensibility

The `InventorySourceSelectionApi` module contains several extension points.

### Public APIs

Public APIs are defined in the `Api` and `Api/Data` directories.

### REST endpoints

The `etc/webapi.xml` file defines endpoints for setting the SSA and retrieving a list of valid SSAs.
