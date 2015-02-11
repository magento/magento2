<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface CurrencyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
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
}
