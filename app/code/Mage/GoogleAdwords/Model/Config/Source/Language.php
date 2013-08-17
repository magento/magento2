<?php
/**
 * Google AdWords language source
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Mage_GoogleAdwords_Model_Config_Source_Language implements Mage_Core_Model_Option_ArrayInterface
{
    /**
     * @var Zend_Locale
     */
    protected $_locale;

    /**
     * @var Mage_GoogleAdwords_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_GoogleAdwords_Model_Filter_UppercaseTitle
     */
    protected $_uppercaseFilter;

    /**
     * Constructor
     *
     * @param Mage_Core_Model_LocaleInterface $locale
     * @param Mage_GoogleAdwords_Helper_Data $helper
     * @param Mage_GoogleAdwords_Model_Filter_UppercaseTitle $uppercaseFilter
     */
    public function __construct(
        Mage_Core_Model_LocaleInterface $locale,
        Mage_GoogleAdwords_Helper_Data $helper,
        Mage_GoogleAdwords_Model_Filter_UppercaseTitle $uppercaseFilter
    ) {
        $this->_helper = $helper;
        $this->_locale = $locale->getLocale();
        $this->_uppercaseFilter = $uppercaseFilter;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $languages = array();
        foreach ($this->_helper->getLanguageCodes() as $languageCode) {
            $localeCode = $this->_helper->convertLanguageCodeToLocaleCode($languageCode);
            $translationForSpecifiedLanguage = $this->_locale->getTranslation($localeCode, 'language', $languageCode);
            $translationForDefaultLanguage = $this->_locale->getTranslation($localeCode, 'language');

            $label = sprintf('%s / %s (%s)', $this->_uppercaseFilter->filter($translationForSpecifiedLanguage),
                $translationForDefaultLanguage, $languageCode);

            $languages[] = array(
                'value' => $languageCode,
                'label' => $label,
            );
        }
        return $languages;
    }
}
