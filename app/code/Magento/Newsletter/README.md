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

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Newsletter module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Newsletter module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

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

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

This module extends customer form ui component the configuration file located in the `view/base/ui_component` directory:
- `customer_form`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

## Additional information

[Learn more about newsletter](https://docs.magento.com/user-guide/marketing/newsletters.html).

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:
- `newsletter_send_all` - schedules newsletter sending

[Learn how to configure and run cron in Magento.](http://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html).

