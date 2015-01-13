<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api\Data;

/**
 * @see \Magento\Checkout\Service\V1\Data\Cart\Currency
 */
interface CurrencyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get global currency code
     *
     * @return string|null
     */
    public function getGlobalCurrencyCode();

    /**
     * Get base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode();

    /**
     * Get store currency code
     *
     * @return string|null
     */
    public function getStoreCurrencyCode();

    /**
     * Get quote currency code
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode();

    /**
     * Get store currency to base currency rate
     *
     * @return float|null
     */
    public function getStoreToBaseRate();

    /**
     * Get store currency to quote currency rate
     *
     * @return float|null
     */
    public function getStoreToQuoteRate();

    /**
     * Get base currency to global currency rate
     *
     * @return float|null
     */
    public function getBaseToGlobalRate();

    /**
     * Get base currency to quote currency rate
     *
     * @return float|null
     */
    public function getBaseToQuoteRate();
}
