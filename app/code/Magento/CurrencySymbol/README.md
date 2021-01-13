# Magento_CurrencySymbol module

This module enables creating custom currencies and managing currency conversion rates.

## Installation

Before installing this module, note that the Magento_CurrencySymbol is dependent on the Magento_Widget module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_CurrencySymbol module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CurrencySymbol module.

### Events
The module dispatches the following events:
- `admin_system_config_changed_section_currency_before_reinit` event in the `\Magento\CurrencySymbol\Model\System\Currencysymbol::setCurrencySymbolsData` method. Parameters:
  - `website` website id (`string|null` type)
  - `website` store id (`string|null` type)
- `admin_system_config_changed_section_currency`  event in the `\Magento\CurrencySymbol\Model\System\Currencysymbol::setCurrencySymbolsData` method. Parameters:
    - `website` website id (`string|null` type)
    - `website` store id (`string|null` type)

For information about an event in Magento 2, see [Events and observers](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts
This module introduces the following layouts in the `view/adminhtml/layout` directory:
- `adminhtml_system_currency_index`
- `adminhtml_system_currencysymbol_index`

For more information about a layout in Magento 2, see the [Layout documentation](http://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information 

### Controllers

#### Currency Controllers
- `CurrencySymbol\Controller\Adminhtml\System\Currency\FetchRates.php` - gets a specified currency conversion rate.
Supports all defined currencies in the system.
- `CurrencySymbol\Controller\Adminhtml\System\Currency\SaveRates.php` -  saves rates for defined currencies.

#### Currency Symbol Controllers
- `CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Reset.php` - resets all custom currency symbols.
- `CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Save.php` - creates custom currency symbols.
