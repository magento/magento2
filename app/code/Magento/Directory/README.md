# Magento_Directory module

Magento_Directory module enables the management of countries and regions recognized by the store and associated data
like the country code and currency rates. Also, enables conversion of prices to a specified currency format.

## Installation details

The Magento_Directory module is one of the base Magento 2 modules. Disabling or uninstalling this module is not recommended.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Directory module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Directory module.

### Layouts

The module introduces layout handles in the `view/frontend/layout` directory.

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### Public APIs

`\Magento\Directory\Api\CountryInformationAcquirerInterface`:
 - get all countries and regions information for the store.
 - get country and region information for the store.

`\Magento\Directory\Api\CurrencyInformationAcquirerInterface`:
 - get currency information for the store.

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).

You can get more information at the articles:
- [Currency](https://docs.magento.com/user-guide/stores/currency-overview.html)
- [Currency Setup Configuration](https://docs.magento.com/user-guide/configuration/general/currency-setup.html)
- [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html)
