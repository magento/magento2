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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * URL
 *
 * Properties:
 *
 * - request
 *
 * - relative_url: true, false
 * - type: 'link', 'skin', 'js', 'media'
 * - store: instanceof \Magento\Core\Model\Store
 * - secure: true, false
 *
 * - scheme: 'http', 'https'
 * - user: 'user'
 * - password: 'password'
 * - host: 'localhost'
 * - port: 80, 443
 * - base_path: '/dev/magento/'
 * - base_script: 'index.php'
 *
 * - storeview_path: 'storeview/'
 * - route_path: 'module/controller/action/param1/value1/param2/value2'
 * - route_name: 'module'
 * - controller_name: 'controller'
 * - action_name: 'action'
 * - route_params: array('param1'=>'value1', 'param2'=>'value2')
 *
 * - query: (?)'param1=value1&param2=value2'
 * - query_array: array('param1'=>'value1', 'param2'=>'value2')
 * - fragment: (#)'fragment-anchor'
 *
 * URL structure:
 *
 * https://user:password@host:443/base_path/[base_script][storeview_path]route_name/controller_name/action_name/param1/value1?query_param=query_value#fragment
 *       \__________A___________/\____________________________________B_____________________________________/
 * \__________________C___________________/              \__________________D_________________/ \_____E_____/
 * \_____________F______________/                        \___________________________G______________________/
 * \___________________________________________________H____________________________________________________/
 *
 * - A: authority
 * - B: path
 * - C: absolute_base_url
 * - D: action_path
 * - E: route_params
 * - F: host_url
 * - G: route_path
 * - H: route_url
 *
 * @category   Magento
 * @package    Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model;

use Magento\Core\Model\App;
use Magento\Core\Model\Session;
use Magento\Core\Model\Store;
use Magento\Core\Model\StoreManager;

class Url extends \Magento\Object implements \Magento\UrlInterface
{
    /**
     * Configuration data cache
     *
     * @var array
     */
    static protected $_configDataCache;

    /**
     * Encrypted session identifier
     *
     * @var string|null
     */
    static protected $_encryptedSessionId;

    /**
     * Reserved Route parameter keys
     *
     * @var array
     */
    protected $_reservedRouteParams = array(
        '_store', '_type', '_secure', '_forced_secure', '_use_rewrite', '_nosid',
        '_absolute', '_current', '_direct', '_fragment', '_escape', '_query',
        '_store_to_url'
    );

    /**
     * Request instance
     *
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * Use Session ID for generate URL
     *
     * @var bool
     */
    protected $_useSession;

    /**
     * Url security info list
     *
     * @var \Magento\Core\Model\Url\SecurityInfoInterface
     */
    protected $_urlSecurityInfo;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * Router list
     *
     * @var \Magento\App\RouterListInterface
     */
    protected $_routerList;

    /**
     * @param \Magento\App\RouterListInterface $routerList
     * @param \Magento\App\RequestInterface $request
     * @param Url\SecurityInfoInterface $urlSecurityInfo
     * @param Store\Config $coreStoreConfig
     * @param \Magento\Core\Helper\Data $coreData
     * @param App $app
     * @param StoreManager $storeManager
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\App\RouterListInterface $routerList,
        \Magento\App\RequestInterface $request,
        Url\SecurityInfoInterface $urlSecurityInfo,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\App $app,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Session $session,
        array $data = array()
    ) {
        $this->_request = $request;
        $this->_routerList = $routerList;
        $this->_urlSecurityInfo = $urlSecurityInfo;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_coreData = $coreData;
        $this->_app = $app;
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        parent::__construct($data);
    }

    /**
     * Initialize object
     */
    protected function _construct()
    {
        $this->setStore(null);
    }

    /**
     * Get default url type
     *
     * @return string
     */
    protected function _getDefaultUrlType()
    {
        return \Magento\Core\Model\Store::URL_TYPE_LINK;
    }

    /**
     * Initialize object data from retrieved url
     *
     * @param   string $url
     * @return  \Magento\Core\Model\Url
     */
    public function parseUrl($url)
    {
        $data = parse_url($url);
        $parts = array(
            'scheme'   => 'setScheme',
            'host'     => 'setHost',
            'port'     => 'setPort',
            'user'     => 'setUser',
            'pass'     => 'setPassword',
            'path'     => 'setPath',
            'query'    => 'setQuery',
            'fragment' => 'setFragment');

        foreach ($parts as $component => $method) {
            if (isset($data[$component])) {
                $this->$method($data[$component]);
            }
        }
        return $this;
    }

    /**
     * Retrieve default controller name
     *
     * @return string
     */
    public function getDefaultControllerName()
    {
        return self::DEFAULT_CONTROLLER_NAME;
    }

    /**
     * Set use_url_cache flag
     *
     * @param boolean $flag
     * @return \Magento\Core\Model\Url
     */
    public function setUseUrlCache($flag)
    {
        $this->setData('use_url_cache', $flag);
        return $this;
    }

    /**
     * Set use session rule
     *
     * @param bool $useSession
     * @return \Magento\Core\Model\Url
     */
    public function setUseSession($useSession)
    {
        $this->_useSession = (bool) $useSession;
        return $this;
    }

    /**
     * Set route front name
     *
     * @param string $name
     * @return \Magento\Core\Model\Url
     */
    public function setRouteFrontName($name)
    {
        $this->setData('route_front_name', $name);
        return $this;
    }

    /**
     * Retrieve use session rule
     *
     * @return bool
     */
    public function getUseSession()
    {
        if (is_null($this->_useSession)) {
            $this->_useSession = $this->_app->getUseSessionInUrl();
        }
        return $this->_useSession;
    }

    /**
     * Retrieve default action name
     *
     * @return string
     */
    public function getDefaultActionName()
    {
        return self::DEFAULT_ACTION_NAME;
    }

    /**
     * Retrieve configuration data
     *
     * @param string $key
     * @param string|null $prefix
     * @return string
     */
    public function getConfigData($key, $prefix = null)
    {
        if (is_null($prefix)) {
            $prefix = 'web/' . ($this->isSecure() ? 'secure' : 'unsecure').'/';
        }
        $path = $prefix . $key;

        $cacheId = $this->getStore()->getCode() . '/' . $path;
        if (!isset(self::$_configDataCache[$cacheId])) {
            $data = $this->getStore()->getConfig($path);
            self::$_configDataCache[$cacheId] = $data;
        }

        return self::$_configDataCache[$cacheId];
    }

    /**
     * Set request
     *
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\Core\Model\Url
     */
    public function setRequest(\Magento\App\RequestInterface $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Zend request object
     *
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve URL type
     *
     * @return string
     */
    public function getType()
    {
        if (!$this->hasData('type')) {
            $this->setData('type', $this->_getDefaultUrlType());
        }
        return $this->_getData('type');
    }

    /**
     * Retrieve is secure mode URL
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->hasData('secure_is_forced')) {
            return (bool)$this->getData('secure');
        }

        $store = $this->getStore();

        if ($store->isAdmin() && !$store->isAdminUrlSecure()) {
            return false;
        }
        if (!$store->isAdmin() && !$store->isFrontUrlSecure()) {
            return false;
        }

        if (!$this->hasData('secure')) {
            if ($this->getType() == \Magento\Core\Model\Store::URL_TYPE_LINK && !$store->isAdmin()) {
                $pathSecure = $this->_urlSecurityInfo->isSecure('/' . $this->getActionPath());
                $this->setData('secure', $pathSecure);
            } else {
                $this->setData('secure', true);
            }
        }
        return $this->getData('secure');
    }

    /**
     * Set store entity
     *
     * @param mixed $params
     * @return \Magento\Core\Model\Url
     */
    public function setStore($params)
    {
        $this->setData('store', $this->_storeManager->getStore($params));
        return $this;
    }

    /**
     * Get current store for the url instance
     *
     * @return \Magento\Core\Model\Store
     */
    public function getStore()
    {
        if (!$this->hasData('store')) {
            $this->setStore(null);
        }
        return $this->_getData('store');
    }

    /**
     * Retrieve Base URL
     *
     * @param array $params
     * @return string
     */
    public function getBaseUrl($params = array())
    {
        if (isset($params['_store'])) {
            $this->setStore($params['_store']);
        }
        if (isset($params['_type'])) {
            $this->setType($params['_type']);
        }

        if (isset($params['_secure'])) {
            $this->setSecure($params['_secure']);
        }

        /**
         * Add availability support urls without store code
         */
        if ($this->getType() == \Magento\Core\Model\Store::URL_TYPE_LINK
            && $this->getRequest()->isDirectAccessFrontendName($this->getRouteFrontName())) {
            $this->setType(\Magento\Core\Model\Store::URL_TYPE_DIRECT_LINK);
        }

        $result =  $this->getStore()->getBaseUrl($this->getType(), $this->isSecure());
        $this->setType($this->_getDefaultUrlType());
        return $result;
    }

    /**
     * Set Route Parameters
     *
     * @param string $data
     * @return \Magento\Core\Model\Url
     */
    public function setRoutePath($data)
    {
        if ($this->_getData('route_path') == $data) {
            return $this;
        }

        $this->unsetData('route_path');
        $routePieces = explode('/', $data);

        $route = array_shift($routePieces);
        if ('*' === $route) {
            $route = $this->getRequest()->getRequestedRouteName();
        }
        $this->setRouteName($route);

        $controller = '';
        if (!empty($routePieces)) {
            $controller = array_shift($routePieces);
            if ('*' === $controller) {
                $controller = $this->getRequest()->getRequestedControllerName();
            }
        }
        $this->setControllerName($controller);

        $action = '';
        if (!empty($routePieces)) {
            $action = array_shift($routePieces);
            if ('*' === $action) {
                $action = $this->getRequest()->getRequestedActionName();
            }
        }
        $this->setActionName($action);

        if (!empty($routePieces)) {
            while (!empty($routePieces)) {
                $key = array_shift($routePieces);
                if (!empty($routePieces)) {
                    $value = array_shift($routePieces);
                    $this->setRouteParam($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve action path
     *
     * @return string
     */
    public function getActionPath()
    {
        if (!$this->getRouteName()) {
            return '';
        }

        $hasParams = (bool) $this->getRouteParams();
        $path = $this->getRouteFrontName() . '/';

        if ($this->getControllerName()) {
            $path .= $this->getControllerName() . '/';
        } elseif ($hasParams) {
            $path .= $this->getDefaultControllerName() . '/';
        }
        if ($this->getActionName()) {
            $path .= $this->getActionName() . '/';
        } elseif ($hasParams) {
            $path .= $this->getDefaultActionName() . '/';
        }

        return $path;
    }

    /**
     * Retrieve route path
     *
     * @param array $routeParams
     * @return string
     */
    public function getRoutePath($routeParams = array())
    {
        if (!$this->hasData('route_path')) {
            $routePath = $this->getRequest()->getAlias(Url\Rewrite::REWRITE_REQUEST_PATH_ALIAS);
            if (!empty($routeParams['_use_rewrite']) && ($routePath !== null)) {
                $this->setData('route_path', $routePath);
                return $routePath;
            }
            $routePath = $this->getActionPath();
            if ($this->getRouteParams()) {
                foreach ($this->getRouteParams() as $key=>$value) {
                    if (is_null($value) || false === $value || '' === $value || !is_scalar($value)) {
                        continue;
                    }
                    $routePath .= $key . '/' . $value . '/';
                }
            }
            if ($routePath != '' && substr($routePath, -1, 1) !== '/') {
                $routePath .= '/';
            }
            $this->setData('route_path', $routePath);
        }
        return $this->_getData('route_path');
    }

    /**
     * Set route name
     *
     * @param string $data
     * @return \Magento\Core\Model\Url
     */
    public function setRouteName($data)
    {
        if ($this->_getData('route_name') == $data) {
            return $this;
        }
        $this->unsetData('route_front_name')
            ->unsetData('route_path')
            ->unsetData('controller_name')
            ->unsetData('action_name')
            ->unsetData('secure');
        return $this->setData('route_name', $data);
    }

    /**
     * Retrieve route front name
     *
     * @return string
     */
    public function getRouteFrontName()
    {
        if (!$this->hasData('route_front_name')) {
            $routeId = $this->getRouteName();
            $router = $this->_routerList->getRouterByRoute($routeId);
            $frontName = $router->getFrontNameByRoute($routeId);

            $this->setRouteFrontName($frontName);
        }

        return $this->_getData('route_front_name');
    }

    /**
     * Retrieve route name
     *
     * @param mixed $default
     * @return string|null
     */
    public function getRouteName($default = null)
    {
        return $this->_getData('route_name') ? $this->_getData('route_name') : $default;
    }

    /**
     * Set Controller Name
     *
     * Reset action name and route path if has change
     *
     * @param string $data
     * @return \Magento\Core\Model\Url
     */
    public function setControllerName($data)
    {
        if ($this->_getData('controller_name') == $data) {
            return $this;
        }
        $this->unsetData('route_path')->unsetData('action_name')->unsetData('secure');
        return $this->setData('controller_name', $data);
    }

    /**
     * Retrieve controller name
     *
     * @param mixed $default
     * @return string|null
     */
    public function getControllerName($default = null)
    {
        return $this->_getData('controller_name') ? $this->_getData('controller_name') : null;
    }

    /**
     * Set Action name
     * Reseted route path if action name has change
     *
     * @param string $data
     * @return \Magento\Core\Model\Url
     */
    public function setActionName($data)
    {
        if ($this->_getData('action_name') == $data) {
            return $this;
        }
        $this->unsetData('route_path');
        return $this->setData('action_name', $data)->unsetData('secure');
    }

    /**
     * Retrieve action name
     *
     * @param mixed $default
     * @return string|null
     */
    public function getActionName($default = null)
    {
        return $this->_getData('action_name') ? $this->_getData('action_name') : $default;
    }

    /**
     * Set route params
     *
     * @param array $data
     * @param boolean $unsetOldParams
     * @return \Magento\Core\Model\Url
     */
    public function setRouteParams(array $data, $unsetOldParams = true)
    {
        if (isset($data['_type'])) {
            $this->setType($data['_type']);
            unset($data['_type']);
        }

        if (isset($data['_store'])) {
            $this->setStore($data['_store']);
            unset($data['_store']);
        }

        if (isset($data['_forced_secure'])) {
            $this->setSecure((bool)$data['_forced_secure']);
            $this->setSecureIsForced(true);
            unset($data['_forced_secure']);
        } elseif (isset($data['_secure'])) {
            $this->setSecure((bool)$data['_secure']);
            unset($data['_secure']);
        }

        if (isset($data['_absolute'])) {
            unset($data['_absolute']);
        }

        if ($unsetOldParams) {
            $this->unsetData('route_params');
        }

        $this->setUseUrlCache(true);
        if (isset($data['_current'])) {
            if (is_array($data['_current'])) {
                foreach ($data['_current'] as $key) {
                    if (array_key_exists($key, $data) || !$this->getRequest()->getUserParam($key)) {
                        continue;
                    }
                    $data[$key] = $this->getRequest()->getUserParam($key);
                }
            } elseif ($data['_current']) {
                foreach ($this->getRequest()->getUserParams() as $key => $value) {
                    if (array_key_exists($key, $data) || $this->getRouteParam($key)) {
                        continue;
                    }
                    $data[$key] = $value;
                }
                foreach ($this->getRequest()->getQuery() as $key => $value) {
                    $this->setQueryParam($key, $value);
                }
                $this->setUseUrlCache(false);
            }
            unset($data['_current']);
        }

        if (isset($data['_use_rewrite'])) {
            unset($data['_use_rewrite']);
        }

        if (isset($data['_store_to_url']) && (bool)$data['_store_to_url'] === true) {
            if (!$this->_coreStoreConfig->getConfig(\Magento\Core\Model\Store::XML_PATH_STORE_IN_URL, $this->getStore())
                && !$this->_storeManager->hasSingleStore()
            ) {
                $this->setQueryParam('___store', $this->getStore()->getCode());
            }
        }
        unset($data['_store_to_url']);

        foreach ($data as $k => $v) {
            $this->setRouteParam($k, $v);
        }

        return $this;
    }

    /**
     * Retrieve route params
     *
     * @return array
     */
    public function getRouteParams()
    {
        return $this->_getData('route_params');
    }

    /**
     * Set route param
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Core\Model\Url
     */
    public function setRouteParam($key, $data)
    {
        $params = $this->_getData('route_params');
        if (isset($params[$key]) && $params[$key] == $data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('route_path');
        return $this->setData('route_params', $params);
    }

    /**
     * Retrieve route params
     *
     * @param string $key
     * @return mixed
     */
    public function getRouteParam($key)
    {
        return $this->getData('route_params', $key);
    }

    /**
     * Retrieve route URL
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     */
    public function getRouteUrl($routePath = null, $routeParams = null)
    {
        if (filter_var($routePath, FILTER_VALIDATE_URL)) {
            return $routePath;
        }

        $this->unsetData('route_params');

        if (isset($routeParams['_direct'])) {
            if (is_array($routeParams)) {
                $this->setRouteParams($routeParams, false);
            }
            return $this->getBaseUrl() . $routeParams['_direct'];
        }

        $this->setRoutePath($routePath);
        if (is_array($routeParams)) {
            $this->setRouteParams($routeParams, false);
        }

        $url = $this->getBaseUrl() . $this->getRoutePath($routeParams);
        return $url;
    }

    /**
     * If the host was switched but session cookie won't recognize it - add session id to query
     *
     * @return \Magento\Core\Model\Url
     */
    public function checkCookieDomains()
    {
        $hostArr = explode(':', $this->getRequest()->getServer('HTTP_HOST'));
        if ($hostArr[0] !== $this->getHost()) {
            $session = $this->_session;
            if (!$session->isValidForHost($this->getHost())) {
                if (!self::$_encryptedSessionId) {
                    $helper = $this->_coreData;
                    if (!$helper) {
                        return $this;
                    }
                    self::$_encryptedSessionId = $session->getEncryptedSessionId();
                }
                $this->setQueryParam($session->getSessionIdQueryParam(), self::$_encryptedSessionId);
            }
        }
        return $this;
    }

    /**
     * Add session param
     *
     * @return \Magento\Core\Model\Url
     */
    public function addSessionParam()
    {
        if (!self::$_encryptedSessionId) {
            $helper = $this->_coreData;
            if (!$helper) {
                return $this;
            }
            self::$_encryptedSessionId = $this->_session->getEncryptedSessionId();
        }
        $this->setQueryParam($this->_session->getSessionIdQueryParam(), self::$_encryptedSessionId);
        return $this;
    }

    /**
     * Set URL query param(s)
     *
     * @param mixed $data
     * @return \Magento\Core\Model\Url
     */
    public function setQuery($data)
    {
        if ($this->_getData('query') == $data) {
            return $this;
        }
        $this->unsetData('query_params');
        return $this->setData('query', $data);
    }

    /**
     * Get query params part of url
     *
     * @param bool $escape "&" escape flag
     * @return string
     */
    public function getQuery($escape = false)
    {
        if (!$this->hasData('query')) {
            $query = '';
            $params = $this->getQueryParams();
            if (is_array($params)) {
                ksort($params);
                $query = http_build_query($params, '', $escape ? '&amp;' : '&');
            }
            $this->setData('query', $query);
        }
        return $this->_getData('query');
    }

    /**
     * Set query Params as array
     *
     * @param array $data
     * @return \Magento\Core\Model\Url
     */
    public function setQueryParams(array $data)
    {
        $this->unsetData('query');

        if ($this->_getData('query_params') == $data) {
            return $this;
        }

        $params = $this->_getData('query_params');
        if (!is_array($params)) {
            $params = array();
        }
        foreach ($data as $param => $value) {
            $params[$param] = $value;
        }
        $this->setData('query_params', $params);

        return $this;
    }

    /**
     * Purge Query params array
     *
     * @return \Magento\Core\Model\Url
     */
    public function purgeQueryParams()
    {
        $this->setData('query_params', array());
        return $this;
    }

    /**
     * Return Query Params
     *
     * @return array
     */
    public function getQueryParams()
    {
        if (!$this->hasData('query_params')) {
            $params = array();
            if ($this->_getData('query')) {
                foreach (explode('&', $this->_getData('query')) as $param) {
                    $paramArr = explode('=', $param);
                    $params[$paramArr[0]] = urldecode($paramArr[1]);
                }
            }
            $this->setData('query_params', $params);
        }
        return $this->_getData('query_params');
    }

    /**
     * Set query param
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Core\Model\Url
     */
    public function setQueryParam($key, $data)
    {
        $params = $this->getQueryParams();
        if (isset($params[$key]) && $params[$key] == $data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('query');
        return $this->setData('query_params', $params);
    }

    /**
     * Retrieve query param
     *
     * @param string $key
     * @return mixed
     */
    public function getQueryParam($key)
    {
        if (!$this->hasData('query_params')) {
            $this->getQueryParams();
        }
        return $this->getData('query_params', $key);
    }

    /**
     * Set fragment to URL
     *
     * @param string $data
     * @return \Magento\Core\Model\Url
     */
    public function setFragment($data)
    {
        return $this->setData('fragment', $data);
    }

    /**
     * Retrieve URL fragment
     *
     * @return string|null
     */
    public function getFragment()
    {
        return $this->_getData('fragment');
    }

    /**
     * Build url by requested path and parameters
     *
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        if (filter_var($routePath, FILTER_VALIDATE_URL)) {
            return $routePath;
        }

        $escapeQuery = false;

        /**
         * All system params should be unset before we call getRouteUrl
         * this method has condition for adding default controller and action names
         * in case when we have params
         */
        $fragment = null;
        if (isset($routeParams['_fragment'])) {
            $fragment = $routeParams['_fragment'];
            unset($routeParams['_fragment']);
        }

        if (isset($routeParams['_escape'])) {
            $escapeQuery = $routeParams['_escape'];
            unset($routeParams['_escape']);
        }

        $query = null;
        if (isset($routeParams['_query'])) {
            $this->purgeQueryParams();
            $query = $routeParams['_query'];
            unset($routeParams['_query']);
        }

        $noSid = null;
        if (isset($routeParams['_nosid'])) {
            $noSid = (bool)$routeParams['_nosid'];
            unset($routeParams['_nosid']);
        }
        $url = $this->getRouteUrl($routePath, $routeParams);
        /**
         * Apply query params, need call after getRouteUrl for rewrite _current values
         */
        if ($query !== null) {
            if (is_string($query)) {
                $this->setQuery($query);
            } elseif (is_array($query)) {
                $this->setQueryParams($query, !empty($routeParams['_current']));
            }
            if ($query === false) {
                $this->setQueryParams(array());
            }
        }

        if ($noSid !== true) {
            $this->_prepareSessionUrl($url);
        }

        $query = $this->getQuery($escapeQuery);
        if ($query) {
            $mark = (strpos($url, '?') === false) ? '?' : ($escapeQuery ? '&amp;' : '&');
            $url .= $mark . $query;
            $this->unsetData('query');
            $this->unsetData('query_params');
        }

        if (!is_null($fragment)) {
            $url .= '#' . $fragment;
        }

        return $this->escape($url);
    }

    /**
     * Check and add session id to URL
     *
     * @param string $url
     *
     * @return \Magento\Core\Model\Url
     */
    protected function _prepareSessionUrl($url)
    {
        return $this->_prepareSessionUrlWithParams($url, array());
    }

    /**
     * Check and add session id to URL, session is obtained with parameters
     *
     * @param string $url
     * @param array $params
     *
     * @return \Magento\Core\Model\Url
     */
    protected function _prepareSessionUrlWithParams($url, array $params)
    {
        if (!$this->getUseSession()) {
            return $this;
        }
        $sessionId = $this->_session->getSessionIdForHost($url);
        if ($this->_app->getUseSessionVar() && !$sessionId) {
            $this->setQueryParam('___SID', $this->isSecure() ? 'S' : 'U'); // Secure/Unsecure
        } else if ($sessionId) {
            $this->setQueryParam($this->_session->getSessionIdQueryParam(), $sessionId);
        }
        return $this;
    }

    /**
     * Rebuild URL to handle the case when session ID was changed
     *
     * @param string $url
     * @return string
     */
    public function getRebuiltUrl($url)
    {
        $this->parseUrl($url);
        $port = $this->getPort();
        if ($port) {
            $port = ':' . $port;
        } else {
            $port = '';
        }
        $url = $this->getScheme() . '://' . $this->getHost() . $port . $this->getPath();

        $this->_prepareSessionUrl($url);

        $query = $this->getQuery();
        if ($query) {
            $url .= '?' . $query;
        }

        $fragment = $this->getFragment();
        if ($fragment) {
            $url .= '#' . $fragment;
        }

        return $this->escape($url);
    }

    /**
     * Escape (enclosure) URL string
     *
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        $value = str_replace('"', '%22', $value);
        $value = str_replace("'", '%27', $value);
        $value = str_replace('>', '%3E', $value);
        $value = str_replace('<', '%3C', $value);
        return $value;
    }

    /**
     * Build url by direct url and parameters
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function getDirectUrl($url, $params = array())
    {
        $params['_direct'] = $url;
        return $this->getUrl('', $params);
    }

    /**
     * Replace Session ID value in URL
     *
     * @param string $html
     * @return string
     */
    public function sessionUrlVar($html)
    {
        return preg_replace_callback('#(\?|&amp;|&)___SID=([SU])(&amp;|&)?#',
            array($this, "sessionVarCallback"), $html);
    }

    /**
     * Check and return use SID for URL
     *
     * @param bool $secure
     * @return bool
     */
    public function useSessionIdForUrl($secure = false)
    {
        $key = 'use_session_id_for_url_' . (int) $secure;
        if (is_null($this->getData($key))) {
            $httpHost = $this->_request->getHttpHost();
            $urlHost = parse_url($this->getStore()->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_LINK, $secure),
                PHP_URL_HOST);

            if ($httpHost != $urlHost) {
                $this->setData($key, true);
            } else {
                $this->setData($key, false);
            }
        }
        return $this->getData($key);
    }

    /**
     * Callback function for session replace
     *
     * @param array $match
     * @return string
     */
    public function sessionVarCallback($match)
    {
        if ($this->useSessionIdForUrl($match[2] == 'S' ? true : false)) {
            return $match[1]
                . $this->_session->getSessionIdQueryParam()
                . '=' . $this->_session->getEncryptedSessionId()
                . (isset($match[3]) ? $match[3] : '');
        } else {
            if ($match[1] == '?' && isset($match[3])) {
                return '?';
            } elseif ($match[1] == '?' && !isset($match[3])) {
                return '';
            } elseif (($match[1] == '&amp;' || $match[1] == '&') && !isset($match[3])) {
                return '';
            } elseif (($match[1] == '&amp;' || $match[1] == '&') && isset($match[3])) {
                return $match[3];
            }
        }
        return '';
    }

    /**
     * Check if users originated URL is one of the domain URLs assigned to stores
     *
     * @return boolean
     */
    public function isOwnOriginUrl()
    {
        $storeDomains = array();
        $referer = parse_url($this->_app->getRequest()->getServer('HTTP_REFERER'), PHP_URL_HOST);
        foreach ($this->_storeManager->getStores() as $store) {
            $storeDomains[] = parse_url($store->getBaseUrl(), PHP_URL_HOST);
            $storeDomains[] = parse_url($store->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_LINK, true), PHP_URL_HOST);
        }
        $storeDomains = array_unique($storeDomains);
        if (empty($referer) || in_array($referer, $storeDomains)) {
            return true;
        }
        return false;
    }

    /**
     * Return frontend redirect URL with SID and other session parameters if any
     *
     * @param string $url
     *
     * @return string
     */
    public function getRedirectUrl($url)
    {
        $this->_prepareSessionUrlWithParams($url, array(
            'name' => \Magento\Core\Controller\Front\Action::SESSION_NAMESPACE
        ));

        $query = $this->getQuery(false);
        if ($query) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
        }

        return $url;
    }
}
