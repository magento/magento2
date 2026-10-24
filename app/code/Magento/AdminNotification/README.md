# Magento_AdminNotification module

This module provides the ability to alert administrators via system messages and provides a message inbox for surveys and notifications.

## Installation details

This module creates the following tables in the database:

- `adminnotification_inbox`
- `admin_system_messages`

Before disabling or uninstalling this module, note that the Magento_Indexer module depends on this module.

For information about module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.

### Events

This module observes the following events:

- `controller_action_predispatch` event in `Magento\AdminNotification\Observer\PredispatchAdminActionControllerObserver` file.

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

- `adminhtml_notification_index`
- `adminhtml_notification_block`

For more information about layouts, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

You can extend admin notifications using the `view/adminhtml/ui_component/notification_area.xml` configuration file.

For information about UI components, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).
