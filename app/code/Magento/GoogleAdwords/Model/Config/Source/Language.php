<?php
/**
 * Google AdWords language source
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Model\Config\Source;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @api
 * @since 2.0.0
 */
class Language implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\GoogleAdwords\Helper\Data
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @var \Magento\GoogleAdwords\Model\Filter\UppercaseTitle
     * @since 2.0.0
     */
    protected $_uppercaseFilter;

    /**
     * @param \Magento\GoogleAdwords\Helper\Data $helper
     * @param \Magento\GoogleAdwords\Model\Filter\UppercaseTitle $uppercaseFilter
     * @since 2.0.0
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
     * @since 2.0.0
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
