# Magento_Contact module

Magento_Contact module provides an implementation of "Contact Us" feature based on sending email message, allows to configure email recipients, email template, etc...

## Installation details
Before installing this module, note that the Magento_Contact is dependent on the Magento_Store module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Contact module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Contact module.

### Layouts
This module introduces the following layouts in the `view/frontend/layout` directory:
 - `contact_index_index`
 - `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### Public APIs
 - `Magento\Contact\Model\MailInterface` - send email from contact form
 - `Magento\Contact\Model\ConfigInterface` - "Contact Us" feature configuration

For information about a public API in Magento 2, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).

## Additional information

### Configuration
Use the `Stores -> Configuration -> General -> Contacts -> Contact Us -> Enable Contact Us` configuration to enable or disable module functionality.
Use the `Stores -> Configuration -> General -> Contacts -> Email Options -> Send Email To` configuration to select the recipients email.
Use the `Stores -> Configuration -> General -> Contacts -> Email Options -> Email Sender` configuration to select the senders email.
Use the `Stores -> Configuration -> General -> Contacts -> Email Options -> Email Template` configuration to select the emails template.
