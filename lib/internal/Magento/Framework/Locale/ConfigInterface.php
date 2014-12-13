<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
