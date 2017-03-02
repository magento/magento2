<?php
/**
 * Google AdWords language source
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Model\Config\Source;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Language implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\GoogleAdwords\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\GoogleAdwords\Model\Filter\UppercaseTitle
     */
    protected $_uppercaseFilter;

    /**
     * @param \Magento\GoogleAdwords\Helper\Data $helper
     * @param \Magento\GoogleAdwords\Model\Filter\UppercaseTitle $uppercaseFilter
     */
    public function __construct(
        \Magento\GoogleAdwords\Helper\Data $helper,
        \Magento\GoogleAdwords\Model\Filter\UppercaseTitle $uppercaseFilter
    ) {
        $this->_helper = $helper;
        $this->_uppercaseFilter = $uppercaseFilter;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $languages = [];
        foreach ($this->_helper->getLanguageCodes() as $languageCode) {
            $localeCode = $this->_helper->convertLanguageCodeToLocaleCode($languageCode);
            $translationForSpecifiedLanguage = \Locale::getDisplayLanguage($localeCode, $localeCode);
            $translationForDefaultLanguage = \Locale::getDisplayLanguage($localeCode);

            $label = sprintf(
                '%s / %s (%s)',
                $this->_uppercaseFilter->filter($translationForSpecifiedLanguage),
                $translationForDefaultLanguage,
                $languageCode
            );

            $languages[] = ['value' => $languageCode, 'label' => $label];
        }
        return $languages;
    }
}
