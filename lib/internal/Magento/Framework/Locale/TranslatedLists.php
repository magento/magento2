<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Locale;

use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\Bundle\LanguageBundle;
use Magento\Framework\Locale\Bundle\RegionBundle;

class TranslatedLists implements ListsInterface
{
    /**
     * @var \Magento\Framework\Locale\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $locale
     */
    public function __construct(
        \Magento\Framework\Locale\ConfigInterface $config,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $locale = null
    ) {
        $this->_config = $config;
        $this->localeResolver = $localeResolver;
        if ($locale !== null) {
            $this->localeResolver->setLocale($locale);
        }
    }

    /**
     * @inheritdoc
     */
    public function getOptionLocales()
    {
        return $this->_getOptionLocales();
    }

    /**
     * @inheritdoc
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getOptionLocales($translatedName = false)
    {
        $currentLocale = $this->localeResolver->getLocale();
        $locales = \ResourceBundle::getLocales('') ?: [];
        $languages = (new LanguageBundle())->get($currentLocale)['Languages'];
        $countries = (new RegionBundle())->get($currentLocale)['Countries'];

        $options = [];
        $allowedLocales = $this->_config->getAllowedLocales();
        foreach ($locales as $locale) {
            if (!in_array($locale, $allowedLocales)) {
                continue;
            }
            $language = \Locale::getPrimaryLanguage($locale);
            $country = \Locale::getRegion($locale);
            if (!$languages[$language] || !$countries[$country]) {
                continue;
            }
            if ($translatedName) {
                $label = ucwords(\Locale::getDisplayLanguage($locale, $locale))
                    . ' (' . \Locale::getDisplayRegion($locale, $locale) . ') / '
                    . $languages[$language]
                    . ' (' . $countries[$country] . ')';
            } else {
                $label = $languages[$language]
                    . ' (' . $countries[$country] . ')';
            }
            $options[] = ['value' => $locale, 'label' => $label];
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionTimezones()
    {
        $options = [];
        $locale = $this->localeResolver->getLocale();
        $zones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL) ?: [];
        foreach ($zones as $code) {
            $options[] = [
                'label' => \IntlTimeZone::createTimeZone($code)->getDisplayName(
                        false,
                        \IntlTimeZone::DISPLAY_LONG,
                        $locale
                    ) . ' (' . $code . ')',
                'value' => $code,
            ];
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionWeekdays($preserveCodes = false, $ucFirstCode = false)
    {
        $options = [];
        $days = (new DataBundle())
            ->get($this->localeResolver->getLocale())['calendar']['gregorian']['dayNames']['format']['wide'] ?: [];
        $englishDays = (new DataBundle())->get('en_US')['calendar']['gregorian']['dayNames']['format']['abbreviated'];
        foreach ($days as $code => $name) {
            $code = $preserveCodes ? $englishDays[$code] : $code;
            $options[] = ['label' => $name, 'value' => $ucFirstCode ? ucfirst($code) : $code];
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function getOptionCountries()
    {
        $options = [];
        $countries = (new RegionBundle())->get($this->localeResolver->getLocale())['Countries'] ?: [];
        foreach ($countries as $code => $name) {
            $options[] = ['label' => $name, 'value' => $code];
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionCurrencies()
    {
        $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'] ?: [];
        $options = [];
        $allowed = $this->_config->getAllowedCurrencies();
        foreach ($currencies as $code => $data) {
            if (!in_array($code, $allowed)) {
                continue;
            }
            $options[] = ['label' => $data[1], 'value' => $code];
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionAllCurrencies()
    {
        $currencyBundle = new \Magento\Framework\Locale\Bundle\CurrencyBundle();
        $locale = $this->localeResolver->getLocale();
        $currencies = $currencyBundle->get($locale)['Currencies'] ?: [];

        $options = [];
        foreach ($currencies as $code => $data) {
            $options[] = ['label' => $data[1], 'value' => $code];
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @param array $option
     * @return array
     */
    protected function _sortOptionArray($option)
    {
        $data = [];
        foreach ($option as $item) {
            $data[$item['value']] = $item['label'];
        }
        asort($data);
        $option = [];
        foreach ($data as $key => $label) {
            $option[] = ['value' => $key, 'label' => $label];
        }
        return $option;
    }

    /**
     * @inheritdoc
     */
    public function getCountryTranslation($value, $locale = null)
    {
        if ($locale == null) {
            return (new RegionBundle())->get($this->localeResolver->getLocale())['Countries'][$value];
        } else {
            return (new RegionBundle())->get($locale)['Countries'][$value];
        }
    }
}
