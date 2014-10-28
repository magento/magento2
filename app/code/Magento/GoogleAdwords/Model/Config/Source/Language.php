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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleAdwords\Model\Config\Source;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Language implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\GoogleAdwords\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\GoogleAdwords\Model\Filter\UppercaseTitle
     */
    protected $_uppercaseFilter;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\GoogleAdwords\Helper\Data $helper
     * @param \Magento\GoogleAdwords\Model\Filter\UppercaseTitle $uppercaseFilter
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\GoogleAdwords\Helper\Data $helper,
        \Magento\GoogleAdwords\Model\Filter\UppercaseTitle $uppercaseFilter
    ) {
        $this->_helper = $helper;
        $this->_locale = $localeResolver->getLocale();
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

            $label = sprintf(
                '%s / %s (%s)',
                $this->_uppercaseFilter->filter($translationForSpecifiedLanguage),
                $translationForDefaultLanguage,
                $languageCode
            );

            $languages[] = array('value' => $languageCode, 'label' => $label);
        }
        return $languages;
    }
}
