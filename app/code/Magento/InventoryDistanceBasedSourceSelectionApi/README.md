# InventoryDistanceBasedSourceSelectionApi module

The `InventoryDistanceBasedSourceSelectionApi` module provides service contracts for distance based source selection algorithm. 

This module is part of the new inventory infrastructure. The
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI (Multi-Source Inventory) project in more detail.

## Installation details

This module is installed as part of Magento Open Source.

## Extensibility

The `InventoryDistanceBasedSourceSelectionApi` module contains extension points and APIs that 3rd-party developers
can use to provide custom distance based source selection algorithms.

### Public APIs

Public APIs are defined in the `Api` and `Api/Data` directories.

### REST endpoints

The `etc/webapi.xml` file defines endpoints for managing distance based algorithms.
