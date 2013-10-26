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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Locale model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model;

class Locale implements \Magento\Core\Model\LocaleInterface
{
    /**
     * Default locale code
     *
     * @var string
     */
    protected $_defaultLocale;

    /**
     * Locale object
     *
     * @var \Zend_Locale
     */
    protected $_locale;

    /**
     * Locale code
     *
     * @var string
     */
    protected $_localeCode;

    /**
     * Emulated locales stack
     *
     * @var array
     */
    protected $_emulatedLocales = array();

    protected static $_currencyCache = array();

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Core\Helper\Translate
     */
    protected $_translate;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Locale\Config
     */
    protected $_config;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Translate $translate
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Locale\Config $config
     * @param \Magento\Core\Model\App $app
     * @param string $locale
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Translate $translate,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\App\State $appState,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Locale\Config $config,
        \Magento\Core\Model\App $app,
        $locale = null
    ) {
        $this->_eventManager = $eventManager;
        $this->_translate = $translate;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_app = $app;
        $this->setLocale($locale);
    }

    /**
     * Set default locale code
     *
     * @param   string $locale
     * @return  \Magento\Core\Model\LocaleInterface
     */
    public function setDefaultLocale($locale)
    {
        $this->_defaultLocale = $locale;
        return $this;
    }

    /**
     * REtrieve default locale code
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        if (!$this->_defaultLocale) {
            $locale = $this->_coreStoreConfig->getConfig(\Magento\Core\Model\LocaleInterface::XML_PATH_DEFAULT_LOCALE);
            if (!$locale) {
                $locale = \Magento\Core\Model\LocaleInterface::DEFAULT_LOCALE;
            }
            $this->_defaultLocale = $locale;
        }
        return $this->_defaultLocale;
    }

    /**
     * Set locale
     *
     * @param   string $locale
     * @return  \Magento\Core\Model\LocaleInterface
     */
    public function setLocale($locale = null)
    {
        if (($locale !== null) && is_string($locale)) {
            $this->_localeCode = $locale;
        } else {
            $this->_localeCode = $this->getDefaultLocale();
        }
        return $this;
    }

    /**
     * Retrieve timezone code
     *
     * @return string
     */
    public function getTimezone()
    {
        return \Magento\Core\Model\LocaleInterface::DEFAULT_TIMEZONE;
    }

    /**
     * Retrieve currency code
     *
     * @return string
     */
    public function getCurrency()
    {
        return \Magento\Core\Model\LocaleInterface::DEFAULT_CURRENCY;
    }

    /**
     * Retrieve locale object
     *
     * @return \Zend_Locale
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            \Zend_Locale_Data::setCache($this->_app->getCache()->getLowLevelFrontend());
            $this->_locale = new \Zend_Locale($this->getLocaleCode());
        } elseif ($this->_locale->__toString() != $this->_localeCode) {
            $this->setLocale($this->_localeCode);
        }

        return $this->_locale;
    }

    /**
     * Retrieve locale code
     *
     * @return string
     */
    public function getLocaleCode()
    {
        if ($this->_localeCode === null) {
            $this->setLocale();
        }
        return $this->_localeCode;
    }

    /**
     * Specify current locale code
     *
     * @param   string $code
     * @return  \Magento\Core\Model\LocaleInterface
     */
    public function setLocaleCode($code)
    {
        $this->_localeCode = $code;
        $this->_locale = null;
        return $this;
    }

    /**
     * Get options array for locale dropdown in currunt locale
     *
     * @return array
     */
    public function getOptionLocales()
    {
        return $this->_getOptionLocales();
    }

    /**
     * Get translated to original locale options array for locale dropdown
     *
     * @return array
     */
    public function getTranslatedOptionLocales()
    {
        return $this->_getOptionLocales(true);
    }

    /**
     * Get options array for locale dropdown
     *
     * @param   bool $translatedName translation flag
     * @return  array
     */
    protected function _getOptionLocales($translatedName=false)
    {
        $options    = array();
        $locales    = $this->getLocale()->getLocaleList();
        $languages  = $this->getLocale()->getTranslationList('language', $this->getLocale());
        $countries  = $this->getCountryTranslationList();

        $allowed    = $this->getAllowLocales();
        foreach ($locales as $code=>$active) {
            if (strstr($code, '_')) {
                if (!in_array($code, $allowed)) {
                    continue;
                }
                $data = explode('_', $code);
                if (!isset($languages[$data[0]]) || !isset($countries[$data[1]])) {
                    continue;
                }
                if ($translatedName) {
                    $label = ucwords($this->getLocale()->getTranslation($data[0], 'language', $code))
                        . ' (' . $this->getLocale()->getTranslation($data[1], 'country', $code) . ') / '
                        . $languages[$data[0]] . ' (' . $countries[$data[1]] . ')';
                } else {
                    $label = $languages[$data[0]] . ' (' . $countries[$data[1]] . ')';
                }
                $options[] = array(
                    'value' => $code,
                    'label' => $label
                );
            }
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * Retrieve timezone option list
     *
     * @return array
     */
    public function getOptionTimezones()
    {
        $options= array();
        $zones  = $this->getTranslationList('windowstotimezone');
        ksort($zones);
        foreach ($zones as $code=>$name) {
            $name = trim($name);
            $options[] = array(
               'label' => empty($name) ? $code : $name . ' (' . $code . ')',
               'value' => $code,
            );
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * Retrieve days of week option list
     *
     * @return array
     */
    public function getOptionWeekdays()
    {
        $options= array();
        $days = $this->getTranslationList('days');
        foreach (array_values($days['format']['wide']) as $code => $name) {
            $options[] = array(
               'label' => $name,
               'value' => $code,
            );
        }
        return $options;
    }

    /**
     * Retrieve country option list
     *
     * @return array
     */
    public function getOptionCountries()
    {
        $options    = array();
        $countries  = $this->getCountryTranslationList();

        foreach ($countries as $code=>$name) {
            $options[] = array(
               'label' => $name,
               'value' => $code,
            );
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * Retrieve currency option list
     *
     * @return unknown
     */
    public function getOptionCurrencies()
    {
        $currencies = $this->getTranslationList('currencytoname');
        $options = array();
        $allowed = $this->getAllowCurrencies();

        foreach ($currencies as $name=>$code) {
            if (!in_array($code, $allowed)) {
                continue;
            }

            $options[] = array(
               'label' => $name,
               'value' => $code,
            );
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * Retrieve all currency option list
     *
     * @return unknown
     */
    public function getOptionAllCurrencies()
    {
        $currencies = $this->getTranslationList('currencytoname');
        $options = array();
        foreach ($currencies as $name=>$code) {
            $options[] = array(
               'label' => $name,
               'value' => $code,
            );
        }
        return $this->_sortOptionArray($options);
    }

    protected function _sortOptionArray($option)
    {
        $data = array();
        foreach ($option as $item) {
            $data[$item['value']] = $item['label'];
        }
        asort($data);
        $option = array();
        foreach ($data as $key => $label) {
            $option[] = array(
               'value' => $key,
               'label' => $label
            );
        }
        return $option;
    }

    /**
     * Retrieve array of allowed locales
     *
     * @return array
     */
    public function getAllowLocales()
    {
        return $this->_config->getAllowedLocales();
    }

    /**
     * Retrieve array of allowed currencies
     *
     * @return unknown
     */
    public function getAllowCurrencies()
    {
        if ($this->_appState->isInstalled()) {
            $data = $this->_storeManager->getStore()->getConfig(\Magento\Core\Model\LocaleInterface::XML_PATH_ALLOW_CURRENCIES_INSTALLED);
            return explode(',', $data);
        } else {
            $data = $this->_config->getAllowedCurrencies();
        }
        return $data;
    }

    /**
     * Retrieve ISO date format
     *
     * @param   string $type
     * @return  string
     */
    public function getDateFormat($type=null)
    {
        return $this->getTranslation($type, 'date');
    }

    /**
     * Retrieve short date format with 4-digit year
     *
     * @return  string
     */
    public function getDateFormatWithLongYear()
    {
        return preg_replace('/(?<!y)yy(?!y)/', 'yyyy',
            $this->getTranslation(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT, 'date'));
    }


    /**
     * Retrieve ISO time format
     *
     * @param   string $type
     * @return  string
     */
    public function getTimeFormat($type=null)
    {
        return $this->getTranslation($type, 'time');
    }

    /**
     * Retrieve ISO datetime format
     *
     * @param   string $type
     * @return  string
     */
    public function getDateTimeFormat($type)
    {
        return $this->getDateFormat($type) . ' ' . $this->getTimeFormat($type);
    }

    /**
     * Create \Zend_Date object for current locale
     *
     * @param mixed              $date
     * @param string             $part
     * @param string|Zend_Locale $locale
     * @param bool               $useTimezone
     * @return \Zend_Date
     */
    public function date($date = null, $part = null, $locale = null, $useTimezone = true)
    {
        if (is_null($locale)) {
            $locale = $this->getLocale();
        }

        if (empty($date)) {
            // $date may be false, but \Zend_Date uses strict compare
            $date = null;
        }
        $date = new \Zend_Date($date, $part, $locale);
        if ($useTimezone) {
            if ($timezone = $this->_storeManager->getStore()->getConfig(\Magento\Core\Model\LocaleInterface::XML_PATH_DEFAULT_TIMEZONE)) {
                $date->setTimezone($timezone);
            }
        }

        return $date;
    }

    /**
     * Create \Zend_Date object with date converted to store timezone and store Locale
     *
     * @param   mixed $store Information about store
     * @param   string|integer|Zend_Date|array|null $date date in UTC
     * @param   boolean $includeTime flag for including time to date
     * @return  \Zend_Date
     */
    public function storeDate($store=null, $date=null, $includeTime=false)
    {
        $timezone = $this->_storeManager->getStore($store)->getConfig(\Magento\Core\Model\LocaleInterface::XML_PATH_DEFAULT_TIMEZONE);
        $date = new \Zend_Date($date, null, $this->getLocale());
        $date->setTimezone($timezone);
        if (!$includeTime) {
            $date->setHour(0)
                ->setMinute(0)
                ->setSecond(0);
        }
        return $date;
    }

    /**
     * Create \Zend_Date object with date converted from store's timezone
     * to UTC time zone. Date can be passed in format of store's locale
     * or in format which was passed as parameter.
     *
     * @param mixed $store Information about store
     * @param string|integer|Zend_Date|array|null $date date in store's timezone
     * @param boolean $includeTime flag for including time to date
     * @param null|string $format
     * @return \Zend_Date
     */
    public function utcDate($store, $date, $includeTime = false, $format = null)
    {
        $dateObj = $this->storeDate($store, $date, $includeTime);
        $dateObj->set($date, $format);
        $dateObj->setTimezone(\Magento\Core\Model\LocaleInterface::DEFAULT_TIMEZONE);
        return $dateObj;
    }

    /**
     * Get store timestamp
     * Timstamp will be builded with store timezone settings
     *
     * @param   mixed $store
     * @return  int
     */
    public function storeTimeStamp($store=null)
    {
        $timezone = $this->_storeManager->getStore($store)->getConfig(\Magento\Core\Model\LocaleInterface::XML_PATH_DEFAULT_TIMEZONE);
        $currentTimezone = @date_default_timezone_get();
        @date_default_timezone_set($timezone);
        $date = date('Y-m-d H:i:s');
        @date_default_timezone_set($currentTimezone);
        return strtotime($date);
    }

    /**
     * Create \Zend_Currency object for current locale
     *
     * @param   string $currency
     * @return  \Zend_Currency
     */
    public function currency($currency)
    {
        \Magento\Profiler::start('locale/currency');
        if (!isset(self::$_currencyCache[$this->getLocaleCode()][$currency])) {
            $options = array();
            try {
                $currencyObject = new \Zend_Currency($currency, $this->getLocale());
            } catch (\Exception $e) {
                $currencyObject = new \Zend_Currency($this->getCurrency(), $this->getLocale());
                $options['name'] = $currency;
                $options['currency'] = $currency;
                $options['symbol'] = $currency;
            }

            $options = new \Magento\Object($options);
            $this->_eventManager->dispatch('currency_display_options_forming', array(
                'currency_options' => $options,
                'base_code' => $currency
            ));

            $currencyObject->setFormat($options->toArray());
            self::$_currencyCache[$this->getLocaleCode()][$currency] = $currencyObject;
        }
        \Magento\Profiler::stop('locale/currency');
        return self::$_currencyCache[$this->getLocaleCode()][$currency];
    }

    /**
     * Returns the first found number from an string
     * Parsing depends on given locale (grouping and decimal)
     *
     * Examples for input:
     * '  2345.4356,1234' = 23455456.1234
     * '+23,3452.123' = 233452.123
     * ' 12343 ' = 12343
     * '-9456km' = -9456
     * '0' = 0
     * '2 054,10' = 2054.1
     * '2'054.52' = 2054.52
     * '2,46 GB' = 2.46
     *
     * @param string|float|int $value
     * @return float|null
     */
    public function getNumber($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (!is_string($value)) {
            return floatval($value);
        }

        //trim spaces and apostrophes
        $value = str_replace(array('\'', ' '), '', $value);

        $separatorComa = strpos($value, ',');
        $separatorDot  = strpos($value, '.');

        if ($separatorComa !== false && $separatorDot !== false) {
            if ($separatorComa > $separatorDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($separatorComa !== false) {
            $value = str_replace(',', '.', $value);
        }

        return floatval($value);
    }

    /**
     * Functions returns array with price formatting info for js function
     * formatCurrency in js/varien/js.js
     *
     * @return array
     */
    public function getJsPriceFormat()
    {
        $format = \Zend_Locale_Data::getContent($this->getLocaleCode(), 'currencynumber');
        $symbols = \Zend_Locale_Data::getList($this->getLocaleCode(), 'symbols');

        $pos = strpos($format, ';');
        if ($pos !== false){
            $format = substr($format, 0, $pos);
        }
        $format = preg_replace("/[^0\#\.,]/", "", $format);
        $totalPrecision = 0;
        $decimalPoint = strpos($format, '.');
        if ($decimalPoint !== false) {
            $totalPrecision = (strlen($format) - (strrpos($format, '.')+1));
        } else {
            $decimalPoint = strlen($format);
        }
        $requiredPrecision = $totalPrecision;
        $t = substr($format, $decimalPoint);
        $pos = strpos($t, '#');
        if ($pos !== false){
            $requiredPrecision = strlen($t) - $pos - $totalPrecision;
        }
        $group = 0;
        if (strrpos($format, ',') !== false) {
            $group = ($decimalPoint - strrpos($format, ',') - 1);
        } else {
            $group = strrpos($format, '.');
        }
        $integerRequired = (strpos($format, '.') - strpos($format, '0'));

        $result = array(
            'pattern' => $this->_storeManager->getStore()->getCurrentCurrency()->getOutputFormat(),
            'precision' => $totalPrecision,
            'requiredPrecision' => $requiredPrecision,
            'decimalSymbol' => $symbols['decimal'],
            'groupSymbol' => $symbols['group'],
            'groupLength' => $group,
            'integerRequired' => $integerRequired
        );

        return $result;
    }

    /**
     * Push current locale to stack and replace with locale from specified store
     * Event is not dispatched.
     *
     * @param int $storeId
     */
    public function emulate($storeId)
    {
        if ($storeId) {
            $this->_emulatedLocales[] = clone $this->getLocale();
            $this->_locale = new \Zend_Locale(
                $this->_coreStoreConfig->getConfig(
                    \Magento\Core\Model\LocaleInterface::XML_PATH_DEFAULT_LOCALE, $storeId
            ));
            $this->_localeCode = $this->_locale->toString();

            $this->_translate->initTranslate($this->_localeCode, \Magento\Core\Model\App\Area::AREA_FRONTEND, true);
        } else {
            $this->_emulatedLocales[] = false;
        }
    }

    /**
     * Get last locale, used before last emulation
     *
     */
    public function revert()
    {
        $locale = array_pop($this->_emulatedLocales);
        if ($locale) {
            $this->_locale = $locale;
            $this->_localeCode = $this->_locale->toString();

            $this->_translate->initTranslate($this->_localeCode, \Magento\Core\Model\App\Area::AREA_ADMINHTML, true);
        }
    }

    /**
     * Returns localized informations as array, supported are several
     * types of informations.
     * For detailed information about the types look into the documentation
     *
     * @param  string             $path   (Optional) Type of information to return
     * @param  string             $value  (Optional) Value for detail list
     * @return array Array with the wished information in the given language
     */
    public function getTranslationList($path = null, $value = null)
    {
        return $this->getLocale()->getTranslationList($path, $this->getLocale(), $value);
    }

    /**
     * Returns a localized information string, supported are several types of informations.
     * For detailed information about the types look into the documentation
     *
     * @param  string             $value  Name to get detailed information about
     * @param  string             $path   (Optional) Type of information to return
     * @return string|false The wished information in the given language
     */
    public function getTranslation($value = null, $path = null)
    {
        return $this->getLocale()->getTranslation($value, $path, $this->getLocale());
    }

    /**
     * Returns the localized country name
     *
     * @param  $value string Name to get detailed information about
     * @return array
     */
    public function getCountryTranslation($value)
    {
        return $this->getLocale()->getTranslation($value, 'country', $this->getLocale());
    }

    /**
     * Returns an array with the name of all countries translated to the given language
     *
     * @return array
     */
    public function getCountryTranslationList()
    {
        return $this->getLocale()->getTranslationList('territory', $this->getLocale(), 2);
    }

    /**
     * Checks if current date of the given store (in the store timezone) is within the range
     *
     * @param int|string|\Magento\Core\Model\Store $store
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return bool
     */
    public function isStoreDateInInterval($store, $dateFrom = null, $dateTo = null)
    {
        if (!$store instanceof \Magento\Core\Model\Store) {
            $store = $this->_storeManager->getStore($store);
        }

        $storeTimeStamp = $this->storeTimeStamp($store);
        $fromTimeStamp  = strtotime($dateFrom);
        $toTimeStamp    = strtotime($dateTo);
        if ($dateTo) {
            // fix date YYYY-MM-DD 00:00:00 to YYYY-MM-DD 23:59:59
            $toTimeStamp += 86400;
        }

        $result = false;
        if (!is_empty_date($dateFrom) && $storeTimeStamp < $fromTimeStamp) {
        } elseif (!is_empty_date($dateTo) && $storeTimeStamp > $toTimeStamp) {
        } else {
            $result = true;
        }
        return $result;
    }
}
