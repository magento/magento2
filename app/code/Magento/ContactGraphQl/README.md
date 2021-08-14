# Magento_ContactGraphQl module

The Magento_ContactGraphQl module adds GraphQL support for the Contact form feature provided by the Magento_Contact module.

## Installation details

The Magento_ContactGraphQl module doesn't make any changes to the database structure and can be disabled or uninstalled without the need of any manual actions.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_ContactGraphQl module by extending the SubmitContactForm resolver class and the input/output of the submitContactForm mutation available in the schema.graphqls file. 
It's important to remember that this is a GraphQL module and any changes to the actual logic of the contact form should be made by extending the Magento_Contact module.
For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_ConfigurableProductStaging module.
