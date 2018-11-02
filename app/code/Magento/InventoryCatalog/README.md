## Introduction

This module is part of the MSI (Multi-Source Inventory) project. See 
[project description](https://devdocs.magento.com/guides/v2.3/inventory/index.html) 
for further information.

## Responsibility of this module

Following the [Single Responsibility Principle](https://en.wikipedia.org/wiki/Single_responsibility_principle)
this module integrates MSI business logic into Magento's catalog logic.

## Extension points and service contracts

All public interfaces regarding this module are located in `InventoryCatalogApi` module. See 
[architecture documentation](https://devdocs.magento.com/guides/v2.3/inventory/architecture.html) 
for further information. 

Please use only `InventoryCatalogApi` interfaces in order to extend this module.
