<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Core data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Currency cache context
     */
    const CONTEXT_CURRENCY = 'current_currency';

    /**
     * Store cache context
     */
    const CONTEXT_STORE = 'store';

    /**#@+
     * Paths for various config settings
     */
    const XML_PATH_DEFAULT_LOCALE = 'general/locale/code';
    const XML_PATH_DEFAULT_TIMEZONE = 'general/locale/timezone';
    const XML_PATH_DEFAULT_COUNTRY = 'general/country/default';
    const XML_PATH_DEV_ALLOW_IPS = 'dev/restrict/allow_ips';
    const XML_PATH_CONNECTION_TYPE = 'global/resources/default_setup/connection/type';
    /**#@- */

    /**
     * Const for correct dividing decimal values
     */
    const DIVIDE_EPSILON = 10000;

    /**
     * @var string[]
     */
    protected $_allowedFormats = [
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_FULL,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_LONG,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
    ];

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var boolean
     */
    protected $_dbCompatibleMode;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $appState
     * @param PriceCurrencyInterface $priceCurrency
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        PriceCurrencyInterface $priceCurrency,
        $dbCompatibleMode = true
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_appState = $appState;
        $this->_dbCompatibleMode = $dbCompatibleMode;
        $this->_priceCurrency =  $priceCurrency;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  float|string
     */
    public function currency($value, $format = true, $includeContainer = true)
    {
        return $format
            ? $this->_priceCurrency->convertAndFormat($value, $includeContainer)
            : $this->_priceCurrency->convert($value);
    }

    /**
     * Convert and format price value for specified store
     *
     * @param   float $value
     * @param   int|\Magento\Store\Model\Store $store
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  float|string
     */
    public function currencyByStore($value, $store = null, $format = true, $includeContainer = true)
    {
        if ($format) {
            $value = $this->_priceCurrency->convertAndFormat(
                $value,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $store
            );
        } else {
            $value = $this->_priceCurrency->convert($value, $store);
        }

        return $value;
    }

    /**
     * Format and convert currency using current store option
     *
     * @param   float $value
     * @param   bool $includeContainer
     * @return  string
     */
    public function formatCurrency($value, $includeContainer = true)
    {
        return $this->_priceCurrency->convertAndFormat($value, $includeContainer);
    }

    /**
     * Formats price
     *
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPrice($price, $includeContainer = true)
    {
        return $this->_priceCurrency->format($price, $includeContainer);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isDevAllowed($storeId = null)
    {
        $allow = true;

        $allowedIps = $this->_scopeConfig->getValue(
            self::XML_PATH_DEV_ALLOW_IPS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $remoteAddr = $this->_remoteAddress->getRemoteAddress();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($remoteAddr, $allowedIps) === false
                && array_search($this->_httpHeader->getHttpHost(), $allowedIps) === false
            ) {
                $allow = false;
            }
        }

        return $allow;
    }

    /**
     * Get information about available cache types
     *
     * @return array
     */
    public function getCacheTypes()
    {
        $types = [];
        foreach ($this->_cacheConfig->getTypes() as $type => $node) {
            if (array_key_exists('label', $node)) {
                $types[$type] = $node['label'];
            }
        }
        return $types;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = [])
    {
        $json = \Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        $this->translateInline->processResponseBody($json, true);
        return $json;
    }

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * @param string $encodedValue
     * @param int $objectDecodeType
     * @return mixed
     */
    public function jsonDecode($encodedValue, $objectDecodeType = \Zend_Json::TYPE_ARRAY)
    {
        return \Zend_Json::decode($encodedValue, $objectDecodeType);
    }

    /**
     * Return default country code
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DEFAULT_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check whether database compatible mode is used (configs enable it for MySQL by default).
     *
     * @return bool
     */
    public function useDbCompatibleMode()
    {
        return $this->_dbCompatibleMode;
    }
}
