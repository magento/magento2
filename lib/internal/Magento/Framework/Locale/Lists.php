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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $options = array();
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

        $allowed = $this->_config->getAllowedLocales();
        foreach (array_keys($locales) as $code) {
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
                $options[] = array('value' => $code, 'label' => $label);
            }
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionTimezones()
    {
        $options = array();
        $zones = $this->getTranslationList('windowstotimezone');
        ksort($zones);
        foreach ($zones as $code => $name) {
            $name = trim($name);
            $options[] = array('label' => empty($name) ? $code : $name . ' (' . $code . ')', 'value' => $code);
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionWeekdays($preserveCodes = false, $ucFirstCode = false)
    {
        $options = array();
        $days = $this->getTranslationList('days');
        $days = $preserveCodes ? $days['format']['wide'] : array_values($days['format']['wide']);
        foreach ($days as $code => $name) {
            $options[] = array('label' => $name, 'value' => $ucFirstCode ? ucfirst($code) : $code);
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function getOptionCountries()
    {
        $options = array();
        $countries = $this->getCountryTranslationList();

        foreach ($countries as $code => $name) {
            $options[] = array('label' => $name, 'value' => $code);
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionCurrencies()
    {
        $currencies = $this->getTranslationList('currencytoname');
        $options = array();
        $allowed = $this->_config->getAllowedCurrencies();

        foreach ($currencies as $name => $code) {
            if (!in_array($code, $allowed)) {
                continue;
            }

            $options[] = array('label' => $name, 'value' => $code);
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptionAllCurrencies()
    {
        $currencies = $this->getTranslationList('currencytoname');
        $options = array();
        foreach ($currencies as $name => $code) {
            $options[] = array('label' => $name, 'value' => $code);
        }
        return $this->_sortOptionArray($options);
    }

    /**
     * @param array $option
     * @return array
     */
    protected function _sortOptionArray($option)
    {
        $data = array();
        foreach ($option as $item) {
            $data[$item['value']] = $item['label'];
        }
        asort($data);
        $option = array();
        foreach ($data as $key => $label) {
            $option[] = array('value' => $key, 'label' => $label);
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
