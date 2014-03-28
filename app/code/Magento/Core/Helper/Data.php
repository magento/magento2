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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Helper;

/**
 * Core data helper
 */
class Data extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Currency cache context
     */
    const CONTEXT_CURRENCY = 'current_currency';

    /**
     * Store cache context
     */
    const CONTEXT_STORE = 'core_store';

    const XML_PATH_DEFAULT_COUNTRY = 'general/country/default';

    const XML_PATH_DEV_ALLOW_IPS = 'dev/restrict/allow_ips';

    const XML_PATH_CONNECTION_TYPE = 'global/resources/default_setup/connection/type';

    const XML_PATH_SINGLE_STORE_MODE_ENABLED = 'general/single_store_mode/enabled';

    /**
     * Const for correct dividing decimal values
     */
    const DIVIDE_EPSILON = 10000;

    /**
     * @var string[]
     */
    protected $_allowedFormats = array(
        \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_FULL,
        \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_LONG,
        \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
        \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
    );

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var boolean
     */
    protected $_dbCompatibleMode;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\State $appState
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\State $appState,
        $dbCompatibleMode = true
    ) {
        parent::__construct($context);
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_appState = $appState;
        $this->_dbCompatibleMode = $dbCompatibleMode;
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
        return $this->currencyByStore($value, null, $format, $includeContainer);
    }

    /**
     * Convert and format price value for specified store
     *
     * @param   float $value
     * @param   int|\Magento\Core\Model\Store $store
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  float|string
     */
    public function currencyByStore($value, $store = null, $format = true, $includeContainer = true)
    {
        try {
            if (!$store instanceof \Magento\Core\Model\Store) {
                $store = $this->_storeManager->getStore($store);
            }

            $value = $store->convertPrice($value, $format, $includeContainer);
        } catch (\Exception $e) {
            $value = $e->getMessage();
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
        return $this->currency($value, true, $includeContainer);
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
        return $this->_storeManager->getStore()->formatPrice($price, $includeContainer);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isDevAllowed($storeId = null)
    {
        $allow = true;

        $allowedIps = $this->_coreStoreConfig->getConfig(self::XML_PATH_DEV_ALLOW_IPS, $storeId);
        $remoteAddr = $this->_remoteAddress->getRemoteAddress();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search(
                $remoteAddr,
                $allowedIps
            ) === false && array_search(
                $this->_httpHeader->getHttpHost(),
                $allowedIps
            ) === false
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
        $types = array();
        foreach ($this->_cacheConfig->getTypes() as $type => $node) {
            $types[$type] = $node['label'];
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
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
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
     * @param \Magento\Core\Model\Store|string|int $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_DEFAULT_COUNTRY, $store);
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

    /**
     * Check if Single-Store mode is enabled in configuration
     *
     * This flag only shows that admin does not want to show certain UI components at backend (like store switchers etc)
     * if Magento has only one store view but it does not check the store view collection
     *
     * @return bool
     */
    public function isSingleStoreModeEnabled()
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_SINGLE_STORE_MODE_ENABLED);
    }
}
