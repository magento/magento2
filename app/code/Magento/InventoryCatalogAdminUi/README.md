# InventoryCatalogAdminUi module

The `InventoryCatalogAdminUi` module extends the Magento Admin UI to add MSI functionality.

This module is part of the new inventory infrastructure. The
[Inventory Management overview](https://devdocs.magento.com/guides/v2.3/inventory/index.html)
describes the MSI (Multi-Source Inventory) project in more detail.

## Installation details

This module is installed as part of Magento Open Source. It may be disabled if the Inventory Management UI
is provided by a 3rd-party system or if you run a headless version of Magento.

## Extensibility

The `InventoryCatalogAdminUi` module contains several extension points.

### Layouts

You can extend and override layouts defined in the `view/adminhtml/layout`  directory.

### UI Components

The `view/adminhtml/ui_component` directory contains extensible UI components.
