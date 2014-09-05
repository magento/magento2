<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Currency data for quote
 *
 * @codeCoverageIgnore
 */
class Currency extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';

    const BASE_CURRENCY_CODE = 'base_currency_code';

    const STORE_CURRENCY_CODE = 'store_currency_code';

    const QUOTE_CURRENCY_CODE = 'quote_currency_code';

    const STORE_TO_BASE_RATE = 'store_to_base_rate';

    const STORE_TO_QUOTE_RATE = 'store_to_quote_rate';

    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';

    const BASE_TO_QUOTE_RATE = 'base_to_quote_rate';

    /**
     * Get global currency code
     *
     * @return string|null
     */
    public function getGlobalCurrencyCode()
    {
        return $this->_get(self::GLOBAL_CURRENCY_CODE);
    }

    /**
     * Get base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode()
    {
        return $this->_get(self::BASE_CURRENCY_CODE);
    }

    /**
     * Get store currency code
     *
     * @return string|null
     */
    public function getStoreCurrencyCode()
    {
        return $this->_get(self::STORE_CURRENCY_CODE);
    }

    /**
     * Get quote currency code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode()
    {
        return $this->_get(self::QUOTE_CURRENCY_CODE);
    }

    /**
     * Get store currency to base currency rate
     *
     * @return float|null
     */
    public function getStoreToBaseRate()
    {
        return $this->_get(self::STORE_TO_BASE_RATE);
    }

    /**
     * Get store currency to quote currency rate
     *
     * @return float|null
     */
    public function getStoreToQuoteRate()
    {
        return $this->_get(self::STORE_TO_QUOTE_RATE);
    }

    /**
     * Get base currency to global currency rate
     *
     * @return float|null
     */
    public function getBaseToGlobalRate()
    {
        return $this->_get(self::BASE_TO_GLOBAL_RATE);
    }

    /**
     * Get base currency to quote currency rate
     *
     * @return float|null
     */
    public function getBaseToQuoteRate()
    {
        return $this->_get(self::BASE_TO_QUOTE_RATE);
    }
}
