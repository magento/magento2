<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Provides access to locale-related config information
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get list pre-configured allowed locales
     *
     * @return string[]
     */
    public function getAllowedLocales();

    /**
     * Get list pre-configured allowed currencies
     *
     * @return string[]
     */
    public function getAllowedCurrencies();
}
