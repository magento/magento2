<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Locale;

interface CurrencyInterface
{
    /**
     * Default currency
     */
    const DEFAULT_CURRENCY = 'USD';

    /**
     * XML path to installed currencies
     */
    const XML_PATH_ALLOW_CURRENCIES_INSTALLED = 'system/currency/installed';

    /**
     * Retrieve currency code
     *
     * @return string
     */
    public function getDefaultCurrency();

    /**
     * Create \Magento\Framework\Currency object for current locale
     *
     * @param   string $currency
     * @return  \Magento\Framework\Currency
     */
    public function getCurrency($currency);
}
