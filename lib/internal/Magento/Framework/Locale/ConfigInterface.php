<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

interface ConfigInterface
{
    /**
     * Get list pre-configured allowed locales
     *
     * @return array
     */
    public function getAllowedLocales();

    /**
     * Get list pre-configured allowed currencies
     *
     * @return array
     */
    public function getAllowedCurrencies();
}
