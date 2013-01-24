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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store model
 *
 * @method Mage_Core_Model_Resource_Store _getResource()
 * @method Mage_Core_Model_Resource_Store getResource()
 * @method Mage_Core_Model_Store setCode(string $value)
 * @method Mage_Core_Model_Store setWebsiteId(int $value)
 * @method Mage_Core_Model_Store setGroupId(int $value)
 * @method Mage_Core_Model_Store setName(string $value)
 * @method int getSortOrder()
 * @method Mage_Core_Model_Store setSortOrder(int $value)
 * @method Mage_Core_Model_Store setIsActive(int $value)
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Store extends Mage_Core_Model_Abstract
{
    /**
     * Entity name
     */
    const ENTITY = 'core_store';

    /**
     * Configuration pathes
     */
    const XML_PATH_STORE_STORE_NAME       = 'general/store_information/name';
    const XML_PATH_STORE_STORE_PHONE      = 'general/store_information/phone';
    const XML_PATH_STORE_IN_URL           = 'web/url/use_store';
    const XML_PATH_USE_REWRITES           = 'web/seo/use_rewrites';
    const XML_PATH_UNSECURE_BASE_URL      = 'web/unsecure/base_url';
    const XML_PATH_SECURE_BASE_URL        = 'web/secure/base_url';
    const XML_PATH_SECURE_IN_FRONTEND     = 'web/secure/use_in_frontend';
    const XML_PATH_SECURE_IN_ADMINHTML    = 'web/secure/use_in_adminhtml';
    const XML_PATH_SECURE_BASE_LINK_URL   = 'web/secure/base_link_url';
    const XML_PATH_UNSECURE_BASE_LINK_URL = 'web/unsecure/base_link_url';
    const XML_PATH_OFFLOADER_HEADER       = 'web/secure/offloader_header';
    const XML_PATH_PRICE_SCOPE            = 'catalog/price/scope';

    /**
     * Price scope constants
     */
    const PRICE_SCOPE_GLOBAL              = 0;
    const PRICE_SCOPE_WEBSITE             = 1;

    /**
     * Possible URL types
     */
    const URL_TYPE_LINK                   = 'link';
    const URL_TYPE_DIRECT_LINK            = 'direct_link';
    const URL_TYPE_WEB                    = 'web';
    const URL_TYPE_JS                     = 'js';
    const URL_TYPE_MEDIA                  = 'media';
    const URL_TYPE_THEME                  = 'theme';

    /**
     * Code constants
     */
    const DEFAULT_CODE                    = 'default';
    const ADMIN_CODE                      = 'admin';

    /**
     * Cache tag
     */
    const CACHE_TAG                       = 'store';

    /**
     * Cookie name
     */
    const COOKIE_NAME                     = 'store';

    /**
     * Cookie currency key
     */
    const COOKIE_CURRENCY                 = 'currency';

    /**
     * Script name, which returns all the images
     */
    const MEDIA_REWRITE_SCRIPT            = 'get.php/';

    /**
     * Cache flag
     *
     * @var boolean
     */
    protected $_cacheTag    = true;

    /**
     * Event prefix for model events
     *
     * @var string
     */
    protected $_eventPrefix = 'store';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'store';

    /**
     * Price filter
     *
     * @var Mage_Directory_Model_Currency_Filter
     */
    protected $_priceFilter;

    /**
     * Website model
     *
     * @var Mage_Core_Model_Website
     */
    protected $_website;

    /**
     * Group model
     *
     * @var Mage_Core_Model_Store_Group
     */
    protected $_group;

    /**
     * Store configuration cache
     *
     * @var array|null
     */
    protected $_configCache = null;

    /**
     * Base nodes of store configuration cache
     *
     * @var array
     */
    protected $_configCacheBaseNodes = array();

    /**
     * Directory cache
     *
     * @var array
     */
    protected $_dirCache = array();

    /**
     * URL cache
     *
     * @var array
     */
    protected $_urlCache = array();

    /**
     * Base URL cache
     *
     * @var array
     */
    protected $_baseUrlCache = array();

    /**
     * Session entity
     *
     * @var Mage_Core_Model_Session_Abstract
     */
    protected $_session;

    /**
     * Flag that shows that backend URLs are secure
     *
     * @var boolean|null
     */
    protected $_isAdminSecure = null;

    /**
     * Flag that shows that frontend URLs are secure
     *
     * @var boolean|null
     */
    protected $_isFrontSecure = null;

    /**
     * Store frontend name
     *
     * @var string|null
     */
    protected $_frontendName = null;

    /**
     * Readonly flag
     *
     * @var bool
     */
    private $_isReadOnly = false;

    /**
     * Url model for current store
     *
     * @var Mage_Core_Model_Url
     */
    protected $_urlModel = null;

    /**
     * Url class name for current store
     *
     * @var bool
     */
    protected $_urlClassName = null;

    /** Default url class name for current store */
    const DEFAULT_URL_MODEL_NAME = 'Mage_Core_Model_Url';

    /**
     * Initialize object
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Store');
        $this->_configCacheBaseNodes = array(
            self::XML_PATH_PRICE_SCOPE,
            self::XML_PATH_SECURE_BASE_URL,
            self::XML_PATH_SECURE_IN_ADMINHTML,
            self::XML_PATH_SECURE_IN_FRONTEND,
            self::XML_PATH_STORE_IN_URL,
            self::XML_PATH_UNSECURE_BASE_URL,
            self::XML_PATH_USE_REWRITES,
            self::XML_PATH_UNSECURE_BASE_LINK_URL,
            self::XML_PATH_SECURE_BASE_LINK_URL,
            'general/locale/code'
        );
    }

    /**
     * Retrieve store session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        if (!$this->_session) {
            $this->_session = Mage::getModel('Mage_Core_Model_Session')
                ->init('store_'.$this->getCode());
        }
        return $this->_session;
    }

    /**
     * Loading store data
     *
     * @param   mixed $id
     * @param   string $field
     * @return  Mage_Core_Model_Store
     */
    public function load($id, $field=null)
    {
        if (!is_numeric($id) && is_null($field)) {
            $this->_getResource()->load($this, $id, 'code');
            return $this;
        }
        return parent::load($id, $field);
    }

    /**
     * Loading store configuration data
     *
     * @param   string $code
     * @return  Mage_Core_Model_Store
     */
    public function loadConfig($code)
    {
        if (is_numeric($code)) {
            foreach (Mage::getConfig()->getNode()->stores->children() as $storeCode => $store) {
                if ((int) $store->system->store->id == $code) {
                    $code = $storeCode;
                    break;
                }
            }
        } else {
            $store = Mage::getConfig()->getNode()->stores->{$code};
        }
        if (!empty($store)) {
            $this->setCode($code);
            $id = (int) $store->system->store->id;
            $this->setId($id)->setStoreId($id);
            $this->setWebsiteId((int) $store->system->website->id);
        }
        return $this;
    }

    /**
     * Retrieve Store code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_getData('code');
    }

    /**
     * Retrieve store configuration data
     *
     * @param   string $path
     * @return  string|null
     */
    public function getConfig($path)
    {
        if (isset($this->_configCache[$path])) {
            return $this->_configCache[$path];
        }

        $config = Mage::getConfig();

        $fullPath = 'stores/' . $this->getCode() . '/' . $path;
        $data = $config->getNode($fullPath);
        if (!$data && !Mage::isInstalled()) {
            $data = $config->getNode('default/' . $path);
        }
        if (!$data) {
            return null;
        }
        return $this->_processConfigValue($fullPath, $path, $data);
    }

    /**
     * Initialize base store configuration data
     *
     * Method provide cache configuration data without loading store config XML
     *
     * @return Mage_Core_Model_Config
     */
    public function initConfigCache()
    {
        /**
         * Functionality related with config separation
         */
        if ($this->_configCache === null) {
            $code = $this->getCode();
            if ($code) {
                if (Mage::app()->useCache('config')) {
                    $cacheId = 'store_' . $code . '_config_cache';
                    $data = Mage::app()->loadCache($cacheId);
                    if ($data) {
                        $data = unserialize($data);
                    } else {
                        $data = array();
                        foreach ($this->_configCacheBaseNodes as $node) {
                            $data[$node] = $this->getConfig($node);
                        }
                        Mage::app()->saveCache(serialize($data), $cacheId, array(
                            self::CACHE_TAG,
                            Mage_Core_Model_Config::CACHE_TAG
                        ));
                    }
                    $this->_configCache = $data;
                }
            }
        }
        return $this;
    }

    /**
     * Set config value for CURRENT model
     *
     * This value don't save in config
     *
     * @param string $path
     * @param mixed $value
     * @return Mage_Core_Model_Store
     */
    public function setConfig($path, $value)
    {
        if (isset($this->_configCache[$path])) {
            $this->_configCache[$path] = $value;
        }
        $fullPath = 'stores/' . $this->getCode() . '/' . $path;
        Mage::getConfig()->setNode($fullPath, $value);

        return $this;
    }

    /**
     * Set website model
     *
     * @param Mage_Core_Model_Website $website
     */
    public function setWebsite(Mage_Core_Model_Website $website)
    {
        $this->_website = $website;
    }

    /**
     * Retrieve store website
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        if (is_null($this->getWebsiteId())) {
            return false;
        }
        if (is_null($this->_website)) {
            $this->_website = Mage::app()->getWebsite($this->getWebsiteId());
        }
        return $this->_website;
    }

    /**
     * Process config value
     *
     * @param string $fullPath
     * @param string $path
     * @param Varien_Simplexml_Element $node
     * @return string
     */
    protected function _processConfigValue($fullPath, $path, $node)
    {
        if (isset($this->_configCache[$path])) {
            return $this->_configCache[$path];
        }

        if ($node->hasChildren()) {
            $aValue = array();
            foreach ($node->children() as $k => $v) {
                $aValue[$k] = $this->_processConfigValue($fullPath . '/' . $k, $path . '/' . $k, $v);
            }
            $this->_configCache[$path] = $aValue;
            return $aValue;
        }

        $sValue = (string) $node;
        if (!empty($node['backend_model']) && !empty($sValue)) {
            $backend = Mage::getModel((string) $node['backend_model']);
            $backend->setPath($path)->setValue($sValue)->afterLoad();
            $sValue = $backend->getValue();
        }

        if (is_string($sValue) && preg_match('/{{(.*)}}.*/', $sValue, $matches)) {
            $placeholder = $matches[1];
            $url = false;
            if ($placeholder == 'unsecure_base_url' || $placeholder == 'unsecure_public_url') {
                $url = $this->getConfig(self::XML_PATH_UNSECURE_BASE_URL);
            } elseif ($placeholder == 'secure_base_url' || $placeholder == 'secure_public_url') {
                $url = $this->getConfig(self::XML_PATH_SECURE_BASE_URL);
            }
            if ($placeholder == 'unsecure_public_url' || $placeholder == 'secure_public_url') {
                $pubName = Mage_Core_Model_Config_Options::PUB_DIRECTORY;
                $url.= (substr(dirname($_SERVER['SCRIPT_FILENAME']), -4) == '/' . $pubName) ? '' : $pubName . '/';
                // @TODO: investigate how to build correct public URLs from API
                if (Mage::registry('custom_entry_point')) {
                    $url .= $pubName . '/';
                }
            }

            if ($url) {
                $sValue = str_replace('{{' . $placeholder . '}}', $url, $sValue);
            } elseif (strpos($sValue, '{{base_url}}') !== false) {
                $sValue = Mage::getConfig()->substDistroServerVars($sValue);
            }
        }

        $this->_configCache[$path] = $sValue;

        return $sValue;
    }

    /**
     * Retrieve default base path
     *
     * @return string
     */
    public function getDefaultBasePath()
    {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return '/';
        }
        return rtrim(Mage::app()->getRequest()->getBasePath() . '/') . '/';
    }

    /**
     * Retrieve url using store configuration specific
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = array())
    {
        /** @var $url Mage_Core_Model_Url */
        $url = $this->getUrlModel()
            ->setStore($this);
        if (Mage::app()->getStore()->getId() != $this->getId()) {
            $params['_store_to_url'] = true;
        }

        return $url->getUrl($route, $params);
    }

    /**
     * Retrieve base URL
     *
     * @param string $type
     * @param boolean|null $secure
     * @return string
     */
    public function getBaseUrl($type = self::URL_TYPE_LINK, $secure = null)
    {
        $cacheKey = $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
            switch ($type) {
                case self::URL_TYPE_WEB:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_url');
                    break;

                case self::URL_TYPE_LINK:
                    $secure = (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_link_url');
                    $url = $this->_updatePathUseRewrites($url);
                    $url = $this->_updatePathUseStoreView($url);
                    break;

                case self::URL_TYPE_DIRECT_LINK:
                    $secure = (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_link_url');
                    $url = $this->_updatePathUseRewrites($url);
                    break;

                case self::URL_TYPE_JS:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_public_url') . 'lib/';
                    break;

                case self::URL_TYPE_THEME:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_public_url')
                        . 'media/theme/';
                    break;

                case self::URL_TYPE_MEDIA:
                    $url = $this->_updateMediaPathUseRewrites($secure);
                    break;

                default:
                    throw Mage::exception('Mage_Core', Mage::helper('Mage_Core_Helper_Data')->__('Invalid base url type'));
            }

            if (false !== strpos($url, '{{base_url}}')) {
                $baseUrl = Mage::getConfig()->substDistroServerVars('{{base_url}}');
                $url = str_replace('{{base_url}}', $baseUrl, $url);
            }

            $this->_baseUrlCache[$cacheKey] = rtrim($url, '/') . '/';
        }

        return $this->_baseUrlCache[$cacheKey];
    }

    /**
     * Remove script file name from url in case when server rewrites are enabled
     *
     * @param   string $url
     * @return  string
     */
    protected function _updatePathUseRewrites($url)
    {
        if ($this->isAdmin()
            || !$this->getConfig(self::XML_PATH_USE_REWRITES)
            || !Mage::isInstalled()
        ) {
            if ($this->_isCustomEntryPoint()) {
                $indexFileName = 'index.php';
            } else {
                $indexFileName = basename($_SERVER['SCRIPT_FILENAME']);
            }
            $url .= $indexFileName . '/';
        }
        return $url;
    }

    /**
     * Check if used entry point is custom
     *
     * @return bool
     */
    protected function _isCustomEntryPoint()
    {
        return (bool)Mage::registry('custom_entry_point');
    }

    /**
     * Retrieve URL for media catalog
     *
     * If we use Database file storage and server doesn't support rewrites (.htaccess in media folder)
     * we have to put name of fetching media script exactly into URL
     *
     * @param null|boolean $secure
     * @param string $type
     * @return string
     */
    protected function _updateMediaPathUseRewrites($secure = null, $type = self::URL_TYPE_MEDIA)
    {
        $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool) $secure;
        $secureStringFlag = $secure ? 'secure' : 'unsecure';
        $url = $this->getConfig('web/' . $secureStringFlag . '/base_' . $type . '_url');
        if (!$this->getConfig(self::XML_PATH_USE_REWRITES)
            && Mage::helper('Mage_Core_Helper_File_Storage_Database')->checkDbUsage()
        ) {
            $urlStart = $this->getConfig('web/' . $secureStringFlag . '/base_public_url');
            $url = str_replace($urlStart, $urlStart . self::MEDIA_REWRITE_SCRIPT, $url);
        }
        return $url;
    }

    /**
     * Add store code to url in case if it is enabled in configuration
     *
     * @param   string $url
     * @return  string
     */
    protected function _updatePathUseStoreView($url)
    {
        if ($this->getStoreInUrl()) {
            $url .= $this->getCode() . '/';
        }
        return $url;
    }

    /**
     * Returns whether url forming scheme prepends url path with store view code
     *
     * @return bool
     */
    public function getStoreInUrl()
    {
        return Mage::isInstalled() && $this->getConfig(self::XML_PATH_STORE_IN_URL);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getId()
    {
        return $this->_getData('store_id');
    }

    /**
     * Check if store is admin store
     *
     * @return unknown
     */
    public function isAdmin()
    {
        return $this->getId() == Mage_Core_Model_App::ADMIN_STORE_ID;
    }


    /**
     * Check if backend URLs should be secure
     *
     * @return boolean
     */
    public function isAdminUrlSecure()
    {
        if ($this->_isAdminSecure === null) {
            $this->_isAdminSecure = (boolean) (int) (string) Mage::getConfig()
                ->getNode(Mage_Core_Model_Url::XML_PATH_SECURE_IN_ADMIN);
        }
        return $this->_isAdminSecure;
    }

    /**
     * Check if frontend URLs should be secure
     *
     * @return boolean
     */
    public function isFrontUrlSecure()
    {
        if ($this->_isFrontSecure === null) {
            $this->_isFrontSecure = Mage::getStoreConfigFlag(Mage_Core_Model_Url::XML_PATH_SECURE_IN_FRONT,
                $this->getId());
        }
        return $this->_isFrontSecure;
    }

    /**
     * Check if request was secure
     *
     * @return boolean
     */
    public function isCurrentlySecure()
    {
        $standardRule = !empty($_SERVER['HTTPS']) && ('off' != $_SERVER['HTTPS']);
        $offloaderHeader = trim((string) Mage::getConfig()->getNode(self::XML_PATH_OFFLOADER_HEADER, 'default'));

        if ((!empty($offloaderHeader) && !empty($_SERVER[$offloaderHeader])) || $standardRule) {
            return true;
        }

        if (Mage::isInstalled()) {
            $secureBaseUrl = Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_URL);

            if (!$secureBaseUrl) {
                return false;
            }

            $uri = Zend_Uri::factory($secureBaseUrl);
            $port = $uri->getPort();
            $isSecure = ($uri->getScheme() == 'https')
                && isset($_SERVER['SERVER_PORT'])
                && ($port == $_SERVER['SERVER_PORT']);
            return $isSecure;
        } else {
            $isSecure = isset($_SERVER['SERVER_PORT']) && (443 == $_SERVER['SERVER_PORT']);
            return $isSecure;
        }
    }

    /*************************************************************************************
     * Store currency interface
     */

    /**
     * Retrieve store base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        $configValue = $this->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
        if ($configValue == Mage_Core_Model_Store::PRICE_SCOPE_GLOBAL) {
            return Mage::app()->getBaseCurrencyCode();
        } else {
            return $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        }
    }

    /**
     * Retrieve store base currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency()
    {
        $currency = $this->getData('base_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('Mage_Directory_Model_Currency')->load($this->getBaseCurrencyCode());
            $this->setData('base_currency', $currency);
        }
        return $currency;
    }

    /**
     * Get default store currency code
     *
     * @return string
     */
    public function getDefaultCurrencyCode()
    {
        $result = $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT);
        return $result;
    }

    /**
     * Retrieve store default currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getDefaultCurrency()
    {
        $currency = $this->getData('default_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('Mage_Directory_Model_Currency')->load($this->getDefaultCurrencyCode());
            $this->setData('default_currency', $currency);
        }
        return $currency;
    }

    /**
     * Set current store currency code
     *
     * @param   string $code
     * @return  string
     */
    public function setCurrentCurrencyCode($code)
    {
        $code = strtoupper($code);
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            $this->_getSession()->setCurrencyCode($code);
            if ($code == $this->getDefaultCurrency()) {
                Mage::app()->getCookie()->delete(self::COOKIE_CURRENCY, $code);
            } else {
                Mage::app()->getCookie()->set(self::COOKIE_CURRENCY, $code);
            }
        }
        return $this;
    }

    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        // try to get currently set code among allowed
        $code = $this->_getSession()->getCurrencyCode();
        if (empty($code)) {
            $code = $this->getDefaultCurrencyCode();
        }
        if (in_array($code, $this->getAvailableCurrencyCodes(true))) {
            return $code;
        }

        // take first one of allowed codes
        $codes = array_values($this->getAvailableCurrencyCodes(true));
        if (empty($codes)) {
            // return default code, if no codes specified at all
            return $this->getDefaultCurrencyCode();
        }
        return array_shift($codes);
    }

    /**
     * Get allowed store currency codes
     *
     * If base currency is not allowed in current website config scope,
     * then it can be disabled with $skipBaseNotAllowed
     *
     * @param bool $skipBaseNotAllowed
     * @return array
     */
    public function getAvailableCurrencyCodes($skipBaseNotAllowed = false)
    {
        $codes = $this->getData('available_currency_codes');
        if (is_null($codes)) {
            $codes = explode(',', $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_ALLOW));
            // add base currency, if it is not in allowed currencies
            $baseCurrencyCode = $this->getBaseCurrencyCode();
            if (!in_array($baseCurrencyCode, $codes)) {
                $codes[] = $baseCurrencyCode;

                // save base currency code index for further usage
                $disallowedBaseCodeIndex = array_keys($codes);
                $disallowedBaseCodeIndex = array_pop($disallowedBaseCodeIndex);
                $this->setData('disallowed_base_currency_code_index', $disallowedBaseCodeIndex);
            }
            $this->setData('available_currency_codes', $codes);
        }

        // remove base currency code, if it is not allowed by config (optional)
        if ($skipBaseNotAllowed) {
            $disallowedBaseCodeIndex = $this->getData('disallowed_base_currency_code_index');
            if (null !== $disallowedBaseCodeIndex) {
                unset($codes[$disallowedBaseCodeIndex]);
            }
        }
        return $codes;
    }

    /**
     * Retrieve store current currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrentCurrency()
    {
        $currency = $this->getData('current_currency');

        if (is_null($currency)) {
            $currency     = Mage::getModel('Mage_Directory_Model_Currency')->load($this->getCurrentCurrencyCode());
            $baseCurrency = $this->getBaseCurrency();

            if (! $baseCurrency->getRate($currency)) {
                $currency = $baseCurrency;
                $this->setCurrentCurrencyCode($baseCurrency->getCode());
            }

            $this->setData('current_currency', $currency);
        }

        return $currency;
    }

    /**
     * Retrieve current currency rate
     *
     * @return float
     */
    public function getCurrentCurrencyRate()
    {
        return $this->getBaseCurrency()->getRate($this->getCurrentCurrency());
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param   double $price
     * @param   boolean $format             Format price to currency format
     * @param   boolean $includeContainer   Enclose into <span class="price"><span>
     * @return  double
     */
    public function convertPrice($price, $format = false, $includeContainer = true)
    {
        if ($this->getCurrentCurrency() && $this->getBaseCurrency()) {
            $value = $this->getBaseCurrency()->convert($price, $this->getCurrentCurrency());
        } else {
            $value = $price;
        }

        if ($this->getCurrentCurrency() && $format) {
            $value = $this->formatPrice($value, $includeContainer);
        }
        return $value;
    }

    /**
     * Round price
     *
     * @param mixed $price
     * @return double
     */
    public function roundPrice($price)
    {
        return round($price, 2);
    }

    /**
     * Format price with currency filter (taking rate into consideration)
     *
     * @param   double $price
     * @param   bool $includeContainer
     * @return  string
     */
    public function formatPrice($price, $includeContainer = true)
    {
        if ($this->getCurrentCurrency()) {
            return $this->getCurrentCurrency()->format($price, array(), $includeContainer);
        }
        return $price;
    }

    /**
     * Get store price filter
     *
     * @return Varien_Filter_Sprintf
     */
    public function getPriceFilter()
    {
        if (!$this->_priceFilter) {
            if ($this->getBaseCurrency() && $this->getCurrentCurrency()) {
                $this->_priceFilter = $this->getCurrentCurrency()->getFilter();
                $this->_priceFilter->setRate($this->getBaseCurrency()->getRate($this->getCurrentCurrency()));
            }
            elseif($this->getDefaultCurrency()) {
                $this->_priceFilter = $this->getDefaultCurrency()->getFilter();
            }
            else {
                $this->_priceFilter = new Varien_Filter_Sprintf('%s', 2);
            }
        }
        return $this->_priceFilter;
    }

    /**
     * Retrieve root category identifier
     *
     * @return int
     */
    public function getRootCategoryId()
    {
        if (!$this->getGroup()) {
            return 0;
        }
        return $this->getGroup()->getRootCategoryId();
    }

    /**
     * Set group model for store
     *
     * @param Mage_Core_Model_Store_Group $group
     */
    public function setGroup($group)
    {
        $this->_group = $group;
    }

    /**
     * Retrieve group model
     *
     * @return Mage_Core_Model_Store_Group
     */
    public function getGroup()
    {
        if (is_null($this->getGroupId())) {
            return false;
        }
        if (is_null($this->_group)) {
            $this->_group = Mage::getModel('Mage_Core_Model_Store_Group')->load($this->getGroupId());
        }
        return $this->_group;
    }

    /**
     * Retrieve website identifier
     *
     * @return string|int|null
     */
    public function getWebsiteId()
    {
        return $this->_getData('website_id');
    }

    /**
     * Retrieve group identifier
     *
     * @return string|int|null
     */
    public function getGroupId()
    {
        return $this->_getData('group_id');
    }

    /**
     * Retrieve default group identifier
     *
     * @return string|int|null
     */
    public function getDefaultGroupId()
    {
        return $this->_getData('default_group_id');
    }

    /**
     * Check if store can be deleted
     *
     * @return boolean
     */
    public function isCanDelete()
    {
        if (!$this->getId()) {
            return false;
        }

        return $this->getGroup()->getDefaultStoreId() != $this->getId();
    }

    /**
     * Retrieve current url for store
     *
     * @param bool|string $fromStore
     * @return string
     */
    public function getCurrentUrl($fromStore = true)
    {
        $sidQueryParam = $this->_getSession()->getSessionIdQueryParam();
        $requestString = $this->getUrlModel()->escape(
            ltrim(Mage::app()->getRequest()->getRequestString(), '/'));

        $storeUrl = Mage::app()->getStore()->isCurrentlySecure()
            ? $this->getUrl('', array('_secure' => true))
            : $this->getUrl('');

        if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
            return $storeUrl;
        }

        $storeParsedUrl = parse_url($storeUrl);

        $storeParsedQuery = array();
        if (isset($storeParsedUrl['query'])) {
            parse_str($storeParsedUrl['query'], $storeParsedQuery);
        }

        $currQuery = Mage::app()->getRequest()->getQuery();
        if (isset($currQuery[$sidQueryParam]) && !empty($currQuery[$sidQueryParam])
            && $this->_getSession()->getSessionIdForHost($storeUrl) != $currQuery[$sidQueryParam]
        ) {
            unset($currQuery[$sidQueryParam]);
        }

        foreach ($currQuery as $k => $v) {
            $storeParsedQuery[$k] = $v;
        }

        if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $this->getCode())) {
            $storeParsedQuery['___store'] = $this->getCode();
        }
        if ($fromStore !== false) {
            $storeParsedQuery['___from_store'] = $fromStore === true ? Mage::app()->getStore()->getCode() : $fromStore;
        }

        return $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path'] . $requestString
            . ($storeParsedQuery ? '?'.http_build_query($storeParsedQuery, '', '&amp;') : '');
    }

    /**
     * Check if store is active
     *
     * @return boolean|null
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * Retrieve store name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Protect delete from non admin area
     *
     * Register indexing event before delete store
     *
     * @return Mage_Core_Model_Store
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        Mage::getSingleton('Mage_Index_Model_Indexer')->logEvent($this, self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE);
        return parent::_beforeDelete();
    }

    /**
     * rewrite in order to clear configuration cache
     *
     * @return Mage_Core_Model_Store
     */
    protected function _afterDelete()
    {
        parent::_afterDelete();
        Mage::getConfig()->removeCache();
        return $this;
    }

    /**
     * Init indexing process after store delete commit
     *
     * @return Mage_Core_Model_Store
     */
    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();
        Mage::getSingleton('Mage_Index_Model_Indexer')->indexEvents(self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE);
        return $this;
    }

    /**
     * Reinit and reset Config Data
     *
     * @return Mage_Core_Model_Store
     */
    public function resetConfig()
    {
        Mage::getConfig()->reinit();
        $this->_dirCache        = array();
        $this->_configCache     = array();
        $this->_baseUrlCache    = array();
        $this->_urlCache        = array();

        return $this;
    }

    /**
     * Get/Set isReadOnly flag
     *
     * @param bool $value
     * @return bool
     */
    public function isReadOnly($value = null)
    {
        if (null !== $value) {
            $this->_isReadOnly = (bool) $value;
        }
        return $this->_isReadOnly;
    }

    /**
     * Retrieve storegroup name
     *
     * @return string
     */
    public function getFrontendName()
    {
        if (is_null($this->_frontendName)) {
            $storeGroupName = (string) Mage::getStoreConfig('general/store_information/name', $this);
            $this->_frontendName = (!empty($storeGroupName)) ? $storeGroupName : $this->getGroup()->getName();
        }
        return $this->_frontendName;
    }

    /**
     * Set url class name for current store
     *
     * @param string $urlClassName
     * @return Mage_Core_Model_Store
     */
    public function setUrlClassName($urlClassName)
    {
        $this->_urlClassName = $urlClassName;
        return $this;
    }

    /**
     * Get url model by class name for current store
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlModel()
    {
        if (null === $this->_urlModel) {
            if (null === $this->_urlClassName) {
                $this->_urlClassName = self::DEFAULT_URL_MODEL_NAME;
            }
            $this->_urlModel = Mage::getModel($this->_urlClassName);
        }

        return $this->_urlModel;
    }
}
