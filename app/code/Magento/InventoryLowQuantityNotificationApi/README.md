# InventoryLowQuantityNotificationApi module

The `InventoryLowQuantityNotificationApi` module provides service contracts for managing Inventory Management notifications. 

This module is part of the new inventory infrastructure. The
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI (Multi-Source Inventory) project in more detail.

## Installation details

This module is installed as part of Magento Open Source. It cannot be deleted or disabled.

## Extensibility

The `InventoryLowQuantityNotificationApi` module contains extension points and APIs that 3rd-party developers
can use to provide custom low quantity notification functionality.

### Public APIs

Public APIs are defined in the `Api` and `Api/Data` directories.

### REST endpoints

The `etc/webapi.xml` file defines endpoints for managing low quantity notifications.
