<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

interface LocaleInterface
{
    /**
     * Serialization Interface
     *
     * @return string
     */
    public function serialize();

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString();

    /**
     * Returns a string representation of the object
     * Alias for toString
     *
     * @return string
     */
    public function __toString();

    /**
     * Return the default locale
     *
     * @return array Returns an array of all locale string
     */
    public static function getDefault();

    /**
     * Sets a new default locale which will be used when no locale can be detected
     * If provided you can set a quality between 0 and 1 (or 2 and 100)
     * which represents the percent of quality the browser
     * requested within HTTP
     *
     * @param  string|\Magento\Framework\LocaleInterface $locale Locale to set
     * @param float|int $quality The quality to set from 0 to 1
     * @return void
     */
    public static function setDefault($locale, $quality = 1);

    /**
     * Expects the Systems standard locale
     *
     * For Windows:
     * f.e.: LC_COLLATE=C;LC_CTYPE=German_Austria.1252;LC_MONETARY=C
     * would be recognised as de_AT
     *
     * @return array
     */
    public static function getEnvironment();

    /**
     * Return an array of all accepted languages of the client
     * Expects RFC compilant Header !!
     *
     * The notation can be :
     * de,en-UK-US;q=0.5,fr-FR;q=0.2
     *
     * @return array - list of accepted languages including quality
     */
    public static function getBrowser();

    /**
     * Sets a new locale
     *
     * @param  string|\Magento\Framework\LocaleInterface $locale (Optional) New locale to set
     * @return void
     */
    public function setLocale($locale = null);

    /**
     * Returns the language part of the locale
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Returns the region part of the locale if available
     *
     * @return string|false - Regionstring
     */
    public function getRegion();

    /**
     * Return the accepted charset of the client
     *
     * @return string
     */
    public static function getHttpCharset();

    /**
     * Returns true if both locales are equal
     *
     * @param  \Zend_Locale $object Locale to check for equality
     * @return boolean
     */
    public function equals(\Zend_Locale $object);

    /**
     * Returns localized informations as array, supported are several
     * types of informations.
     * For detailed information about the types look into the documentation
     *
     * @param  string             $path   (Optional) Type of information to return
     * @param  string|\Magento\Framework\LocaleInterface $locale (Optional) Locale
     *         |Language for which this informations should be returned
     * @param  string             $value  (Optional) Value for detail list
     * @return array Array with the wished information in the given language
     */
    public static function getTranslationList($path = null, $locale = null, $value = null);

    /**
     * Returns a localized information string, supported are several types of informations.
     * For detailed information about the types look into the documentation
     *
     * @param  string             $value  Name to get detailed information about
     * @param  string             $path   (Optional) Type of information to return
     * @param  string|\Magento\Framework\LocaleInterface $locale (Optional) Locale
     *         |Language for which this informations should be returned
     * @return string|false The wished information in the given language
     */
    public static function getTranslation($value = null, $path = null, $locale = null);

    /**
     * Returns an array with translated yes strings
     *
     * @param  string|\Magento\Framework\LocaleInterface $locale (Optional)
     *         Locale for language translation (defaults to $this locale)
     * @return array
     */
    public static function getQuestion($locale = null);

    /**
     * Checks if a locale identifier is a real locale or not
     * Examples:
     * "en_XX" refers to "en", which returns true
     * "XX_yy" refers to "root", which returns false
     *
     * @param  string|\Magento\Framework\LocaleInterface $locale     Locale to check for
     * @param  boolean            $strict     (Optional) If true, no rerouting will be done when checking
     * @param  boolean            $compatible (DEPRECATED) Only for internal usage, brakes compatibility mode
     * @return boolean If the locale is known dependend on the settings
     */
    public static function isLocale($locale, $strict = false, $compatible = true);

    /**
     * Finds the proper locale based on the input
     * Checks if it exists, degrades it when necessary
     * Detects registry locale and when all fails tries to detect a automatic locale
     * Returns the found locale as string
     *
     * @param string $locale
     * @throws \Zend_Locale_Exception When the given locale is no locale or the autodetection fails
     * @return string
     */
    public static function findLocale($locale = null);

    /**
     * Returns the expected locale for a given territory
     *
     * @param string $territory Territory for which the locale is being searched
     * @return string|null Locale string or null when no locale has been found
     */
    public static function getLocaleToTerritory($territory);

    /**
     * Returns a list of all known locales where the locale is the key
     * Only real locales are returned, the internal locales 'root', 'auto', 'browser'
     * and 'environment' are suppressed
     *
     * @return array List of all Locales
     */
    public static function getLocaleList();

    /**
     * Returns the set cache
     *
     * @return \Zend_Cache_Core The set cache
     */
    public static function getCache();

    /**
     * Sets a cache
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
     * Disables the set cache
     *
     * @param  boolean $flag True disables any set cache, default is false
     * @return void
     */
    public static function disableCache($flag);

    /**
     * Search the locale automatically and return all used locales
     * ordered by quality
     *
     * Standard Searchorder is Browser, Environment, Default
     *
     * @param null $order
     * @internal param string $searchorder (Optional) Searchorder
     * @return array Returns an array of all detected locales
     */
    public static function getOrder($order = null);
}
