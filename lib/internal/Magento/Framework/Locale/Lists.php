<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

class Lists implements \Magento\Framework\Locale\ListsInterface
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @var \Magento\Framework\Locale\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Locale\ConfigInterface $config
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $locale
     */
    public function __construct(
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Locale\ConfigInterface $config,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $locale = null
    ) {
        $this->_scopeResolver = $scopeResolver;
        $this->_config = $config;
        $this->_localeResolver = $localeResolver;
        if ($locale !== null) {
            $this->_localeResolver->setLocale($locale);
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
     */
    protected function _getOptionLocales($translatedName = false)
    {
        $options = [];
        $locales = $this->_localeResolver->getLocale()->getLocaleList();
        $languages = $this->_localeResolver->getLocale()->getTranslationList(
            'language',
            $this->_localeResolver->getLocale()
        );
        $countries = $this->_localeResolver->getLocale()->getTranslationList(
            'territory',
            $this->_localeResolver->getLocale(),
            2
        );

        //Zend locale codes for internal allowed locale codes
        $allowed = $this->_config->getAllowedLocales();
        $allowedAliases = [];
        foreach ($allowed as $code) {
            $allowedAliases[\Zend_Locale::getAlias($code)] = $code;
        }

        //Process translating to internal locale codes from Zend locale codes
        $processedLocales = [];
        foreach ($locales as $code => $active) {
            if (array_key_exists($code, $allowedAliases)) {
                $processedLocales[$allowedAliases[$code]] = $active;
            } else {
                $processedLocales[$code] = $active;
            }
        }

        foreach (array_keys($processedLocales) as $code) {
            if (strstr($code, '_')) {
                if (!in_array($code, $allowed)) {
                    continue;
                }
                $data = explode('_', $code);
                if (!isset($languages[$data[0]]) || !isset($countries[$data[1]])) {
                    continue;
                }
                if ($translatedName) {
                    $label = ucwords(
                        $this->_localeResolver->getLocale()->getTranslation($data[0], 'language', $code)
                    ) . ' (' . $this->_localeResolver->getLocale()->getTranslation(
                        $data[1],
                        'country',
                        $code
                    ) . ') / ' . $languages[$data[0]] . ' (' . $countries[$data[1]] . ')';
                } else {
                    $label = $languages[$data[0]] . ' (' . $countries[$data[1]] . ')';
                }
                $options[] = ['value' => $code, 'label' => $label];
            }
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionTimezones()
    {
        $options = [];
        $zones = $this->getTranslationList('timezonetowindows');
        foreach ($zones as $windowsTimezones => $isoTimezones) {
            $windowsTimezones = trim($windowsTimezones);
            $options[] = ['label' => empty($windowsTimezones) ? $isoTimezones : $windowsTimezones . ' (' . $isoTimezones . ')', 'value' => $isoTimezones];
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionWeekdays($preserveCodes = false, $ucFirstCode = false)
    {
        $options = [];
        $days = $this->getTranslationList('days');
        $days = $preserveCodes ? $days['format']['wide'] : array_values($days['format']['wide']);
        foreach ($days as $code => $name) {
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
        $countries = $this->getCountryTranslationList();

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
        $currencies = $this->getTranslationList('currencytoname');
        $options = [];
        $allowed = $this->_config->getAllowedCurrencies();

        foreach ($currencies as $name => $code) {
            if (!in_array($code, $allowed)) {
                continue;
            }

            $options[] = ['label' => $name, 'value' => $code];
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionAllCurrencies()
    {
        $currencies = $this->getTranslationList('currencytoname');
        $options = [];
        foreach ($currencies as $name => $code) {
            $options[] = ['label' => $name, 'value' => $code];
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
    public function getTranslationList($path = null, $value = null)
    {
        return $this->_localeResolver->getLocale()->getTranslationList(
            $path,
            $this->_localeResolver->getLocale(),
            $value
        );
    }

    /**
     * @inheritdoc
     */
    public function getCountryTranslation($value)
    {
        $locale = $this->_localeResolver->getLocale();
        return $locale->getTranslation($value, 'country', $locale);
    }

    /**
     * @inheritdoc
     */
    public function getCountryTranslationList()
    {
        $locale = $this->_localeResolver->getLocale();
        return $locale->getTranslationList('territory', $locale, 2);
    }
}
