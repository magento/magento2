<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Provides access to currency config information
 *
 * @api
 */
interface CurrencyInterface
{
    /**
     * Retrieve default currency code
     *
     * @return string
     */
    public function getDefaultCurrency();

    /**
     * Create Currency object for current locale
     *
     * @param   string $currency
     * @return  \Magento\Framework\Currency
     */
    public function getCurrency($currency);
}
