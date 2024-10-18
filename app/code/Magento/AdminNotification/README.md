# Magento_AdminNotification module

The Magento_AdminNotification module provides the ability to alert administrators via system messages and provides a message inbox for surveys and notifications.

## Installation details

The Magento_AdminNotification module creates the following tables in the database:

- `adminnotification_inbox`
- `admin_system_messages`

Before disabling or uninstalling this module, note that the Magento_Indexer module depends on this module.

For information about module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_AdminNotification module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_AdminNotification module.

### Events

This module observes the following events:

- `controller_action_predispatch` event in `Magento\AdminNotification\Observer\PredispatchAdminActionControllerObserver` file.

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

- `adminhtml_notification_index`
- `adminhtml_notification_block`

For more information about layouts in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

You can extend admin notifications using the `view/adminhtml/ui_component/notification_area.xml` configuration file.

For information about UI components in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).
