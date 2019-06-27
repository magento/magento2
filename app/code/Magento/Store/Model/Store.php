<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Catalog\Model\Category;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeInterface as AppScopeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Url\ScopeInterface as UrlScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Zend\Uri\UriFactory;

/**
 * Store model
 *
 * @api
 * @method Store setGroupId($value)
 * @method int getSortOrder()
 * @method int getStoreId()
 * @method Store setSortOrder($value)
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Store extends AbstractExtensibleModel implements
    AppScopeInterface,
    UrlScopeInterface,
    IdentityInterface,
    StoreInterface
{
    /**
     * Store Id key name
     */
    const STORE_ID = 'store_id';

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
    const XML_PATH_STORE_IN_URL = 'web/url/use_store';

    const XML_PATH_USE_REWRITES = 'web/seo/use_rewrites';

    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    const XML_PATH_SECURE_BASE_URL = 'web/secure/base_url';

    const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';

    const XML_PATH_SECURE_IN_ADMINHTML = 'web/secure/use_in_adminhtml';

    const XML_PATH_ENABLE_HSTS = 'web/secure/enable_hsts';

    const XML_PATH_ENABLE_UPGRADE_INSECURE = 'web/secure/enable_upgrade_insecure';

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

    const ADMIN_CODE = 'admin';

    /**
     * Cache tag
     */
    const CACHE_TAG = 'store';

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
     * @deprecated unused protected property
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
     * @var \Magento\Config\Model\ResourceModel\Config\Data
     */
    protected $_configDataResource;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDatabase = null;

    /**
     * Filesystem instance
     *
     * @var Filesystem
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
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var \Magento\Store\Model\Information
     */
    protected $information;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Framework\Url\ModifierInterface
     */
    private $urlModifier;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface
     */
    private $pillPut;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $resource
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Config\Model\ResourceModel\Config\Data $configDataResource
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param Information $information
     * @param string $currencyInstalled
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param bool $isCustomEntryPoint
     * @param array $data optional generic object data
     * @param \Magento\Framework\Event\ManagerInterface|null $eventManager
     * @param \Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface|null $pillPut
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\ResourceModel\Store $resource,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Config\Model\ResourceModel\Config\Data $configDataResource,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\Information $information,
        $currencyInstalled,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $isCustomEntryPoint = false,
        array $data = [],
        \Magento\Framework\Event\ManagerInterface $eventManager = null,
        \Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface $pillPut = null
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
        $this->_httpContext = $httpContext;
        $this->_session = $session;
        $this->currencyFactory = $currencyFactory;
        $this->information = $information;
        $this->_currencyInstalled = $currencyInstalled;
        $this->groupRepository = $groupRepository;
        $this->websiteRepository = $websiteRepository;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Event\ManagerInterface::class);
        $this->pillPut = $pillPut ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @inheritdoc
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
        $this->_coreFileStorageDatabase = ObjectManager::getInstance()
            ->get(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $this->_config = ObjectManager::getInstance()->get(
            \Magento\Framework\App\Config\ReinitableConfigInterface::class
        );
    }

    /**
     * Initialize object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Store\Model\ResourceModel\Store::class);
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
     * @throws \Zend_Validate_Exception
     */
    protected function _getValidationRulesBeforeSave()
    {
        $validator = new \Magento\Framework\Validator\DataObject();

        $storeLabelRule = new \Zend_Validate_NotEmpty();
        $storeLabelRule->setMessage(__('Name is required'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $validator->addRule($storeLabelRule, 'name');

        $storeCodeRule = new \Zend_Validate_Regex('/^[a-z]+[a-z0-9_]*$/i');
        $storeCodeRule->setMessage(
            __(
                'The store code may contain only letters (a-z), numbers (0-9) or underscore (_),'
                . ' and the first character must be a letter.'
            ),
            \Zend_Validate_Regex::NOT_MATCH
        );
        $validator->addRule($storeCodeRule, 'code');

        return $validator;
    }

    /**
     * Loading store data
     *
     * @param mixed $key
     * @param string $field
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load($key, $field = null)
    {
        if (!is_numeric($key) && $field === null) {
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
     * @inheritdoc
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Retrieve store configuration data
     *
     * @param   string $path
     * @return  string|null
     */
    public function getConfig($path)
    {
        $data = $this->_config->getValue($path, ScopeInterface::SCOPE_STORE, $this->getCode());
        if ($data === null) {
            $data = $this->_config->getValue($path);
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsite()
    {
        if ($this->getWebsiteId() === null) {
            return false;
        }
        return $this->websiteRepository->getById($this->getWebsiteId());
    }

    /**
     * Retrieve url using store configuration specific
     *
     * @param string $route
     * @param array $params
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl($route = '', $params = [])
    {
        /** @var $url UrlInterface */
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getBaseUrl($type = UrlInterface::URL_TYPE_LINK, $secure = null)
    {
        $cacheKey = $type . '/' . ($secure === null ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
            $secure = $secure === null ? $this->isCurrentlySecure() : (bool)$secure;
            switch ($type) {
                case UrlInterface::URL_TYPE_WEB:
                    $path = $secure
                        ? self::XML_PATH_SECURE_BASE_URL
                        : self::XML_PATH_UNSECURE_BASE_URL;
                    $url = $this->getConfig($path);
                    break;

                case UrlInterface::URL_TYPE_LINK:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_LINK_URL : self::XML_PATH_UNSECURE_BASE_LINK_URL;
                    $url = $this->getConfig($path);
                    $url = $this->_updatePathUseRewrites($url);
                    $url = $this->_updatePathUseStoreView($url);
                    break;

                case UrlInterface::URL_TYPE_DIRECT_LINK:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_LINK_URL : self::XML_PATH_UNSECURE_BASE_LINK_URL;
                    $url = $this->getConfig($path);
                    $url = $this->_updatePathUseRewrites($url);
                    break;

                case UrlInterface::URL_TYPE_STATIC:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_STATIC_URL : self::XML_PATH_UNSECURE_BASE_STATIC_URL;
                    $url = $this->getConfig($path);
                    if (!$url) {
                        $url = $this->getBaseUrl(UrlInterface::URL_TYPE_WEB, $secure)
                            . $this->filesystem->getUri(DirectoryList::STATIC_VIEW);
                    }
                    break;

                case UrlInterface::URL_TYPE_MEDIA:
                    $url = $this->_getMediaScriptUrl($this->filesystem, $secure);
                    if (!$url) {
                        $path = $secure ? self::XML_PATH_SECURE_BASE_MEDIA_URL : self::XML_PATH_UNSECURE_BASE_MEDIA_URL;
                        $url = $this->getConfig($path);
                        if (!$url) {
                            $url = $this->getBaseUrl(UrlInterface::URL_TYPE_WEB, $secure)
                                . $this->filesystem->getUri(DirectoryList::MEDIA);
                        }
                    }
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid base url type');
            }

            if (false !== strpos($url, self::BASE_URL_PLACEHOLDER)) {
                $url = str_replace(self::BASE_URL_PLACEHOLDER, $this->_request->getDistroBaseUrl(), $url);
            }

            $this->_baseUrlCache[$cacheKey] = $this->getUrlModifier()->execute(
                rtrim($url, '/') . '/',
                \Magento\Framework\Url\ModifierInterface::MODE_BASE
            );
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
        if ($this->getForceDisableRewrites() || !$this->getConfig(self::XML_PATH_USE_REWRITES)) {
            if ($this->_isCustomEntryPoint()) {
                $indexFileName = 'index.php';
            } else {
                $scriptFilename = $this->_request->getServer('SCRIPT_FILENAME');
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $indexFileName = basename($scriptFilename);
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
     * @param Filesystem $filesystem
     * @param bool $secure
     * @return string|bool
     */
    protected function _getMediaScriptUrl(Filesystem $filesystem, $secure)
    {
        if (!$this->getConfig(self::XML_PATH_USE_REWRITES) && $this->_coreFileStorageDatabase->checkDbUsage()) {
            $baseUrl = $this->getBaseUrl(UrlInterface::URL_TYPE_WEB, $secure);
            return $baseUrl . $filesystem->getUri(DirectoryList::PUB) . '/' . self::MEDIA_REWRITE_SCRIPT;
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
        return !($this->hasDisableStoreInUrl() && $this->getDisableStoreInUrl())
            && $this->getConfig(self::XML_PATH_STORE_IN_URL);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * Check if frontend URLs should be secure
     *
     * @return boolean
     */
    public function isFrontUrlSecure()
    {
        if ($this->_isFrontSecure === null) {
            $this->_isFrontSecure = $this->_config
                ->isSetFlag(self::XML_PATH_SECURE_IN_FRONTEND, ScopeInterface::SCOPE_STORE, $this->getId());
        }
        return $this->_isFrontSecure;
    }

    /**
     * @inheritdoc
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

        $secureBaseUrl = $this->_config->getValue(self::XML_PATH_SECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
        $secureFrontend = $this->_config->getValue(self::XML_PATH_SECURE_IN_FRONTEND, ScopeInterface::SCOPE_STORE);

        if (!$secureBaseUrl || !$secureFrontend) {
            return false;
        }

        $uri = UriFactory::factory($secureBaseUrl);
        $port = $uri->getPort();
        $serverPort = $this->_request->getServer('SERVER_PORT');
        $isSecure = $uri->getScheme() == 'https' && isset($serverPort) && $port == $serverPort;
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
        $configValue = $this->getConfig(self::XML_PATH_PRICE_SCOPE);
        if ($configValue == self::PRICE_SCOPE_GLOBAL) {
            return $this->_config->getValue(Currency::XML_PATH_CURRENCY_BASE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        }
        return $this->getConfig(Currency::XML_PATH_CURRENCY_BASE);
    }

    /**
     * Retrieve store base currency
     *
     * @return Currency
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
        $result = $this->getConfig(Currency::XML_PATH_CURRENCY_DEFAULT);
        return $result;
    }

    /**
     * Retrieve store default currency
     *
     * @return Currency
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
     * @param string $code
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setCurrentCurrencyCode($code)
    {
        $code = strtoupper($code);
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            $this->_getSession()->setCurrencyCode($code);

            $defaultCode = ($this->_storeManager->getStore() !== null)
                ? $this->_storeManager->getStore()->getDefaultCurrency()->getCode()
                : $this->_storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode();
            
            $this->_httpContext->setValue(Context::CONTEXT_CURRENCY, $code, $defaultCode);
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
        $availableCurrencyCodes = \array_values($this->getAvailableCurrencyCodes(true));
        // try to get currently set code among allowed
        $code = $this->_httpContext->getValue(Context::CONTEXT_CURRENCY) ?? $this->_getSession()->getCurrencyCode();
        if (empty($code) || !\in_array($code, $availableCurrencyCodes)) {
            $code = $this->getDefaultCurrencyCode();
            if (!\in_array($code, $availableCurrencyCodes) && !empty($availableCurrencyCodes)) {
                $code = $availableCurrencyCodes[0];
            }
        }

        return $code;
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
            $codes = explode(',', $this->getConfig(Currency::XML_PATH_CURRENCY_ALLOW));
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
        return explode(',', $this->getConfig($this->_currencyInstalled));
    }

    /**
     * Retrieve store current currency
     *
     * @return Currency
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentCurrency()
    {
        $currency = $this->getData('current_currency');

        if ($currency === null) {
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentCurrencyRate()
    {
        return $this->getBaseCurrency()->getRate($this->getCurrentCurrency());
    }

    /**
     * Retrieve root category identifier
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRootCategoryId()
    {
        if (!$this->getGroup()) {
            return Category::ROOT_CATEGORY_ID;
        }
        return $this->getGroup()->getRootCategoryId();
    }

    /**
     * Set group model for store
     *
     * @param Group $group
     * @return Store
     */
    public function setGroup(Group $group)
    {
        $this->setGroupId($group->getId());
        return $this;
    }

    /**
     * Retrieve group model
     *
     * @return Group|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGroup()
    {
        if (null === $this->getGroupId()) {
            return false;
        }
        return $this->groupRepository->get($this->getGroupId());
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
     * Reinit Stores on after save
     *
     * @return $this
     * @throws \Exception
     * @since 100.1.3
     * @deprecated 100.1.3
     */
    public function afterSave()
    {
        $this->_storeManager->reinitStores();
        if ($this->isObjectNew()) {
            $event = $this->_eventPrefix . '_add';
        } else {
            $event = $this->_eventPrefix . '_edit';
        }
        $store  = $this;
        $this->getResource()->addCommitCallback(
            function () use ($event, $store) {
                $this->eventManager->dispatch($event, ['store' => $store]);
            }
        );
        $this->pillPut->put();
        return parent::afterSave();
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData('website_id', $websiteId);
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
     * Retrieve store group identifier
     *
     * @return string|int|null
     */
    public function getStoreGroupId()
    {
        return $this->getGroupId();
    }

    /**
     * @inheritdoc
     */
    public function setStoreGroupId($storeGroupId)
    {
        return $this->setGroupId($storeGroupId);
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData('is_active', $isActive);
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCanDelete()
    {
        if (!$this->getId()) {
            return false;
        }

        return $this->getGroup()->getStoresCount() > 1;
    }

    /**
     * Check if store is default
     *
     * @return boolean
     * @since 100.1.0
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isDefault()
    {
        if (!$this->getId() && $this->getWebsite() && $this->getWebsite()->getStoresCount() == 0) {
            return true;
        }
        return $this->getGroup()->getDefaultStoreId() == $this->getId();
    }

    /**
     * Retrieve current url for store
     *
     * @param bool $fromStore
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrl($fromStore = true)
    {
        $sidQueryParam = $this->_sidResolver->getSessionIdQueryParam($this->_getSession());
        $requestString = $this->_url->escape(ltrim($this->_request->getRequestString(), '/'));

        $storeUrl = $this->getUrl('', ['_secure' => $this->_storeManager->getStore()->isCurrentlySecure()]);

        if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
            return $storeUrl;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $storeParsedUrl = parse_url($storeUrl);

        $storeParsedQuery = [];
        if (isset($storeParsedUrl['query'])) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            parse_str($storeParsedUrl['query'], $storeParsedQuery);
        }

        $currQuery = $this->_request->getQueryValue();
        if (isset($currQuery[$sidQueryParam])
            && !empty($currQuery[$sidQueryParam])
            && $this->_getSession()->getSessionIdForHost($storeUrl) != $currQuery[$sidQueryParam]
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

        $requestStringParts = explode('?', $requestString, 2);
        $requestStringPath = $requestStringParts[0];
        if (isset($requestStringParts[1])) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            parse_str($requestStringParts[1], $requestString);
        } else {
            $requestString = [];
        }

        $currentUrlQueryParams = array_merge($requestString, $storeParsedQuery);

        $currentUrl = $storeParsedUrl['scheme']
            . '://'
            . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path']
            . $requestStringPath
            . ($currentUrlQueryParams ? '?' . http_build_query($currentUrlQueryParams) : '');

        return $currentUrl;
    }

    /**
     * Check if store is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return (bool)$this->_getData('is_active');
    }

    /**
     * Protect delete from non admin area
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        $this->_configDataResource->clearScopeData(ScopeInterface::SCOPE_STORES, $this->getId());
        return parent::beforeDelete();
    }

    /**
     * Rewrite in order to clear configuration cache
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterDelete()
    {
        $store = $this;
        $this->getResource()->addCommitCallback(
            function () use ($store) {
                $this->_storeManager->reinitStores();
                $this->eventManager->dispatch($this->_eventPrefix . '_delete', ['store' => $store]);
            }
        );
        parent::afterDelete();
        $this->_configCacheType->clean();

        if ($this->getId() === $this->getGroup()->getDefaultStoreId()) {
            $ids = $this->getGroup()->getStoreIds();
            if (!empty($ids) && count($ids) > 1) {
                unset($ids[$this->getId()]);
                $defaultId = current($ids);
            } else {
                $defaultId = null;
            }
            $this->getGroup()->setDefaultStoreId($defaultId);
            $this->getGroup()->save();
        }

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
     * Retrieve store group name
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFrontendName()
    {
        if (null === $this->_frontendName) {
            $storeGroupName = (string)$this->_config
                ->getValue(Information::XML_PATH_STORE_INFO_NAME, ScopeInterface::SCOPE_STORE, $this);
            $this->_frontendName = !empty($storeGroupName) ? $storeGroupName : $this->getGroup()->getName();
        }
        return $this->_frontendName;
    }

    /**
     * Retrieve formatted store address from config
     *
     * @return string
     */
    public function getFormattedAddress()
    {
        return $this->information->getFormattedAddress($this);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * Return Store Path
     *
     * @return string
     */
    public function getStorePath()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $parsedUrl = parse_url($this->getBaseUrl());
        return $parsedUrl['path'] ?? '/';
    }

    /**
     * @inheritdoc
     */
    public function getScopeType()
    {
        return ScopeInterface::SCOPE_STORE;
    }

    /**
     * @inheritdoc
     */
    public function getScopeTypeName()
    {
        return 'Store View';
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\StoreExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Gets URL modifier.
     *
     * @return \Magento\Framework\Url\ModifierInterface
     * @deprecated 100.1.0
     */
    private function getUrlModifier()
    {
        if ($this->urlModifier === null) {
            $this->urlModifier = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Url\ModifierInterface::class
            );
        }

        return $this->urlModifier;
    }
}
