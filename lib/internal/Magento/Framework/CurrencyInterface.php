<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\Currency\Exception\CurrencyException;
use Zend_Cache_Core;

/**
 * @api
 * @since 100.0.2
 */
interface CurrencyInterface
{
    /**
     * Returns a localized currency string
     *
     * @param  int|float $value OPTIONAL Currency value
     * @param  array $options OPTIONAL options to set temporary
     * @throws CurrencyException When the value is not a number
     * @return string
     */
    public function toCurrency($value = null, array $options = []);

    /**
     * Set the formatting options.
     *
     * Sets the formatting options of the localized currency string
     * If no parameter is passed, the standard setting of the
     * actual set locale will be used
     *
     * @param  array $options (Optional) Options to set
     * @return CurrencyInterface
     */
    public function setFormat(array $options = []);

    /**
     * Returns the actual or details of other currency symbols, when no symbol is available it returns the shortname.
     *
     * @param  string $currency OPTIONAL Currency name
     * @param  string $locale OPTIONAL Locale to display informations
     * @return string
     */
    public function getSymbol($currency = null, $locale = null);

    /**
     * Returns the actual or details of other currency shortnames
     *
     * @param  string $currency OPTIONAL Currency's name
     * @param  string $locale OPTIONAL The locale
     * @return string
     */
    public function getShortName($currency = null, $locale = null);

    /**
     * Returns the actual or details of other currency names
     *
     * @param  string $currency OPTIONAL Currency's short name
     * @param  string $locale OPTIONAL The locale
     * @return string
     */
    public function getName($currency = null, $locale = null);

    /**
     * Returns a list of regions where this currency is or was known
     *
     * @param  string $currency OPTIONAL Currency's short name
     * @throws CurrencyException When no currency was defined
     * @return array List of regions
     */
    public function getRegionList($currency = null);

    /**
     * Return currency list.
     *
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
     * @return Zend_Cache_Core The set cache
     */
    public static function getCache();

    /**
     * Sets a cache for \Magento\Framework\Currency
     *
     * @param  Zend_Cache_Core $cache Cache to set
     * @return void
     */
    public static function setCache(Zend_Cache_Core $cache);

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
     * @param  string $locale OPTIONAL Locale for parsing input
     * @throws CurrencyException When the given locale does not exist
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
     * @param float|int|CurrencyInterface $value Add this value to currency
     * @param string|CurrencyInterface $currency The currency to add
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function setValue($value, $currency = null);

    /**
     * Adds a currency
     *
     * @param float|int|CurrencyInterface $value Add this value to currency
     * @param string|CurrencyInterface $currency The currency to add
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function add($value, $currency = null);

    /**
     * Substracts a currency
     *
     * @param float|int|CurrencyInterface $value Substracts this value from currency
     * @param string|CurrencyInterface $currency The currency to substract
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function sub($value, $currency = null);

    /**
     * Divides a currency
     *
     * @param float|int|CurrencyInterface $value Divides this value from currency
     * @param string|CurrencyInterface $currency The currency to divide
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function div($value, $currency = null);

    /**
     * Multiplies a currency
     *
     * @param float|int|CurrencyInterface $value Multiplies this value from currency
     * @param string|CurrencyInterface $currency The currency to multiply
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function mul($value, $currency = null);

    /**
     * Calculates the modulo from a currency
     *
     * @param float|int|CurrencyInterface $value Calculate modulo from this value
     * @param string|CurrencyInterface $currency The currency to calculate the modulo
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function mod($value, $currency = null);

    /**
     * Compares two currencies
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return CurrencyInterface
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function compare($value, $currency = null);

    /**
     * Returns true when the two currencies are equal
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return boolean
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function equals($value, $currency = null);

    /**
     * Returns true when the currency is more than the given value
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return boolean
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function isMore($value, $currency = null);

    /**
     * Returns true when the currency is less than the given value
     *
     * @param float|int|CurrencyInterface $value Compares the currency with this value
     * @param string|CurrencyInterface $currency The currency to compare this value from
     * @return boolean
     * @deprecated This approach works incorrect, because Zend_Service no longer exists.
     * @see no alternatives
     */
    public function isLess($value, $currency = null);
}
