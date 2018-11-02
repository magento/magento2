## Introduction

This module is part of the MSI (Multi-Source Inventory) project. See 
[project description](https://devdocs.magento.com/guides/v2.3/inventory/index.html) 
for further information.

## Responsibility of this module

Following the [Single Responsibility Principle](https://en.wikipedia.org/wiki/Single_responsibility_principle)
this module provides service contracts for inventory management.
See [architecture documentation](https://devdocs.magento.com/guides/v2.3/inventory/architecture.html) 
for further information.

## MultiDimensionalIndexer

The InventoryMultiDimensionalIndexerApi provides functionality of multi-dimension index creation and
handling.

Library introduces a set of extension points which split monolithic index by specified Dimension (Scope), creating 
independent index (i.e. dedicated MySQL table) per each Dimension. Along with that library provides index name 
resolving mechanism based on provided scope. The Multi-Dimension indexes introduced for the sake of data scalability
and ability to reindex data in the scope of particular Dimension only.

Aliasing mechanism guarantees zero downtime to make Front-End responsive while Full Reindex being processed.
