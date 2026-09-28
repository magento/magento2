<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GoogleAdwords\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * @api
 * @since 100.0.2
 */
class Data extends AbstractHelper
{
    /**#@+
     * Google AdWords language codes
     */
    public const XML_PATH_LANGUAGES = 'google/adwords/languages';

    public const XML_PATH_LANGUAGE_CONVERT = 'google/adwords/language_convert';

    /**#@-*/

    /**#@+
     * Google AdWords conversion src
     */
    public const XML_PATH_CONVERSION_JS_SRC = 'google/adwords/conversion_js_src';

    public const XML_PATH_CONVERSION_IMG_SRC = 'google/adwords/conversion_img_src';

    /**#@-*/

    /**
     * Google AdWords registry name for conversion value
     */
    public const CONVERSION_VALUE_REGISTRY_NAME = 'google_adwords_conversion_value';

    /**
     * Google AdWords registry name for conversion value currency
     */
    public const CONVERSION_VALUE_CURRENCY_REGISTRY_NAME = 'google_adwords_conversion_value_currency';

    /**
     * Default value for conversion value
     */
    public const CONVERSION_VALUE_DEFAULT = 0;

    /**#@+
     * Google AdWords config data
     */
    public const XML_PATH_ACTIVE = 'google/adwords/active';

    public const XML_PATH_CONVERSION_ID = 'google/adwords/conversion_id';

    public const XML_PATH_CONVERSION_LANGUAGE = 'google/adwords/conversion_language';

    public const XML_PATH_CONVERSION_FORMAT = 'google/adwords/conversion_format';

    public const XML_PATH_CONVERSION_COLOR = 'google/adwords/conversion_color';

    public const XML_PATH_CONVERSION_LABEL = 'google/adwords/conversion_label';

    public const XML_PATH_CONVERSION_VALUE_TYPE = 'google/adwords/conversion_value_type';

    public const XML_PATH_CONVERSION_VALUE = 'google/adwords/conversion_value';

    /**
     * Google Adwords send order conversion value currency when using dynamic value
     */
    public const XML_PATH_SEND_CURRENCY = 'google/adwords/send_currency';

    /**#@-*/

    /**#@+
     * Conversion value types
     */
    public const CONVERSION_VALUE_TYPE_DYNAMIC = 1;

    public const CONVERSION_VALUE_TYPE_CONSTANT = 0;

    /**#@-*/

    /**#@-*/

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Registry $registry
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE
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
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_VALUE,
            ScopeInterface::SCOPE_STORE
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
            $conversionValue = (float)$this->_registry->registry(self::CONVERSION_VALUE_REGISTRY_NAME);
        } else {
            $conversionValue = $this->getConversionValueConstant();
        }
        return empty($conversionValue) ? self::CONVERSION_VALUE_DEFAULT : $conversionValue;
    }

    /**
     * Get send order currency to Google Adwords
     *
     * @return boolean
     * @since 100.3.0
     */
    public function hasSendConversionValueCurrency()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_CURRENCY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion value currency
     *
     * @return string|false
     * @since 100.3.0
     */
    public function getConversionValueCurrency()
    {
        if ($this->hasSendConversionValueCurrency()) {
            return (string) $this->_registry->registry(self::CONVERSION_VALUE_CURRENCY_REGISTRY_NAME);
        }
        return false;
    }
}
