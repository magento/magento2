<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api\Data;

/**
 * Currency interface.
 *
 * @api
 */
interface CurrencyInterface
{
    /**
     * Get the base currency code for the store.
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Set the base currency code for the store.
     *
     * @param string $code
     * @return $this
     */
    public function setBaseCurrencyCode($code);

    /**
     * Get the default display currency code for the store.
     *
     * @return string
     */
    public function getDefaultDisplayCurrencyCode();

    /**
     * Set the default display currency code for the store.
     *
     * @param string $code
     * @return $this
     */
    public function setDefaultDisplayCurrencyCode($code);

    /**
     * Get the list of allowed currency codes for the store.
     *
     * @return string[]
     */
    public function getAllowedCurrencyCodes();

    /**
     * Set the list of allowed currency codes for the store.
     *
     * @param string[] $codes
     * @return $this
     */
    public function setAllowedCurrencyCodes(array $codes = null);

    /**
     * Get the list of exchange rate information for the store.
     *
     * @return \Magento\Directory\Api\Data\ExchangeRateInterface[]
     */
    public function getExchangeRates();

    /**
     * Set the list of exchange rate information for the store.
     *
     * @param \Magento\Directory\Api\Data\ExchangeRateInterface[] $exchangeRates
     * @return $this
     */
    public function setExchangeRates(array $exchangeRates = null);
}
