<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

interface CurrencyInterface
{
    /**
     * Returns a localized currency string
     *
     * @param  integer|float $value   OPTIONAL Currency value
     * @param  array         $options OPTIONAL options to set temporary
     * @throws \Zend_Currency_Exception When the value is not a number
     * @return string
     */
    public function toCurrency($value = null, array $options = []);

    /**
     * Sets the formating options of the localized currency string
     * If no parameter is passed, the standard setting of the
     * actual set locale will be used
     *
     * @param  array $options (Optional) Options to set
     * @return \Magento\Framework\CurrencyInterface
     */
    public function setFormat(array $options = []);

    /**
     * Returns the actual or details of other currency symbols,
     * when no symbol is available it returns the currency shortname (f.e. FIM for Finnian Mark)
     *
     * @param  string             $currency (Optional) Currency name
     * @param  string $locale   (Optional) Locale to display informations
     * @return string
     */
    public function getSymbol($currency = null, $locale = null);

    /**
     * Returns the actual or details of other currency shortnames
     *
     * @param  string             $currency OPTIONAL Currency's name
     * @param  string $locale   OPTIONAL The locale
     * @return string
     */
    public function getShortName($currency = null, $locale = null);

    /**
     * Returns the actual or details of other currency names
     *
     * @param  string             $currency (Optional) Currency's short name
     * @param  string $locale   (Optional) The locale
     * @return string
     */
    public function getName($currency = null, $locale = null);

    /**
     * Returns a list of regions where this currency is or was known
     *
     * @param  string $currency OPTIONAL Currency's short name
     * @throws \Zend_Currency_Exception When no currency was defined
     * @return array List of regions
     */
    public function getRegionList($currency = null);

    /**
     * Returns a list of currencies which are used in this region
     * a region name should be 2 charachters only (f.e. EG, DE, US)
     * If no region is given, the actual region is used
     *
     * @param  string $region OPTIONAL Region to return the currencies for
     * @return array List of currencies
     */
    public function getCurrencyList($region = null);

    /**
     * Returns the actual currency name
     *
     * @return string
     */
    public function toString();

    /**
     * Returns the set cache
     *
     * @return \Zend_Cache_Core The set cache
     */
    public static function getCache();

    /**
     * Sets a cache for \Magento\Framework\Currency
     *
     * @param  \Zend_Cache_Core $cache Cache to set
     * @return void
     */
    public static function setCache(\Zend_Cache_Core $cache);

    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function hasCache();

    /**
     * Removes any set cache
     *
     * @return void
     */
    public static function removeCache();

    /**
     * Clears all set cache data
     *
     * @param string $tag Tag to clear when the default tag name is not used
     * @return void
     */
    public static function clearCache($tag = null);

    /**
     * Sets a new locale for data retrievement
     * Example: 'de_XX' will be set to 'de' because 'de_XX' does not exist
     * 'xx_YY' will be set to 'root' because 'xx' does not exist
     *
     * @param  string $locale (Optional) Locale for parsing input
     * @throws \Zend_Currency_Exception When the given locale does not exist
     * @return $this
     */
    public function setLocale($locale = null);

    /**
     * Returns the actual set locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Returns the value
     *
     * @return float
     */
    public function getValue();

    /**
     * Adds a currency
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Add this value to currency
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to add
     * @return \Magento\Framework\CurrencyInterface
     */
    public function setValue($value, $currency = null);

    /**
     * Adds a currency
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Add this value to currency
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to add
     * @return \Magento\Framework\CurrencyInterface
     */
    public function add($value, $currency = null);

    /**
     * Substracts a currency
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Substracts this value from currency
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to substract
     * @return \Magento\Framework\CurrencyInterface
     */
    public function sub($value, $currency = null);

    /**
     * Divides a currency
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Divides this value from currency
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to divide
     * @return \Magento\Framework\CurrencyInterface
     */
    public function div($value, $currency = null);

    /**
     * Multiplies a currency
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Multiplies this value from currency
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to multiply
     * @return \Magento\Framework\CurrencyInterface
     */
    public function mul($value, $currency = null);

    /**
     * Calculates the modulo from a currency
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Calculate modulo from this value
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to calculate the modulo
     * @return \Magento\Framework\CurrencyInterface
     */
    public function mod($value, $currency = null);

    /**
     * Compares two currencies
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Compares the currency with this value
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to compare this value from
     * @return \Magento\Framework\CurrencyInterface
     */
    public function compare($value, $currency = null);

    /**
     * Returns true when the two currencies are equal
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Compares the currency with this value
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to compare this value from
     * @return boolean
     */
    public function equals($value, $currency = null);

    /**
     * Returns true when the currency is more than the given value
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Compares the currency with this value
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to compare this value from
     * @return boolean
     */
    public function isMore($value, $currency = null);

    /**
     * Returns true when the currency is less than the given value
     *
     * @param float|integer|\Magento\Framework\CurrencyInterface $value    Compares the currency with this value
     * @param string|\Magento\Framework\CurrencyInterface        $currency The currency to compare this value from
     * @return boolean
     */
    public function isLess($value, $currency = null);

    /**
     * Returns the set service class
     *
     * @return \Zend_Service
     */
    public function getService();

    /**
     * Sets a new exchange service
     *
     * @param string|\Magento\Framework\Locale\CurrencyInterface $service Service class
     * @return \Magento\Framework\CurrencyInterface
     */
    public function setService($service);
}
