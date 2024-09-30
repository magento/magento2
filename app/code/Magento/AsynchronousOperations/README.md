# Magento_AsynchronousOperations module

This component is designed to provide a response for a client that launched the bulk operation as soon as possible and postpone handling of operations moving them to the background handler.

## Installation details

The Magento_AsynchronousOperations module creates the following tables in the database:

- `magento_bulk`
- `magento_operation`
- `magento_acknowledged_bulk`

Before disabling or uninstalling this module, note that the following modules depends on this module:

- Magento_WebapiAsync

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_AsynchronousOperations module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_AsynchronousOperations module.

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

- `bulk_bulk_details`
- `bulk_bulk_details_modal`
- `bulk_index_index`

For more information about layouts in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend Magento_AsynchronousOperations module using the following configuration files in the `view/adminhtml/ui_component/` directory:

- `bulk_details_form`
- `bulk_details_form_modal`
- `bulk_listing`
- `failed_operation_listing`
- `failed_operation_modal_listing`
- `notification_area`
- `retriable_operation_listing`
- `retriable_operation_modal_listing`

For information about UI components in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).
