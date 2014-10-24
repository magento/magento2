# CurrencySymbol

**CurrencySymbol** module provides support for adding custom currencies as well as the management of the currency 
conversion rates.

## Controllers

### Currency Controllers
***CurrencySymbol\Controller\Adminhtml\System\Currency\FetchRates.php*** retrieves conversion rates for a specified 
currency to every other defined currency in the system.
***CurrencySymbol\Controller\Adminhtml\System\Currency\SaveRates.php*** saves rates for each defined currency.

### Currency Symbol Controllers
***CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Reset.php*** resets all custom currency symbols.
***CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Save.php*** creates a new custom currency symbols.

