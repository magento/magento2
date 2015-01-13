<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\AbstractModel;

/**
 * Store model
 *
 * @method \Magento\Store\Model\Store setCode(string $value)
 * @method \Magento\Store\Model\Store setWebsiteId(int $value)
 * @method \Magento\Store\Model\Store setGroupId(int $value)
 * @method \Magento\Store\Model\Store setName(string $value)
 * @method int getSortOrder()
 * @method int getStoreId()
 * @method \Magento\Store\Model\Store setSortOrder(int $value)
 * @method \Magento\Store\Model\Store setIsActive(int $value)
 */
class Store extends AbstractModel implements
    \Magento\Framework\App\ScopeInterface,
    \Magento\Framework\Url\ScopeInterface,
    \Magento\Framework\Object\IdentityInterface
{
    /**
     * Entity name
     */
    const ENTITY = 'store';

    /**
     * Custom entry point param
     */
    const CUSTOM_ENTRY_POINT_PARAM = 'custom_entry_point';

    /**#@+
     * Configuration paths
     */
    const XML_PATH_STORE_STORE_NAME = 'general/store_information/name';

    const XML_PATH_STORE_STORE_PHONE = 'general/store_information/phone';

    const XML_PATH_STORE_IN_URL = 'web/url/use_store';

    const XML_PATH_USE_REWRITES = 'web/seo/use_rewrites';

    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    const XML_PATH_SECURE_BASE_URL = 'web/secure/base_url';

    const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';

    const XML_PATH_SECURE_IN_ADMINHTML = 'web/secure/use_in_adminhtml';

    const XML_PATH_SECURE_BASE_LINK_URL = 'web/secure/base_link_url';

    const XML_PATH_UNSECURE_BASE_LINK_URL = 'web/unsecure/base_link_url';

    const XML_PATH_SECURE_BASE_STATIC_URL = 'web/secure/base_static_url';

    const XML_PATH_UNSECURE_BASE_STATIC_URL = 'web/unsecure/base_static_url';

    const XML_PATH_SECURE_BASE_MEDIA_URL = 'web/secure/base_media_url';

    const XML_PATH_UNSECURE_BASE_MEDIA_URL = 'web/unsecure/base_media_url';

    const XML_PATH_PRICE_SCOPE = 'catalog/price/scope';

    /**#@-*/

    /**#@+
     * Price scope constants
     */
    const PRICE_SCOPE_GLOBAL = 0;

    const PRICE_SCOPE_WEBSITE = 1;

    /**#@-*/

    /**#@+
     * Code constants
     */
    const DEFAULT_CODE = 'default';

    const ADMIN_CODE = 'admin';

    /**#@-*/

    /**
     * Cache tag
     */
    const CACHE_TAG = 'store';

    /**
     * Cookie name
     */
    const COOKIE_NAME = 'store';

    /**
     * Script name, which returns all the images
     */
    const MEDIA_REWRITE_SCRIPT = 'get.php/';

    /**
     * A placeholder for generating base URL
     */
    const BASE_URL_PLACEHOLDER = '{{base_url}}';

    /**
     * Identifier of default store
     * used for loading data of default scope
     */
    const DEFAULT_STORE_ID = 0;

    /**
     * Default store Id (for install)
     */
    const DISTRO_STORE_ID = 1;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * Cache flag
     *
     * @var boolean
     */
    protected $_cacheTag = true;

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
     * @var \Magento\Directory\Model\Currency\Filter
     */
    protected $_priceFilter;

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
    protected $_configCacheBaseNodes = [];

    /**
     * Directory cache
     *
     * @var array
     */
    protected $_dirCache = [];

    /**
     * URL cache
     *
     * @var array
     */
    protected $_urlCache = [];

    /**
     * Base URL cache
     *
     * @var array
     */
    protected $_baseUrlCache = [];

    /**
     * Session entity
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
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
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var bool
     */
    protected $_isCustomEntryPoint = false;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Core\Model\Resource\Config\Data
     */
    protected $_configDataResource;

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDatabase = null;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Store Config
     *
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var string
     */
    protected $_currencyInstalled;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\Resource\Store $resource
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Core\Model\Resource\Config\Data $configDataResource
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param string $currencyInstalled
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param bool $isCustomEntryPoint
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\Resource\Store $resource,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Http\RequestInterface $request,
        \Magento\Core\Model\Resource\Config\Data $configDataResource,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        $currencyInstalled,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        $isCustomEntryPoint = false,
        array $data = []
    ) {
        $this->_coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->_config = $config;
        $this->_url = $url;
        $this->_configCacheType = $configCacheType;
        $this->_request = $request;
        $this->_configDataResource = $configDataResource;
        $this->_isCustomEntryPoint = $isCustomEntryPoint;
        $this->filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->_sidResolver = $sidResolver;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManager = $cookieManager;
        $this->_httpContext = $httpContext;
        $this->_session = $session;
        $this->currencyFactory = $currencyFactory;
        $this->_currencyInstalled = $currencyInstalled;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        $properties = array_diff($properties, ['_coreFileStorageDatabase', '_config']);
        return $properties;
    }

    /**
     * Init not serializable fields
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $this->_coreFileStorageDatabase = \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Core\Helper\File\Storage\Database'
        );
        $this->_config = \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Framework\App\Config\ReinitableConfigInterface'
        );
    }

    /**
     * Initialize object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Store\Model\Resource\Store');
    }

    /**
     * Retrieve store session object
     *
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    protected function _getSession()
    {
        if (!$this->_session->isSessionExists()) {
            $this->_session->setName('store_' . $this->getCode());
            $this->_session->start();
        }
        return $this->_session;
    }

    /**
     * Validation rules for store
     *
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        $validator = new \Magento\Framework\Validator\Object();

        $storeLabelRule = new \Zend_Validate_NotEmpty();
        $storeLabelRule->setMessage(__('Name is required'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $validator->addRule($storeLabelRule, 'name');

        $storeCodeRule = new \Zend_Validate_Regex('/^[a-z]+[a-z0-9_]*$/');
        $storeCodeRule->setMessage(
            __(
                'The store code may contain only letters (a-z), numbers (0-9) or underscore(_),'
                . ' the first character must be a letter'
            ),
            \Zend_Validate_Regex::NOT_MATCH
        );
        $validator->addRule($storeCodeRule, 'code');

        return $validator;
    }

    /**
     * Loading store data
     *
     * @param   mixed $key
     * @param   string $field
     * @return  $this
     */
    public function load($key, $field = null)
    {
        if (!is_numeric($key) && is_null($field)) {
            $this->_getResource()->load($this, $key, 'code');
            return $this;
        }
        return parent::load($key, $field);
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
    protected function _getConfig($path)
    {
        $data = $this->_config->getValue($path, ScopeInterface::SCOPE_STORE, $this->getCode());
        if (!$data) {
            $data = $this->_config->getValue($path, \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT);
        }
        return $data === false ? null : $data;
    }

    /**
     * Set relation to the website
     *
     * @param Website $website
     * @return void
     */
    public function setWebsite(Website $website)
    {
        $this->setWebsiteId($website->getId());
    }

    /**
     * Retrieve store website
     *
     * @return Website|bool
     */
    public function getWebsite()
    {
        if (is_null($this->getWebsiteId())) {
            return false;
        }
        return $this->_storeManager->getWebsite($this->getWebsiteId());
    }

    /**
     * Retrieve url using store configuration specific
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        /** @var $url \Magento\Framework\UrlInterface */
        $url = $this->_url->setScope($this);
        if ($this->_storeManager->getStore()->getId() != $this->getId()) {
            $params['_scope_to_url'] = true;
        }

        return $url->getUrl($route, $params);
    }

    /**
     * Retrieve base URL
     *
     * @param string $type
     * @param boolean|null $secure
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getBaseUrl($type = \Magento\Framework\UrlInterface::URL_TYPE_LINK, $secure = null)
    {
        $cacheKey = $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
            $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
            switch ($type) {
                case \Magento\Framework\UrlInterface::URL_TYPE_WEB:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_URL : self::XML_PATH_UNSECURE_BASE_URL;
                    $url = $this->_getConfig($path);
                    break;

                case \Magento\Framework\UrlInterface::URL_TYPE_LINK:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_LINK_URL : self::XML_PATH_UNSECURE_BASE_LINK_URL;
                    $url = $this->_getConfig($path);
                    $url = $this->_updatePathUseRewrites($url);
                    $url = $this->_updatePathUseStoreView($url);
                    break;

                case \Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_LINK_URL : self::XML_PATH_UNSECURE_BASE_LINK_URL;
                    $url = $this->_getConfig($path);
                    $url = $this->_updatePathUseRewrites($url);
                    break;

                case \Magento\Framework\UrlInterface::URL_TYPE_STATIC:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_STATIC_URL : self::XML_PATH_UNSECURE_BASE_STATIC_URL;
                    $url = $this->_getConfig($path);
                    if (!$url) {
                        $url = $this->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                            $secure
                        ) . $this->filesystem->getUri(
                            DirectoryList::STATIC_VIEW
                        );
                    }
                    break;

                case \Magento\Framework\UrlInterface::URL_TYPE_MEDIA:
                    $url = $this->_getMediaScriptUrl($this->filesystem, $secure);
                    if (!$url) {
                        $path = $secure ? self::XML_PATH_SECURE_BASE_MEDIA_URL : self::XML_PATH_UNSECURE_BASE_MEDIA_URL;
                        $url = $this->_getConfig($path);
                        if (!$url) {
                            $url = $this->getBaseUrl(
                                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                                $secure
                            ) . $this->filesystem->getUri(
                                DirectoryList::MEDIA
                            );
                        }
                    }
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid base url type');
            }

            if (false !== strpos($url, self::BASE_URL_PLACEHOLDER)) {
                $distroBaseUrl = $this->_request->getDistroBaseUrl();
                $url = str_replace(self::BASE_URL_PLACEHOLDER, $distroBaseUrl, $url);
            }

            $this->_baseUrlCache[$cacheKey] = rtrim($url, '/') . '/';
        }

        return $this->_baseUrlCache[$cacheKey];
    }

    /**
     * Retrieve base media directory path
     *
     * @return string
     */
    public function getBaseMediaDir()
    {
        return $this->filesystem->getUri(DirectoryList::MEDIA);
    }

    /**
     * Retrieve base static directory path
     *
     * @return string
     */
    public function getBaseStaticDir()
    {
        return $this->filesystem->getUri(DirectoryList::STATIC_VIEW);
    }

    /**
     * Append script file name to url in case when server rewrites are disabled
     *
     * @param   string $url
     * @return  string
     */
    protected function _updatePathUseRewrites($url)
    {
        if ($this->getForceDisableRewrites() || !$this->_getConfig(self::XML_PATH_USE_REWRITES)) {
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
        return $this->_isCustomEntryPoint;
    }

    /**
     * Retrieve URL for media catalog
     *
     * If we use Database file storage and server doesn't support rewrites (.htaccess in media folder)
     * we have to put name of fetching media script exactly into URL
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param bool $secure
     * @return string|bool
     */
    protected function _getMediaScriptUrl(\Magento\Framework\Filesystem $filesystem, $secure)
    {
        if (!$this->_getConfig(self::XML_PATH_USE_REWRITES) && $this->_coreFileStorageDatabase->checkDbUsage()) {
            return $this->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                $secure
            ) . $filesystem->getUri(
                DirectoryList::PUB
            ) . '/' . self::MEDIA_REWRITE_SCRIPT;
        }
        return false;
    }

    /**
     * Add store code to url in case if it is enabled in configuration
     *
     * @param   string $url
     * @return  string
     */
    protected function _updatePathUseStoreView($url)
    {
        if ($this->isUseStoreInUrl()) {
            $url .= $this->getCode() . '/';
        }
        return $url;
    }

    /**
     * Returns whether url forming scheme prepends url path with store view code
     *
     * @return boolean
     */
    public function isUseStoreInUrl()
    {
        return !($this->hasDisableStoreInUrl() &&
            $this->getDisableStoreInUrl()) &&
            $this->_getConfig(self::XML_PATH_STORE_IN_URL);
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
     * Check if frontend URLs should be secure
     *
     * @return boolean
     */
    public function isFrontUrlSecure()
    {
        if ($this->_isFrontSecure === null) {
            $this->_isFrontSecure = $this->_config->isSetFlag(
                self::XML_PATH_SECURE_IN_FRONTEND,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getId()
            );
        }
        return $this->_isFrontSecure;
    }

    /**
     * @return bool
     */
    public function isUrlSecure()
    {
        return $this->isFrontUrlSecure();
    }

    /**
     * Check if request was secure
     *
     * @return boolean
     */
    public function isCurrentlySecure()
    {
        if ($this->_request->isSecure()) {
            return true;
        }

        $secureBaseUrl = $this->_config->getValue(
            self::XML_PATH_SECURE_BASE_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$secureBaseUrl) {
            return false;
        }

        $uri = \Zend_Uri::factory($secureBaseUrl);
        $port = $uri->getPort();
        $isSecure = $uri->getScheme() == 'https' && isset(
            $_SERVER['SERVER_PORT']
        ) && $port == $_SERVER['SERVER_PORT'];
        return $isSecure;
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
        $configValue = $this->_getConfig(self::XML_PATH_PRICE_SCOPE);
        if ($configValue == self::PRICE_SCOPE_GLOBAL) {
            return $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
            );
        } else {
            return $this->_getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE);
        }
    }

    /**
     * Retrieve store base currency
     *
     * @return \Magento\Directory\Model\Currency
     */
    public function getBaseCurrency()
    {
        $currency = $this->getData('base_currency');
        if (null === $currency) {
            $currency = $this->currencyFactory->create()->load($this->getBaseCurrencyCode());
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
        $result = $this->_getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT);
        return $result;
    }

    /**
     * Retrieve store default currency
     *
     * @return \Magento\Directory\Model\Currency
     */
    public function getDefaultCurrency()
    {
        $currency = $this->getData('default_currency');
        if (null === $currency) {
            $currency = $this->currencyFactory->create()->load($this->getDefaultCurrencyCode());
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

            $this->_httpContext->setValue(
                \Magento\Core\Helper\Data::CONTEXT_CURRENCY,
                $code,
                $this->_storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode()
            );
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
        $code = $this->_httpContext->getValue(\Magento\Core\Helper\Data::CONTEXT_CURRENCY);
        $code = is_null($code) ? $this->_getSession()->getCurrencyCode() : $code;
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
        if (null === $codes) {
            $codes = explode(',', $this->_getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW));
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
     * Array of installed currencies for the scope
     *
     * @return array
     */
    public function getAllowedCurrencies()
    {
        return explode(',', $this->_getConfig($this->_currencyInstalled));
    }

    /**
     * Retrieve store current currency
     *
     * @return \Magento\Directory\Model\Currency
     */
    public function getCurrentCurrency()
    {
        $currency = $this->getData('current_currency');

        if (is_null($currency)) {
            $currency = $this->currencyFactory->create()->load($this->getCurrentCurrencyCode());
            $baseCurrency = $this->getBaseCurrency();

            if (!$baseCurrency->getRate($currency)) {
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
     * @param \Magento\Store\Model\Group $group
     * @return void
     */
    public function setGroup(\Magento\Store\Model\Group $group)
    {
        $this->setGroupId($group->getId());
    }

    /**
     * Retrieve group model
     *
     * @return \Magento\Store\Model\Group|bool
     */
    public function getGroup()
    {
        if (null === $this->getGroupId()) {
            return false;
        }
        return $this->_storeManager->getGroup($this->getGroupId());
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
        $sidQueryParam = $this->_sidResolver->getSessionIdQueryParam($this->_getSession());
        $requestString = $this->_url->escape(ltrim($this->_request->getRequestString(), '/'));

        $storeUrl = $this->_storeManager->getStore()->isCurrentlySecure() ? $this->getUrl(
            '',
            ['_secure' => true]
        ) : $this->getUrl(
            ''
        );

        if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
            return $storeUrl;
        }

        $storeParsedUrl = parse_url($storeUrl);

        $storeParsedQuery = [];
        if (isset($storeParsedUrl['query'])) {
            parse_str($storeParsedUrl['query'], $storeParsedQuery);
        }

        $currQuery = $this->_request->getQuery();
        if (isset(
            $currQuery[$sidQueryParam]
        ) && !empty($currQuery[$sidQueryParam]) && $this->_getSession()->getSessionIdForHost(
            $storeUrl
        ) != $currQuery[$sidQueryParam]
        ) {
            unset($currQuery[$sidQueryParam]);
        }

        foreach ($currQuery as $key => $value) {
            $storeParsedQuery[$key] = $value;
        }

        if (!$this->isUseStoreInUrl()) {
            $storeParsedQuery['___store'] = $this->getCode();
        }
        if ($fromStore !== false) {
            $storeParsedQuery['___from_store'] = $fromStore ===
                true ? $this->_storeManager->getStore()->getCode() : $fromStore;
        }

        return $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host'] . (isset(
            $storeParsedUrl['port']
        ) ? ':' .
            $storeParsedUrl['port'] : '') .
            $storeParsedUrl['path'] .
            $requestString .
            ($storeParsedQuery ? '?' .
            http_build_query($storeParsedQuery, '', '&amp;') : ''
            );
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
     * @return $this
     */
    public function beforeDelete()
    {
        $this->_configDataResource->clearScopeData(\Magento\Store\Model\ScopeInterface::SCOPE_STORES, $this->getId());

        return parent::beforeDelete();
    }

    /**
     * Rewrite in order to clear configuration cache
     *
     * @return $this
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->_configCacheType->clean();
        return $this;
    }

    /**
     * Reinit and reset Config Data
     *
     * @return $this
     */
    public function resetConfig()
    {
        $this->_config->reinit();
        $this->_dirCache = [];
        $this->_baseUrlCache = [];
        $this->_urlCache = [];

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
            $this->_isReadOnly = (bool)$value;
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
        if (null === $this->_frontendName) {
            $storeGroupName = (string)$this->_config->getValue(
                'general/store_information/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this
            );
            $this->_frontendName = !empty($storeGroupName) ? $storeGroupName : $this->getGroup()->getName();
        }
        return $this->_frontendName;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Set store cookie with this store's code for a year.
     *
     * @return $this
     */
    public function setCookie()
    {
        $cookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(true)
            ->setDurationOneYear()
            ->setPath($this->getStorePath());
        $this->_cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $this->getCode(),
            $cookieMetadata
        );
        return $this;
    }

    /**
     * Get store code from store cookie.
     *
     * @return null|string
     */
    public function getStoreCodeFromCookie()
    {
        return $this->_cookieManager->getCookie(self::COOKIE_NAME);
    }

    /**
     * Delete store cookie.
     *
     * @return $this
     */
    public function deleteCookie()
    {
        $cookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath($this->getStorePath());
        $this->_cookieManager->deleteCookie(self::COOKIE_NAME, $cookieMetadata);
        return $this;
    }

    /**
     * @return string
     */
    public function getStorePath()
    {
        $parsedUrl = parse_url($this->getBaseUrl());
        return isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
    }
}
