<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface CurrencyInterface
 * @api
 */
interface CurrencyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_GLOBAL_CURRENCY_CODE = 'global_currency_code';

    const KEY_BASE_CURRENCY_CODE = 'base_currency_code';

    const KEY_STORE_CURRENCY_CODE = 'store_currency_code';

    const KEY_QUOTE_CURRENCY_CODE = 'quote_currency_code';

    const KEY_STORE_TO_BASE_RATE = 'store_to_base_rate';

    const KEY_STORE_TO_QUOTE_RATE = 'store_to_quote_rate';

    const KEY_BASE_TO_GLOBAL_RATE = 'base_to_global_rate';

    const KEY_BASE_TO_QUOTE_RATE = 'base_to_quote_rate';

    /**#@-*/

    /**
     * Get global currency code
     *
     * @return string|null
     */
    public function getGlobalCurrencyCode();

    /**
     * Set global currency code
     *
     * @param string $globalCurrencyCode
     * @return $this
     */
    public function setGlobalCurrencyCode($globalCurrencyCode);

    /**
     * Get base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Get store currency code
     *
     * @return string|null
     */
    public function getStoreCurrencyCode();

    /**
     * Set store currency code
     *
     * @param string $storeCurrencyCode
     * @return $this
     */
    public function setStoreCurrencyCode($storeCurrencyCode);

    /**
     * Get quote currency code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode();

    /**
     * Set quote currency code
     *
     * @param string $quoteCurrencyCode
     * @return $this
     */
    public function setQuoteCurrencyCode($quoteCurrencyCode);

    /**
     * Get store currency to base currency rate
     *
     * @return float|null
     */
    public function getStoreToBaseRate();

    /**
     * Set store currency to base currency rate
     *
     * @param float $storeToBaseRate
     * @return $this
     */
    public function setStoreToBaseRate($storeToBaseRate);

    /**
     * Get store currency to quote currency rate
     *
     * @return float|null
     */
    public function getStoreToQuoteRate();

    /**
     * Set store currency to quote currency rate
     *
     * @param float $storeToQuoteRate
     * @return $this
     */
    public function setStoreToQuoteRate($storeToQuoteRate);

    /**
     * Get base currency to global currency rate
     *
     * @return float|null
     */
    public function getBaseToGlobalRate();

    /**
     * Set base currency to global currency rate
     *
     * @param float $baseToGlobalRate
     * @return $this
     */
    public function setBaseToGlobalRate($baseToGlobalRate);

    /**
     * Get base currency to quote currency rate
     *
     * @return float|null
     */
    public function getBaseToQuoteRate();

    /**
     * Set base currency to quote currency rate
     *
     * @param float $baseToQuoteRate
     * @return $this
     */
    public function setBaseToQuoteRate($baseToQuoteRate);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\CurrencyExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\CurrencyExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\CurrencyExtensionInterface $extensionAttributes);
}
