<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Locale\Bundle\LanguageBundle;
use Magento\Framework\Locale\Bundle\RegionBundle;
use Magento\Framework\Locale\ConfigInterface;
use Magento\Framework\Locale\ResolverInterface;

class Lists
{
    /**
     * List of allowed locales
     *
     * @var array
     */
    protected $allowedLocales;

    /**
     * @param ConfigInterface $localeConfig
     */
    public function __construct(ConfigInterface $localeConfig)
    {
        $this->allowedLocales = $localeConfig->getAllowedLocales();
    }

    /**
     * Retrieve list of timezones
     *
     * @return array
     */
    public function getTimezoneList()
    {
        $zones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $list = [];
        foreach ($zones as $code) {
            $list[$code] = \IntlTimeZone::createTimeZone($code)->getDisplayName(
                false,
                \IntlTimeZone::DISPLAY_LONG,
                ResolverInterface::DEFAULT_LOCALE
            ) . ' (' . $code . ')';
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
        $currencies = (new CurrencyBundle())->get(ResolverInterface::DEFAULT_LOCALE)['Currencies'];
        $list = [];
        foreach ($currencies as $code => $data) {
            $list[$code] = $data[1] . ' (' . $code . ')';
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
        $languages = (new LanguageBundle())->get(ResolverInterface::DEFAULT_LOCALE)['Languages'];
        $countries = (new RegionBundle())->get(ResolverInterface::DEFAULT_LOCALE)['Countries'];
        $locales = \ResourceBundle::getLocales(null);

        $list = [];
        foreach ($locales as $locale) {
            if (!in_array($locale, $this->allowedLocales)) {
                continue;
            }
            $language = \Locale::getPrimaryLanguage($locale);
            $country = \Locale::getRegion($locale);
            if (!$languages[$language] || !$countries[$country]) {
                continue;
            }
            $list[$locale] = $languages[$language] . ' (' . $countries[$country] . ')';
        }
        asort($list);
        return $list;
    }
}
