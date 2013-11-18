<?php
/**
 * Google AdWords Data Helper
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
 */
namespace Magento\GoogleAdwords\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
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
    CONST CONVERSION_VALUE_DEFAULT = 0;

    /**#@+
     * Google AdWords config data
     */
    const XML_PATH_ACTIVE  = 'google/adwords/active';
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
    CONST CONVERSION_VALUE_TYPE_DYNAMIC = 1;
    CONST CONVERSION_VALUE_TYPE_CONSTANT = 0;
    /**#@-*/

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Core\Model\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Store\ConfigInterface $storeConfig
     * @param \Magento\Core\Model\Registry $registry
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Store\ConfigInterface $storeConfig,
        \Magento\Core\Model\Registry $registry
    ) {
        parent::__construct($context);
        $this->_config = $config;
        $this->_storeConfig = $storeConfig;
        $this->_registry = $registry;
    }

    /**
     * Is Google AdWords active
     *
     * @return bool
     */
    public function isGoogleAdwordsActive()
    {
        return $this->_storeConfig->getConfigFlag(self::XML_PATH_ACTIVE)
            && $this->getConversionId()
            && $this->getConversionLanguage()
            && $this->getConversionFormat()
            && $this->getConversionColor()
            && $this->getConversionLabel();
    }

    /**
     * Retrieve language codes from config
     *
     * @return array
     */
    public function getLanguageCodes()
    {
        return (array)$this->_config->getValue(self::XML_PATH_LANGUAGES, 'default');
    }

    /**
     * Convert language code in the code of the current locale language
     *
     * @param string $language
     * @return string
     */
    public function convertLanguageCodeToLocaleCode($language)
    {
        $convertArray = (array)$this->_config->getValue(self::XML_PATH_LANGUAGE_CONVERT, 'default');
        return isset($convertArray[$language]) ? $convertArray[$language] : $language;
    }

    /**
     * Get conversion path to js src
     *
     * @return string
     */
    public function getConversionJsSrc()
    {
        return (string)$this->_config->getValue(self::XML_PATH_CONVERSION_JS_SRC, 'default');
    }

    /**
     * Get conversion img src
     *
     * @return string
     */
    public function getConversionImgSrc()
    {
        return sprintf($this->_config->getValue(self::XML_PATH_CONVERSION_IMG_SRC, 'default'), $this->getConversionId(),
            $this->getConversionLabel());
    }

    /**
     * Get Google AdWords conversion id
     *
     * @return int
     */
    public function getConversionId()
    {
        return (int)$this->_storeConfig->getConfig(self::XML_PATH_CONVERSION_ID);
    }

    /**
     * Get Google AdWords conversion language
     *
     * @return string
     */
    public function getConversionLanguage()
    {
        return $this->_storeConfig->getConfig(self::XML_PATH_CONVERSION_LANGUAGE);
    }

    /**
     * Get Google AdWords conversion format
     *
     * @return int
     */
    public function getConversionFormat()
    {
        return $this->_storeConfig->getConfig(self::XML_PATH_CONVERSION_FORMAT);
    }

    /**
     * Get Google AdWords conversion color
     *
     * @return string
     */
    public function getConversionColor()
    {
        return $this->_storeConfig->getConfig(self::XML_PATH_CONVERSION_COLOR);
    }

    /**
     * Get Google AdWords conversion label
     *
     * @return string
     */
    public function getConversionLabel()
    {
        return $this->_storeConfig->getConfig(self::XML_PATH_CONVERSION_LABEL);
    }

    /**
     * Get Google AdWords conversion value type
     *
     * @return string
     */
    public function getConversionValueType()
    {
        return $this->_storeConfig->getConfig(self::XML_PATH_CONVERSION_VALUE_TYPE);
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
        return (float)$this->_storeConfig->getConfig(self::XML_PATH_CONVERSION_VALUE);
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
}
