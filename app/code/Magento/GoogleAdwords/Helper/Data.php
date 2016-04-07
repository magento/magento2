<?php
/**
 * Google AdWords Data Helper
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * Google AdWords language codes
     */
    const XML_PATH_LANGUAGES = 'google/adwords/languages';

    const XML_PATH_LANGUAGE_CONVERT = 'google/adwords/language_convert';

    /**#@-*/

    /**#@+
     * Google AdWords conversion src
     */
    const XML_PATH_CONVERSION_JS_SRC = 'google/adwords/conversion_js_src';

    const XML_PATH_CONVERSION_IMG_SRC = 'google/adwords/conversion_img_src';

    /**#@-*/

    /**
     * Google AdWords registry name for conversion value
     */
    const CONVERSION_VALUE_REGISTRY_NAME = 'google_adwords_conversion_value';

    /**
     * Default value for conversion value
     */
    const CONVERSION_VALUE_DEFAULT = 0;

    /**#@+
     * Google AdWords config data
     */
    const XML_PATH_ACTIVE = 'google/adwords/active';

    const XML_PATH_CONVERSION_ID = 'google/adwords/conversion_id';

    const XML_PATH_CONVERSION_LANGUAGE = 'google/adwords/conversion_language';

    const XML_PATH_CONVERSION_FORMAT = 'google/adwords/conversion_format';

    const XML_PATH_CONVERSION_COLOR = 'google/adwords/conversion_color';

    const XML_PATH_CONVERSION_LABEL = 'google/adwords/conversion_label';

    const XML_PATH_CONVERSION_VALUE_TYPE = 'google/adwords/conversion_value_type';

    const XML_PATH_CONVERSION_VALUE = 'google/adwords/conversion_value';

    /**#@-*/

    /**#@+
     * Conversion value types
     */
    const CONVERSION_VALUE_TYPE_DYNAMIC = 1;

    const CONVERSION_VALUE_TYPE_CONSTANT = 0;

    /**#@-*/

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
    }

    /**
     * Is Google AdWords active
     *
     * @return bool
     */
    public function isGoogleAdwordsActive()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) &&
            $this->getConversionId() &&
            $this->getConversionLanguage() &&
            $this->getConversionFormat() &&
            $this->getConversionColor() &&
            $this->getConversionLabel();
    }

    /**
     * Retrieve language codes from config
     *
     * @return string[]
     */
    public function getLanguageCodes()
    {
        return (array)$this->scopeConfig->getValue(self::XML_PATH_LANGUAGES, 'default');
    }

    /**
     * Convert language code in the code of the current locale language
     *
     * @param string $language
     * @return string
     */
    public function convertLanguageCodeToLocaleCode($language)
    {
        $convertArray = (array)$this->scopeConfig->getValue(self::XML_PATH_LANGUAGE_CONVERT, 'default');
        return isset($convertArray[$language]) ? $convertArray[$language] : $language;
    }

    /**
     * Get conversion path to js src
     *
     * @return string
     */
    public function getConversionJsSrc()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CONVERSION_JS_SRC, 'default');
    }

    /**
     * Get conversion img src
     *
     * @return string
     */
    public function getConversionImgSrc()
    {
        return sprintf(
            $this->scopeConfig->getValue(self::XML_PATH_CONVERSION_IMG_SRC, 'default'),
            $this->getConversionId(),
            $this->getConversionLabel()
        );
    }

    /**
     * Get Google AdWords conversion id
     *
     * @return int
     */
    public function getConversionId()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion language
     *
     * @return string
     */
    public function getConversionLanguage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_LANGUAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion format
     *
     * @return int
     */
    public function getConversionFormat()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_FORMAT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion color
     *
     * @return string
     */
    public function getConversionColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_COLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion label
     *
     * @return string
     */
    public function getConversionLabel()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_LABEL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion value type
     *
     * @return string
     */
    public function getConversionValueType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_VALUE_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks if conversion value is dynamic
     *
     * @return bool
     */
    public function isDynamicConversionValue()
    {
        return $this->getConversionValueType() == self::CONVERSION_VALUE_TYPE_DYNAMIC;
    }

    /**
     * Get Google AdWords conversion value constant
     *
     * @return float
     */
    public function getConversionValueConstant()
    {
        return (double)$this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_VALUE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion value
     *
     * @return float
     */
    public function getConversionValue()
    {
        if ($this->isDynamicConversionValue()) {
            $conversionValue = (double)$this->_registry->registry(self::CONVERSION_VALUE_REGISTRY_NAME);
        } else {
            $conversionValue = $this->getConversionValueConstant();
        }
        return empty($conversionValue) ? self::CONVERSION_VALUE_DEFAULT : $conversionValue;
    }
}
