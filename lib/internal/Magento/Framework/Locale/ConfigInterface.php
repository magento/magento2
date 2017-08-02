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
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get list pre-configured allowed locales
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getAllowedLocales();

    /**
     * Get list pre-configured allowed currencies
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getAllowedCurrencies();
}
