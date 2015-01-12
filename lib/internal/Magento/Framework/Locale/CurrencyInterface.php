<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
