<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Locale;

class Lists implements ListsInterface
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
        $locales = \ResourceBundle::getLocales(null);
        $languages = (new \ResourceBundle($currentLocale, 'ICUDATA-lang'))['Languages'];
        $countries = (new \ResourceBundle($currentLocale, 'ICUDATA-region'))['Countries'];

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
        $zones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC);
        foreach ($zones as $code) {
            $options[] = [
                'label' => \IntlTimeZone::createTimeZone($code)->getDisplayName(
                        false,
                        \IntlTimeZone::DISPLAY_LONG,
                        $locale
                    ) . ' (' . $code . ')',
                'value' => $code
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
        $days = (new \ResourceBundle(
            $this->localeResolver->getLocale(),
            'ICUDATA'
        ))['calendar']['gregorian']['dayNames']['format']['wide'];
        $englishDays = (new \ResourceBundle(
            'en',
            'ICUDATA'
        ))['calendar']['gregorian']['dayNames']['format']['abbreviated'];
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
        $countries = (new \ResourceBundle($this->localeResolver->getLocale(), 'ICUDATA-region'))['Countries'];
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
        $currencies = (new \ResourceBundle($this->localeResolver->getLocale(), 'ICUDATA-curr'))['Currencies'];
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
        $currencies = (new \ResourceBundle($this->localeResolver->getLocale(), 'ICUDATA-curr'))['Currencies'];
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
    public function getCountryTranslation($value)
    {
        return (new \ResourceBundle($this->localeResolver->getLocale(), 'ICUDATA-region'))['Countries'][$value];
    }
}
