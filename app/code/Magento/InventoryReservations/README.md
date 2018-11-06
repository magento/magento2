# InventoryReservations module

The `InventoryReservations` module provides logic for handling product reservations.

This module is part of the MSI (Multi-Source Inventory) project. The 
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI project in more detail.

## Installation details

This module is installed as part of Magento Open Source. It cannot be deleted or disabled.

## Extension points and service contracts

All public interfaces related to this module are located in the `InventoryReservationsApi` module. 
Use the interfaces defined in `InventoryReservationsApi` to extend this module.

## Additional information

The `InventoryReservations` module creates the `inventory_cleanup_reservations` cron job.
