# Magento_Newsletter module

This module allows clients to subscribe for information about new promotions and discounts and allows store administrators to send newsletters to clients subscribed for them.

## Installation

Before installing this module, note that the Magento_Newsletter is dependent on the following modules:

- `Magento_Store`
- `Magento_Customer`
- `Magento_Eav`
- `Magento_Widget`

Before disabling or uninstalling this module, note that the following modules depends on this module:

- `Magento_NewsletterGraphQl`

This module creates the following tables in the database:

- `newsletter_subscriber`
- `newsletter_template`
- `newsletter_queue`
- `newsletter_queue_link`
- `newsletter_queue_store_link`
- `newsletter_problem`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_Newsletter module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Newsletter module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` and `view/adminhtml/layout` directories:

- `view/adminhtml/layout`:
    - `newsletter_problem_block`
    - `newsletter_problem_grid`
    - `newsletter_problem_index`
    - `newsletter_queue_edit`
    - `newsletter_queue_grid`
    - `newsletter_queue_grid_block`
    - `newsletter_queue_index`
    - `newsletter_queue_preview`
    - `newsletter_queue_preview_popup`
    - `newsletter_subscriber_block`
    - `newsletter_subscriber_exportcsv`
    - `newsletter_subscriber_exportxml`
    - `newsletter_subscriber_grid`
    - `newsletter_subscriber_index`
    - `newsletter_template_edit`
    - `newsletter_template_preview`
    - `newsletter_template_preview_popup`
    - `preview`

- `view/frontend/layout`:
    - `customer_account`
    - `customer_account_create`
    - `newsletter_manage_index`
    - `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

This module extends customer form ui component the configuration file located in the `view/base/ui_component` directory:

- `customer_form`

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

## Additional information

[Learn more about newsletter](https://experienceleague.adobe.com/docs/commerce-admin/marketing/communications/newsletters/newsletters.html).

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `newsletter_send_all` - schedules newsletter sending

[Learn how to configure and run cron in Magento.](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs.html).
