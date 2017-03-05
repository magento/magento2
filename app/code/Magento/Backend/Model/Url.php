<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

use Magento\Framework\Url\HostChecker;
use Magento\Framework\App\ObjectManager;

/**
 * Class \Magento\Backend\Model\UrlInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Url extends \Magento\Framework\Url implements \Magento\Backend\Model\UrlInterface
{
    /**
     * Whether to use a security key in the backend
     *
     * @bug Currently, this constant is slightly misleading: it says "form key", but in fact it is used by URLs, too
     */
    const XML_PATH_USE_SECURE_KEY = 'admin/security/use_form_key';

    /**
     * Authentication session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menu;

    /**
     * Startup page url from config
     *
     * @var string
     */
    protected $_startupMenuItemId;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendHelper;

    /**
     * Menu config
     *
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menuConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_scope;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo
     * @param \Magento\Framework\Url\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Url\RouteParamsResolverFactory $routeParamsResolverFactory
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Url\RouteParamsPreprocessorInterface $routeParamsPreprocessor
     * @param string $scopeType
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param Menu\Config $menuConfig
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param Auth\Session $authSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     * @param HostChecker|null $hostChecker
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo,
        \Magento\Framework\Url\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Url\RouteParamsResolverFactory $routeParamsResolverFactory,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url\RouteParamsPreprocessorInterface $routeParamsPreprocessor,
        $scopeType,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Backend\Model\Menu\Config $menuConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = [],
        HostChecker $hostChecker = null
    ) {
        $this->_encryptor = $encryptor;
        $hostChecker = $hostChecker ?: ObjectManager::getInstance()->get(HostChecker::class);
        parent::__construct(
            $routeConfig,
            $request,
            $urlSecurityInfo,
            $scopeResolver,
            $session,
            $sidResolver,
            $routeParamsResolverFactory,
            $queryParamsResolver,
            $scopeConfig,
            $routeParamsPreprocessor,
            $scopeType,
            $data,
            $hostChecker
        );
        $this->_backendHelper = $backendHelper;
        $this->_menuConfig = $menuConfig;
        $this->_cache = $cache;
        $this->_session = $authSession;
        $this->formKey = $formKey;
        $this->_storeFactory = $storeFactory;
    }

    /**
     * Retrieve is secure mode for ULR logic
     *
     * @return bool
     */
    protected function _isSecure()
    {
        if ($this->hasData('secure_is_forced')) {
            return $this->getData('secure');
        }
        return $this->_scopeConfig->isSetFlag('web/secure/use_in_adminhtml');
    }

    /**
     * Force strip secret key param if _nosecret param specified
     *
     * @param array $data
     * @param bool $unsetOldParams
     * @return $this
     */
    protected function _setRouteParams(array $data, $unsetOldParams = true)
    {
        if (isset($data['_nosecret'])) {
            $this->setNoSecret(true);
            unset($data['_nosecret']);
        } else {
            $this->setNoSecret(false);
        }
        unset($data['_scope_to_url']);
        return parent::_setRouteParams($data, $unsetOldParams);
    }

    /**
     * Custom logic to retrieve Urls
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        if (filter_var($routePath, FILTER_VALIDATE_URL)) {
            return $routePath;
        }

        $cacheSecretKey = false;
        if (is_array($routeParams) && isset($routeParams['_cache_secret_key'])) {
            unset($routeParams['_cache_secret_key']);
            $cacheSecretKey = true;
        }
        $result = parent::getUrl($routePath, $routeParams);
        if (!$this->useSecretKey()) {
            return $result;
        }
        $this->_setRoutePath($routePath);
        $routeName = $this->_getRouteName('*');
        $controllerName = $this->_getControllerName(self::DEFAULT_CONTROLLER_NAME);
        $actionName = $this->_getActionName(self::DEFAULT_ACTION_NAME);
        if ($cacheSecretKey) {
            $secret = [self::SECRET_KEY_PARAM_NAME => "\${$routeName}/{$controllerName}/{$actionName}\$"];
        } else {
            $secret = [
                self::SECRET_KEY_PARAM_NAME => $this->getSecretKey($routeName, $controllerName, $actionName),
            ];
        }
        if (is_array($routeParams)) {
            $routeParams = array_merge($secret, $routeParams);
        } else {
            $routeParams = $secret;
        }
        if (is_array($this->_getRouteParams())) {
            $routeParams = array_merge($this->_getRouteParams(), $routeParams);
        }
        return parent::getUrl("{$routeName}/{$controllerName}/{$actionName}", $routeParams);
    }

    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $routeName
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function getSecretKey($routeName = null, $controller = null, $action = null)
    {
        $salt = $this->formKey->getFormKey();
        $request = $this->_getRequest();
        if (!$routeName) {
            if ($request->getBeforeForwardInfo('route_name') !== null) {
                $routeName = $request->getBeforeForwardInfo('route_name');
            } else {
                $routeName = $request->getRouteName();
            }
        }
        if (!$controller) {
            if ($request->getBeforeForwardInfo('controller_name') !== null) {
                $controller = $request->getBeforeForwardInfo('controller_name');
            } else {
                $controller = $request->getControllerName();
            }
        }
        if (!$action) {
            if ($request->getBeforeForwardInfo('action_name') !== null) {
                $action = $request->getBeforeForwardInfo('action_name');
            } else {
                $action = $request->getActionName();
            }
        }
        $secret = $routeName . $controller . $action . $salt;
        return $this->_encryptor->getHash($secret);
    }

    /**
     * Return secret key settings flag
     *
     * @return bool
     */
    public function useSecretKey()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_USE_SECURE_KEY) && !$this->getNoSecret();
    }

    /**
     * Enable secret key using
     *
     * @return $this
     */
    public function turnOnSecretKey()
    {
        $this->setNoSecret(false);
        return $this;
    }

    /**
     * Disable secret key using
     *
     * @return $this
     */
    public function turnOffSecretKey()
    {
        $this->setNoSecret(true);
        return $this;
    }

    /**
     * Refresh admin menu cache etc.
     *
     * @return void
     */
    public function renewSecretUrls()
    {
        $this->_cache->clean([\Magento\Backend\Block\Menu::CACHE_TAGS]);
    }

    /**
     * Find admin start page url
     *
     * @return string
     */
    public function getStartupPageUrl()
    {
        $menuItem = $this->_getMenu()->get(
            $this->_scopeConfig->getValue(self::XML_PATH_STARTUP_MENU_ITEM, $this->_scopeType)
        );
        if ($menuItem !== null) {
            if ($menuItem->isAllowed() && $menuItem->getAction()) {
                return $menuItem->getAction();
            }
        }
        return $this->findFirstAvailableMenu();
    }

    /**
     * Find first menu item that user is able to access
     *
     * @return string
     */
    public function findFirstAvailableMenu()
    {
        /* @var $menu \Magento\Backend\Model\Menu\Item */
        $menu = $this->_getMenu();
        $item = $menu->getFirstAvailable();
        $action = $item ? $item->getAction() : null;
        if (!$item) {
            $user = $this->_getSession()->getUser();
            if ($user) {
                $user->setHasAvailableResources(false);
            }
            $action = '*/*/denied';
        }
        return $action;
    }

    /**
     * Get Menu model
     *
     * @return \Magento\Backend\Model\Menu
     */
    protected function _getMenu()
    {
        if ($this->_menu === null) {
            $this->_menu = $this->_menuConfig->getMenu();
        }
        return $this->_menu;
    }

    /**
     * Set custom auth session
     *
     * @param \Magento\Backend\Model\Auth\Session $session
     * @return $this
     */
    public function setSession(\Magento\Backend\Model\Auth\Session $session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Retrieve auth session
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Return backend area front name, defined in configuration
     *
     * @return string
     */
    public function getAreaFrontName()
    {
        if (!$this->_getData('area_front_name')) {
            $this->setData('area_front_name', $this->_backendHelper->getAreaFrontName());
        }
        return $this->_getData('area_front_name');
    }

    /**
     * Retrieve action path.
     * Add backend area front name as a prefix to action path
     *
     * @return string
     */
    protected function _getActionPath()
    {
        $path = parent::_getActionPath();
        if ($path) {
            if ($this->getAreaFrontName()) {
                $path = $this->getAreaFrontName() . '/' . $path;
            }
        }
        return $path;
    }

    /**
     * Get scope for the url instance
     *
     * @return \Magento\Store\Model\Store
     */
    protected function _getScope()
    {
        if (!$this->_scope) {
            $this->_scope = $this->_storeFactory->create(
                [
                    'url' => $this,
                    'data' => ['code' => 'admin', 'force_disable_rewrites' => false, 'disable_store_in_url' => true],
                ]
            );
        }
        return $this->_scope;
    }

    /**
     * Get cache id for config path
     *
     * @param string $path
     * @return string
     */
    protected function _getConfigCacheId($path)
    {
        return 'admin/' . $path;
    }

    /**
     * Get config data by path
     * Use only global config values for backend
     *
     * @param string $path
     * @return null|string
     */
    protected function _getConfig($path)
    {
        return $this->_scopeConfig->getValue($path);
    }
}
