# Magento_AdminNotification module

The Magento_AdminNotification module provides the ability to alert administrators via system messages and provides a message inbox for surveys and notifications.

## Installation details

Before disabling or uninstalling this module, note that the Magento_Indexer module depends on this module.

For information about module installation in Magento 2, see [Enable or disable modules](http://devdocs.magento.com/guides/v2.3/install-gde/install/cli/install-cli-subcommands-enable.html).

### Events

This module observes the following events:

 - `controller_action_predispatch` event in `Magento\AdminNotification\Observer\PredispatchAdminActionControllerObserver`

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

- `adminhtml_notification_index`
- `adminhtml_notification_block`

For more information about layouts in Magento 2, see the [Layout documentation](http://devdocs.magento.com/guides/v2.3/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend admin notifications using the `view/adminhtml/ui_component/notification_area.xml` configuration file.

For information about UI components in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.3/ui_comp_guide/bk-ui_comps.html).
