# InventoryMultiDimensionalIndexerApi module

The `InventoryMultiDimensionalIndexerApi` module  provides functionality for creating and handling multi-dimension indexes.

This module is part of the new inventory infrastructure. The
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI (Multi-Source Inventory) project in more detail.

## Installation details

This module is installed as part of Magento Open Source. It cannot be deleted or disabled.


## Extension points

The library introduces a set of extension points which split a monolithic index by the specified dimension (Scope), creating 
an independent index (i.e. dedicated MySQL table) per dimension. The library also provides a mechanism for resolving 
index names based on the provided scope. The multi-dimension indexes are introduced for the sake of data scalability
and the ability to reindex data in the scope of particular dimension only.

An aliasing mechanism guarantees zero downtime to make Front-End responsive while Full Reindex being processed.
