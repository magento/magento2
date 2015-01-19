<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend_Locale;
use Magento\Framework\Locale\ConfigInterface;

class Lists
{
    /**
     * Zend locale object
     *
     * @var Zend_Locale
     */
    protected $zendLocale;

    /**
     * List of allowed locales
     *
     * @var array
     */
    protected $allowedLocales;

    /**
     * Constructor
     *
     * @param Zend_Locale $zendLocale
     * @param ConfigInterface $localeConfig
     */
    public function __construct(Zend_Locale $zendLocale, ConfigInterface $localeConfig)
    {
        $this->zendLocale = $zendLocale;
        $this->allowedLocales = $localeConfig->getAllowedLocales();
    }

    /**
     * Retrieve list of timezones
     *
     * @return array
     */
    public function getTimezoneList()
    {
        $timeZone  = $this->zendLocale->getTranslationList('TimezoneToWindows');
        $list = [];
        foreach ($timeZone as $windows => $iso) {
            $list[$iso] = $windows . ' (' . $iso . ')';
        }
        asort($list);
        return $list;
    }

    /**
     * Retrieve list of currencies
     *
     * @return array
     */
    public function getCurrencyList()
    {
        $currencies = $this->zendLocale->getTranslationList('NameToCurrency');
        $list = [];
        foreach ($currencies as $code => $value) {
            $list[$code] = $value . ' (' . $code . ')';
        }
        asort($list);
        return $list;
    }

    /**
     * Retrieve list of locales
     *
     * @return  array
     */
    public function getLocaleList()
    {
        $languages = $this->zendLocale->getTranslationList('Language');
        $countries = $this->zendLocale->getTranslationList('Territory');
        $locales = $this->zendLocale->getLocaleList();

        $allowedAliases = [];
        foreach ($this->allowedLocales as $code) {
            $allowedAliases[$this->zendLocale->getAlias($code)] = $code;
        }

        $list = [];
        foreach (array_keys($locales) as $code) {
            if (array_key_exists($code, $allowedAliases)) {
                $code = $allowedAliases[$code];
            }
            if (strstr($code, '_')) {
                $data = explode('_', $code);
                if (!isset($languages[$data[0]]) || !isset($countries[$data[1]])) {
                    continue;
                }
                $list[$code] = $languages[$data[0]] . ' (' . $countries[$data[1]] . ')';
            }
        }
        asort($list);
        return $list;
    }
}
