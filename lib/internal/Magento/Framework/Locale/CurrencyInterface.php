<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Provides access to currency config information
 *
 * @api
 * @since 2.0.0
 */
interface CurrencyInterface
{
    /**
     * Retrieve default currency code
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultCurrency();

    /**
     * Create Currency object for current locale
     *
     * @param   string $currency
     * @return  \Magento\Framework\Currency
     * @since 2.0.0
     */
    public function getCurrency($currency);
}
