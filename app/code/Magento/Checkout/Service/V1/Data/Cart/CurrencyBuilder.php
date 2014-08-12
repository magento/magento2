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
 * Currency data builder for quote
 *
 * @codeCoverageIgnore
 */
class CurrencyBuilder extends \Magento\Framework\Service\Data\AbstractObjectBuilder
{
    /**
     * Set global currency code
     *
     * @param string|null $value
     * @return $this
     */
    public function setGlobalCurrencyCode($value)
    {
        return $this->_set(Currency::GLOBAL_CURRENCY_CODE, $value);
    }

    /**
     * Set base currency code
     *
     * @param string|null $value
     * @return $this
     */
    public function setBaseCurrencyCode($value)
    {
        return $this->_set(Currency::BASE_CURRENCY_CODE, $value);
    }

    /**
     * Set store currency code
     *
     * @param string|null $value
     * @return $this
     */
    public function setStoreCurrencyCode($value)
    {
        return $this->_set(Currency::STORE_CURRENCY_CODE, $value);
    }

    /**
     * Set quote currency code
     *
     * @param string|null $value
     * @return $this
     */
    public function setQuoteCurrencyCode($value)
    {
        return $this->_set(Currency::QUOTE_CURRENCY_CODE, $value);
    }

    /**
     * Set store currency to base currency rate
     *
     * @param float|null $value
     * @return $this
     */
    public function setStoreToBaseRate($value)
    {
        return $this->_set(Currency::STORE_TO_BASE_RATE, $value);
    }

    /**
     * Set store currency to quote currency rate
     *
     * @param float|null $value
     * @return $this
     */
    public function setStoreToQuoteRate($value)
    {
        return $this->_set(Currency::STORE_TO_QUOTE_RATE, $value);
    }

    /**
     * Set base currency to global currency rate
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseToGlobalRate($value)
    {
        return $this->_set(Currency::BASE_TO_GLOBAL_RATE, $value);
    }

    /**
     * Set base currency to quote currency rate
     *
     * @param float|null $value
     * @return $this
     */
    public function setBaseToQuoteRate($value)
    {
        return $this->_set(Currency::BASE_TO_QUOTE_RATE, $value);
    }
}
