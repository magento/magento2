<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Provides access to locale-related config information
 *
 * @api
 * @since 100.0.2
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
