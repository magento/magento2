<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\HostChecker;

/**
 * URL
 *
 * Properties:
 *
 * - request
 *
 * - relative_url: true, false
 * - type: 'link', 'skin', 'js', 'media'
 * - scope: instanceof \Magento\Framework\Url\ScopeInterface
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
 * - scopeview_path: 'scopeview/'
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
 * @codingStandardsIgnoreStart
 * URL structure:
 *
 * https://user:password@host:443/base_path/[base_script][scopeview_path]route_name/controller_name/action_name/param1/value1?query_param=query_value#fragment
 *       \__________A___________/\____________________________________B_____________________________________/
 * \__________________C___________________/              \__________________D_________________/ \_____E_____/
 * \_____________F______________/                        \___________________________G______________________/
 * \___________________________________________________H____________________________________________________/
 * @codingStandardsIgnoreEnd
 *
 * - A: authority
 * - B: path
 * - C: absolute_base_url
 * - D: action_path
 * - E: route_params
 * - F: host_url
 * - G: route_path
 * - H: route_url
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Url extends \Magento\Framework\DataObject implements \Magento\Framework\UrlInterface
{
    /**
     * Configuration data cache
     *
     * @var array
     */
    protected static $_configDataCache;

    /**
     * Reserved Route parameter keys
     *
     * @var array
     */
    protected $_reservedRouteParams = [
        '_scope',
        '_type',
        '_secure',
        '_forced_secure',
        '_use_rewrite',
        '_nosid',
        '_absolute',
        '_current',
        '_direct',
        '_fragment',
        '_escape',
        '_query',
        '_scope_to_url',
    ];

    /**
     * @var string
     */
    protected $_scopeType;

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
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
     * @var \Magento\Framework\Url\SecurityInfoInterface
     */
    protected $_urlSecurityInfo;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * Constructor
     *
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    protected $_routeConfig;

    /**
     * @var \Magento\Framework\Url\RouteParamsResolverInterface
     */
    private $_routeParamsResolver;

    /**
     * @var \Magento\Framework\Url\RouteParamsResolverFactory
     */
    private $_routeParamsResolverFactory;

    /**
     * @var \Magento\Framework\Url\ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $_queryParamsResolver;

    /**
     * Cache urls requested by getUrl method
     *
     * @var array
     */
    private $cacheUrl = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Url\RouteParamsPreprocessorInterface
     */
    protected $routeParamsPreprocessor;

    /**
     * @var \Magento\Framework\Url\ModifierInterface
     */
    private $urlModifier;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var HostChecker
     */
    private $hostChecker;

    /**
     * @var Json
     */
    private $serializer;

    /**
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
     * @param array $data
     * @param HostChecker|null $hostChecker
     * @param Json|null $serializer
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
        array $data = [],
        HostChecker $hostChecker = null,
        Json $serializer = null
    ) {
        $this->_request = $request;
        $this->_routeConfig = $routeConfig;
        $this->_urlSecurityInfo = $urlSecurityInfo;
        $this->_scopeResolver = $scopeResolver;
        $this->_session = $session;
        $this->_sidResolver = $sidResolver;
        $this->_routeParamsResolverFactory = $routeParamsResolverFactory;
        $this->_queryParamsResolver = $queryParamsResolver;
        $this->_scopeConfig = $scopeConfig;
        $this->routeParamsPreprocessor = $routeParamsPreprocessor;
        $this->_scopeType = $scopeType;
        $this->hostChecker = $hostChecker ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(HostChecker::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($data);
    }

    /**
     * Initialize object data from retrieved url
     *
     * @param   string $url
     * @return  \Magento\Framework\UrlInterface
     */
    protected function _parseUrl($url)
    {
        $data = parse_url($url);
        $parts = [
            'scheme' => 'setScheme',
            'host' => 'setHost',
            'port' => 'setPort',
            'user' => 'setUser',
            'pass' => 'setPassword',
            'path' => 'setPath',
            'query' => '_setQuery',
            'fragment' => 'setFragment',
        ];

        foreach ($parts as $component => $method) {
            if (isset($data[$component])) {
                $this->{$method}($data[$component]);
            }
        }
        return $this;
    }

    /**
     * Set use session rule
     *
     * @param bool $useSession
     * @return \Magento\Framework\UrlInterface
     */
    public function setUseSession($useSession)
    {
        $this->_useSession = (bool) $useSession;
        return $this;
    }

    /**
     * Retrieve use session rule
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseSession()
    {
        if ($this->_useSession === null) {
            $this->_useSession = $this->_sidResolver->getUseSessionInUrl();
        }
        return $this->_useSession;
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
        if ($prefix === null) {
            $prefix = 'web/' . ($this->_isSecure() ? 'secure' : 'unsecure') . '/';
        }
        $path = $prefix . $key;

        $cacheId = $this->_getConfigCacheId($path);
        if (!isset(self::$_configDataCache[$cacheId])) {
            $data = $this->_getConfig($path);
            self::$_configDataCache[$cacheId] = $data;
        }

        return self::$_configDataCache[$cacheId];
    }

    /**
     * Get cache id for config path
     *
     * @param string $path
     * @return string
     */
    protected function _getConfigCacheId($path)
    {
        return $this->_getScope()->getCode() . '/' . $path;
    }

    /**
     * Get config data by path
     *
     * @param string $path
     * @return null|string
     */
    protected function _getConfig($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            $this->_scopeType,
            $this->_getScope()
        );
    }

    /**
     * Set request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\UrlInterface
     */
    public function setRequest(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Zend request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function _getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve URL type
     *
     * @return string
     */
    protected function _getType()
    {
        if (!$this->getRouteParamsResolver()->hasData('type')) {
            $this->getRouteParamsResolver()->setData('type', self::DEFAULT_URL_TYPE);
        }
        return $this->getRouteParamsResolver()->getType();
    }

    /**
     * Retrieve is secure mode URL
     *
     * @return bool
     */
    protected function _isSecure()
    {
        if ($this->_request->isSecure()) {
            return true;
        }

        if ($this->getRouteParamsResolver()->hasData('secure_is_forced')) {
            return (bool) $this->getRouteParamsResolver()->getData('secure');
        }

        if (!$this->_getScope()->isUrlSecure()) {
            return false;
        }

        if (!$this->getRouteParamsResolver()->hasData('secure')) {
            if ($this->_getType() == UrlInterface::URL_TYPE_LINK) {
                $pathSecure = $this->_urlSecurityInfo->isSecure('/' . $this->_getActionPath());
                $this->getRouteParamsResolver()->setData('secure', $pathSecure);
            } elseif ($this->_getType() == UrlInterface::URL_TYPE_STATIC) {
                $isRequestSecure = $this->_getRequest()->isSecure();
                $this->getRouteParamsResolver()->setData('secure', $isRequestSecure);
            } else {
                $this->getRouteParamsResolver()->setData('secure', true);
            }
        }

        return $this->getRouteParamsResolver()->getData('secure');
    }

    /**
     * Set scope entity
     *
     * @param mixed $params
     * @return \Magento\Framework\UrlInterface
     */
    public function setScope($params)
    {
        $this->setData('scope', $this->_scopeResolver->getScope($params));
        $this->getRouteParamsResolver()->setScope($this->_scopeResolver->getScope($params));
        return $this;
    }

    /**
     * Get current scope for the url instance
     *
     * @return \Magento\Framework\Url\ScopeInterface
     */
    protected function _getScope()
    {
        if (!$this->hasData('scope')) {
            $this->setScope(null);
        }
        return $this->_getData('scope');
    }

    /**
     * Retrieve Base URL
     *
     * @param array $params
     * @return string
     */
    public function getBaseUrl($params = [])
    {
        /**
         *  Original Scope
         */
        $origScope = $this->_getScope();

        if (isset($params['_scope'])) {
            $this->setScope($params['_scope']);
        }
        if (isset($params['_type'])) {
            $this->getRouteParamsResolver()->setType($params['_type']);
        }

        if (isset($params['_secure'])) {
            $this->getRouteParamsResolver()->setSecure($params['_secure']);
        }

        /**
         * Add availability support urls without scope code
         */
        if ($this->_getType() == UrlInterface::URL_TYPE_LINK
            && $this->_getRequest()->isDirectAccessFrontendName(
                $this->_getRouteFrontName()
            )
        ) {
            $this->getRouteParamsResolver()->setType(UrlInterface::URL_TYPE_DIRECT_LINK);
        }

        $result = $this->_getScope()->getBaseUrl($this->_getType(), $this->_isSecure());

        // setting back the original scope
        $this->setScope($origScope);
        $this->getRouteParamsResolver()->setType(self::DEFAULT_URL_TYPE);

        return $result;
    }

    /**
     * Set Route Parameters
     *
     * @param string $data
     * @return \Magento\Framework\UrlInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _setRoutePath($data)
    {
        if ($this->_getData('route_path') == $data) {
            return $this;
        }

        $this->unsetData('route_path');
        $routePieces = explode('/', $data);

        $route = array_shift($routePieces);
        if ('*' === $route) {
            $route = $this->_getRequest()->getRouteName();
        }
        $this->_setRouteName($route);

        $controller = '';
        if (!empty($routePieces)) {
            $controller = array_shift($routePieces);
            if ('*' === $controller) {
                $controller = $this->_getRequest()->getControllerName();
            }
        }
        $this->_setControllerName($controller);

        $action = '';
        if (!empty($routePieces)) {
            $action = array_shift($routePieces);
            if ('*' === $action) {
                $action = $this->_getRequest()->getActionName();
            }
        }
        $this->_setActionName($action);

        if (!empty($routePieces)) {
            while (!empty($routePieces)) {
                $key = array_shift($routePieces);
                if (!empty($routePieces)) {
                    $value = array_shift($routePieces);
                    $this->getRouteParamsResolver()->setRouteParam($key, $value);
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
    protected function _getActionPath()
    {
        if (!$this->_getRouteName()) {
            return '';
        }

        $hasParams = (bool) $this->_getRouteParams();
        $path = $this->_getRouteFrontName() . '/';

        if ($this->_getControllerName()) {
            $path .= $this->_getControllerName() . '/';
        } elseif ($hasParams) {
            $path .= self::DEFAULT_CONTROLLER_NAME . '/';
        }
        if ($this->_getActionName()) {
            $path .= $this->_getActionName() . '/';
        } elseif ($hasParams) {
            $path .= self::DEFAULT_ACTION_NAME . '/';
        }

        return $path;
    }

    /**
     * Retrieve route path
     *
     * @param array $routeParams
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getRoutePath($routeParams = [])
    {
        if (!$this->hasData('route_path')) {
            $routePath = $this->_getRequest()->getAlias(self::REWRITE_REQUEST_PATH_ALIAS);
            if (!empty($routeParams['_use_rewrite']) && $routePath !== null) {
                $this->setData('route_path', $routePath);
                return $routePath;
            }
            $routePath = $this->_getActionPath();
            if ($this->_getRouteParams()) {
                foreach ($this->_getRouteParams() as $key => $value) {
                    if ($value === null || false === $value || '' === $value || !is_scalar($value)) {
                        continue;
                    }
                    $routePath .= $key . '/' . $value . '/';
                }
            }
            $this->setData('route_path', $routePath);
        }
        return $this->_getData('route_path');
    }

    /**
     * Set route name
     *
     * @param string $data
     * @return \Magento\Framework\UrlInterface
     */
    protected function _setRouteName($data)
    {
        if ($this->_getData('route_name') == $data) {
            return $this;
        }
        $this->unsetData('route_front_name')
            ->unsetData('route_path')
            ->unsetData('controller_name')
            ->unsetData('action_name');
        $this->_queryParamsResolver->unsetData('secure');
        return $this->setData('route_name', $data);
    }

    /**
     * Retrieve route front name
     *
     * @return string
     */
    protected function _getRouteFrontName()
    {
        if (!$this->hasData('route_front_name')) {
            $frontName = $this->_routeConfig->getRouteFrontName(
                $this->_getRouteName(),
                $this->_scopeResolver->getAreaCode()
            );
            $this->setData('route_front_name', $frontName);
        }
        return $this->_getData('route_front_name');
    }

    /**
     * Retrieve route name
     *
     * @param mixed $default
     * @return string|null
     */
    protected function _getRouteName($default = null)
    {
        return $this->_getData('route_name') ? $this->_getData('route_name') : $default;
    }

    /**
     * Set Controller Name
     *
     * Reset action name and route path if has change
     *
     * @param string $data
     * @return \Magento\Framework\UrlInterface
     */
    protected function _setControllerName($data)
    {
        if ($this->_getData('controller_name') == $data) {
            return $this;
        }
        $this->unsetData('route_path')->unsetData('action_name');
        $this->_queryParamsResolver->unsetData('secure');
        return $this->setData('controller_name', $data);
    }

    /**
     * Retrieve controller name
     *
     * @param mixed $default
     * @return string|null
     */
    protected function _getControllerName($default = null)
    {
        return $this->_getData('controller_name') ? $this->_getData('controller_name') : $default;
    }

    /**
     * Set Action name
     * Reseted route path if action name has change
     *
     * @param string $data
     * @return \Magento\Framework\UrlInterface
     */
    protected function _setActionName($data)
    {
        if ($this->_getData('action_name') == $data) {
            return $this;
        }
        $this->unsetData('route_path');
        $this->setData('action_name', $data);
        $this->_queryParamsResolver->unsetData('secure');
        return $this;
    }

    /**
     * Retrieve action name
     *
     * @param mixed $default
     * @return string|null
     */
    protected function _getActionName($default = null)
    {
        return $this->_getData('action_name') ? $this->_getData('action_name') : $default;
    }

    /**
     * Set route params
     *
     * @param array $data
     * @param boolean $unsetOldParams
     * @return \Magento\Framework\UrlInterface
     */
    protected function _setRouteParams(array $data, $unsetOldParams = true)
    {
        $this->getRouteParamsResolver()->setRouteParams($data, $unsetOldParams);
        return $this;
    }

    /**
     * Retrieve route params
     *
     * @return array
     */
    protected function _getRouteParams()
    {
        return $this->getRouteParamsResolver()->getRouteParams();
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

        $this->getRouteParamsResolver()->unsetData('route_params');

        if (isset($routeParams['_direct'])) {
            if (is_array($routeParams)) {
                $this->_setRouteParams($routeParams, false);
            }
            return $this->getBaseUrl() . $routeParams['_direct'];
        }

        $this->_setRoutePath($routePath);
        if (is_array($routeParams)) {
            $this->_setRouteParams($routeParams, false);
        }

        return $this->getBaseUrl($routeParams) . $this->_getRoutePath($routeParams);
    }

    /**
     * Add session param
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function addSessionParam()
    {
        $this->setQueryParam(
            $this->_sidResolver->getSessionIdQueryParam($this->_session),
            $this->_session->getSessionId()
        );
        return $this;
    }

    /**
     * Set URL query param(s)
     *
     * @param mixed $data
     * @return \Magento\Framework\UrlInterface
     */
    protected function _setQuery($data)
    {
        return $this->_queryParamsResolver->setQuery($data);
    }

    /**
     * Get query params part of url
     *
     * @param bool $escape "&" escape flag
     * @return string
     */
    protected function _getQuery($escape = false)
    {
        return $this->_queryParamsResolver->getQuery($escape);
    }

    /**
     * Add query Params as array
     *
     * @param array $data
     * @return \Magento\Framework\UrlInterface
     */
    public function addQueryParams(array $data)
    {
        $this->_queryParamsResolver->addQueryParams($data);
        return $this;
    }

    /**
     * Set query param
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Framework\UrlInterface
     */
    public function setQueryParam($key, $data)
    {
        $this->_queryParamsResolver->setQueryParam($key, $data);
        return $this;
    }

    /**
     * Retrieve URL fragment
     *
     * @return string|null
     */
    protected function _getFragment()
    {
        return $this->_getData('fragment');
    }

    /**
     * Build and cache url by requested path and parameters
     *
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        if (filter_var($routePath, FILTER_VALIDATE_URL)) {
            return $routePath;
        }

        $routeParams = $this->routeParamsPreprocessor
            ->execute($this->_scopeResolver->getAreaCode(), $routePath, $routeParams);

        $isCached = true;
        $isArray = is_array($routeParams);

        if ($isArray) {
            array_walk_recursive(
                $routeParams,
                function ($item) use (&$isCached) {
                    if (is_object($item)) {
                        $isCached = false;
                    }
                }
            );
        }

        if (!$isCached) {
            return $this->getUrlModifier()->execute(
                $this->createUrl($routePath, $routeParams)
            );
        }

        $cachedParams = $routeParams;
        if ($isArray) {
            ksort($cachedParams);
        }

        $cacheKey = sha1($routePath . $this->serializer->serialize($cachedParams));
        if (!isset($this->cacheUrl[$cacheKey])) {
            $this->cacheUrl[$cacheKey] = $this->getUrlModifier()->execute(
                $this->createUrl($routePath, $routeParams)
            );
        }

        return $this->cacheUrl[$cacheKey];
    }

    /**
     * Build url by requested path and parameters
     *
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function createUrl($routePath = null, array $routeParams = null)
    {
        $escapeQuery = false;
        $escapeParams = true;

        /**
         * All system params should be unset before we call getRouteUrl
         * this method has condition for adding default controller and action names
         * in case when we have params
         */
        $this->getRouteParamsResolver()->unsetData('secure');
        $fragment = null;
        if (isset($routeParams['_fragment'])) {
            $fragment = $routeParams['_fragment'];
            unset($routeParams['_fragment']);
        }

        if (isset($routeParams['_escape'])) {
            $escapeQuery = $routeParams['_escape'];
            unset($routeParams['_escape']);
        }

        if (isset($routeParams['_escape_params'])) {
            $escapeParams = $routeParams['_escape_params'];
            unset($routeParams['_escape_params']);
        }
        $this->getRouteParamsResolver()->setData('escape_params', $escapeParams);

        $query = null;
        if (isset($routeParams['_query'])) {
            $this->_queryParamsResolver->setQueryParams([]);
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
                $this->_setQuery($query);
            } elseif (is_array($query)) {
                $this->addQueryParams($query, !empty($routeParams['_current']));
            }
            if ($query === false) {
                $this->addQueryParams([]);
            }
        }

        if ($noSid !== true) {
            $this->_prepareSessionUrl($url);
        }

        $query = $this->_getQuery($escapeQuery);
        if ($query) {
            $mark = strpos($url, '?') === false ? '?' : ($escapeQuery ? '&amp;' : '&');
            $url .= $mark . $query;
            $this->_queryParamsResolver->unsetData('query');
            $this->_queryParamsResolver->unsetData('query_params');
        }

        if ($fragment !== null) {
            $url .= '#' . $this->getEscaper()->encodeUrlParam($fragment);
        }
        $this->getRouteParamsResolver()->unsetData('secure');
        $this->getRouteParamsResolver()->unsetData('escape_params');

        return $url;
    }

    /**
     * Check and add session id to URL
     *
     * @param string $url
     *
     * @return \Magento\Framework\UrlInterface
     */
    protected function _prepareSessionUrl($url)
    {
        if (!$this->getUseSession()) {
            return $this;
        }
        $sessionId = $this->_session->getSessionIdForHost($url);
        if ($this->_sidResolver->getUseSessionVar() && !$sessionId) {
            $this->setQueryParam('___SID', $this->_isSecure() ? 'S' : 'U');
        } elseif ($sessionId) {
            $this->setQueryParam($this->_sidResolver->getSessionIdQueryParam($this->_session), $sessionId);
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
        $this->_parseUrl($url);
        $port = $this->getPort();
        if ($port) {
            $port = ':' . $port;
        } else {
            $port = '';
        }
        $url = $this->getScheme() . '://' . $this->getHost() . $port . $this->getPath();

        $this->_prepareSessionUrl($url);

        $query = $this->_getQuery();
        if ($query) {
            $url .= '?' . $query;
        }

        $fragment = $this->_getFragment();
        if ($fragment) {
            $url .= '#' . $this->getEscaper()->encodeUrlParam($fragment);
        }

        return $url;
    }

    /**
     * Escape (enclosure) URL string
     *
     * @param string $value
     * @return string
     * @deprecated 100.2.0
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
    public function getDirectUrl($url, $params = [])
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
        return preg_replace_callback(
            '#(\?|&amp;|&)___SID=([SU])(&amp;|&)?#',
            // @codingStandardsIgnoreStart
            /**
             * Callback function for session replace
             *
             * @param array $match
             * @return string
             */
            // @codingStandardsIgnoreEnd
            function ($match) {
                if ($this->useSessionIdForUrl($match[2] == 'S' ? true : false)) {
                    return $match[1] . $this->_sidResolver->getSessionIdQueryParam($this->_session) . '='
                        . $this->_session->getSessionId() . (isset($match[3]) ? $match[3] : '');
                } else {
                    if ($match[1] == '?') {
                        return isset($match[3]) ? '?' : '';
                    } elseif ($match[1] == '&amp;' || $match[1] == '&') {
                        return isset($match[3]) ? $match[3] : '';
                    }
                }
            },
            $html
        );
    }

    /**
     * Check and return use SID for URL
     *
     * @param bool $secure
     * @return bool
     */
    public function useSessionIdForUrl($secure = false)
    {
        $key = 'use_session_id_for_url_' . (int)$secure;
        if ($this->getData($key) === null) {
            $httpHost = $this->_request->getHttpHost();
            $urlHost = parse_url(
                $this->_getScope()->getBaseUrl(UrlInterface::URL_TYPE_LINK, $secure),
                PHP_URL_HOST
            );

            if ($httpHost != $urlHost) {
                $this->setData($key, true);
            } else {
                $this->setData($key, false);
            }
        }
        return $this->getData($key);
    }

    /**
     * Check if users originated URL is one of the domain URLs assigned to scopes
     *
     * @return boolean
     */
    public function isOwnOriginUrl()
    {
        return $this->hostChecker->isOwnOrigin($this->_request->getServer('HTTP_REFERER'));
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
        $this->_prepareSessionUrl($url);
        $query = $this->_getQuery(false);
        if ($query) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
        }

        return $url;
    }

    /**
     * Retrieve current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        $httpHostWithPort = $this->_request->getHttpHost(false);
        $httpHostWithPort = explode(':', $httpHostWithPort);
        $httpHost = isset($httpHostWithPort[0]) ? $httpHostWithPort[0] : '';
        $port = '';
        if (isset($httpHostWithPort[1])) {
            $defaultPorts = [
                \Magento\Framework\App\Request\Http::DEFAULT_HTTP_PORT,
                \Magento\Framework\App\Request\Http::DEFAULT_HTTPS_PORT,
            ];
            if (!in_array($httpHostWithPort[1], $defaultPorts)) {
                /** Custom port */
                $port = ':' . $httpHostWithPort[1];
            }
        }
        return $this->_request->getScheme() . '://' . $httpHost . $port . $this->_request->getRequestUri();
    }

    /**
     * Get Route Params Resolver
     *
     * @return Url\RouteParamsResolverInterface
     */
    protected function getRouteParamsResolver()
    {
        if (!$this->_routeParamsResolver) {
            $this->_routeParamsResolver = $this->_routeParamsResolverFactory->create();
        }
        return $this->_routeParamsResolver;
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

    /**
     * Get escaper
     *
     * @return Escaper
     * @deprecated 100.2.0
     */
    private function getEscaper()
    {
        if ($this->escaper == null) {
            $this->escaper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Escaper::class);
        }
        return $this->escaper;
    }
}
