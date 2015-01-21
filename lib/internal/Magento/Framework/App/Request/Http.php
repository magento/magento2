<?php
/**
 * Http request
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Request;

class Http extends \Zend_Controller_Request_Http implements
    \Magento\Framework\App\RequestInterface,
    \Magento\Framework\App\Http\RequestInterface
{
    const DEFAULT_HTTP_PORT = 80;

    const DEFAULT_HTTPS_PORT = 443;

    const XML_PATH_OFFLOADER_HEADER = 'web/secure/offloader_header';

    /**
     * ORIGINAL_PATH_INFO
     * @var string
     */
    protected $_originalPathInfo = '';

    /**
     * @var string
     */
    protected $_requestString = '';

    /**
     * Path info array used before applying rewrite from config
     *
     * @var null|array
     */
    protected $_rewritedPathInfo = null;

    /**
     * @var string
     */
    protected $_requestedRouteName = null;

    /**
     * @var array
     */
    protected $_routingInfo = [];

    /**
     * @var string
     */
    protected $_route;

    /**
     * @var array
     */
    protected $_directFrontNames;

    /**
     * @var string
     */
    protected $_controllerModule = null;

    /**
     * Straight request flag.
     * If flag is determined no additional logic is applicable
     *
     * @var $_isStraight bool
     */
    protected $_isStraight = false;

    /**
     * Request's original information before forward.
     *
     * @var array
     */
    protected $_beforeForwardInfo = [];

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    protected $_routeConfig;

    /**
     * @var PathInfoProcessorInterface
     */
    private $_pathInfoProcessor;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieReaderInterface
     */
    protected $cookieReader;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\App\Route\ConfigInterface\Proxy $routeConfig
     * @param PathInfoProcessorInterface $pathInfoProcessor
     * @param \Magento\Framework\Stdlib\Cookie\CookieReaderInterface $cookieReader
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager,
     * @param string|null $uri
     * @param array $directFrontNames
     */
    public function __construct(
        \Magento\Framework\App\Route\ConfigInterface\Proxy $routeConfig,
        PathInfoProcessorInterface $pathInfoProcessor,
        \Magento\Framework\Stdlib\Cookie\CookieReaderInterface $cookieReader,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $uri = null,
        $directFrontNames = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_routeConfig = $routeConfig;
        $this->_directFrontNames = $directFrontNames;
        parent::__construct($uri);
        $this->_pathInfoProcessor = $pathInfoProcessor;
        $this->cookieReader = $cookieReader;
    }

    /**
     * Returns ORIGINAL_PATH_INFO.
     * This value is calculated instead of reading PATH_INFO
     * directly from $_SERVER due to cross-platform differences.
     *
     * @return string
     */
    public function getOriginalPathInfo()
    {
        if (empty($this->_originalPathInfo)) {
            $this->setPathInfo();
        }
        return $this->_originalPathInfo;
    }

    /**
     * Set the PATH_INFO string
     * Set the ORIGINAL_PATH_INFO string
     *
     * @param string|null $pathInfo
     * @return $this
     */
    public function setPathInfo($pathInfo = null)
    {
        if ($pathInfo === null) {
            $requestUri = $this->getRequestUri();
            if (null === $requestUri) {
                return $this;
            }

            // Remove the query string from REQUEST_URI
            $pos = strpos($requestUri, '?');
            if ($pos) {
                $requestUri = substr($requestUri, 0, $pos);
            }

            $baseUrl = $this->getBaseUrl();
            $pathInfo = substr($requestUri, strlen($baseUrl));
            if (null !== $baseUrl && false === $pathInfo) {
                $pathInfo = '';
            } elseif (null === $baseUrl) {
                $pathInfo = $requestUri;
            }

            $pathInfo = $this->_pathInfoProcessor->process($this, $pathInfo);

            $this->_originalPathInfo = (string)$pathInfo;

            $this->_requestString = $pathInfo . ($pos !== false ? substr($requestUri, $pos) : '');
        }

        $this->_pathInfo = (string)$pathInfo;
        return $this;
    }

    /**
     * Specify new path info
     * It happen when occur rewrite based on configuration
     *
     * @param string $pathInfo
     * @return $this
     */
    public function rewritePathInfo($pathInfo)
    {
        if ($pathInfo != $this->getPathInfo() && $this->_rewritedPathInfo === null) {
            $this->_rewritedPathInfo = explode('/', trim($this->getPathInfo(), '/'));
        }
        $this->setPathInfo($pathInfo);
        return $this;
    }

    /**
     * Check if code declared as direct access frontend name
     * this mean what this url can be used without store code
     *
     * @param   string $code
     * @return  bool
     */
    public function isDirectAccessFrontendName($code)
    {
        return isset($this->_directFrontNames[$code]);
    }

    /**
     * Get request string
     *
     * @return string
     */
    public function getRequestString()
    {
        return $this->_requestString;
    }

    /**
     * Get base path
     *
     * @return string
     */
    public function getBasePath()
    {
        $path = parent::getBasePath();
        if (empty($path)) {
            $path = '/';
        } else {
            $path = str_replace('\\', '/', $path);
        }
        return $path;
    }

    /**
     * Get base url
     *
     * @param bool $raw
     * @return mixed|string
     */
    public function getBaseUrl($raw = false)
    {
        $url = parent::getBaseUrl($raw);
        $url = str_replace('\\', '/', $url);
        return $url;
    }

    /**
     * Set route name
     *
     * @param string $route
     * @return $this
     */
    public function setRouteName($route)
    {
        $this->_route = $route;
        $module = $this->_routeConfig->getRouteFrontName($route);
        if ($module) {
            $this->setModuleName($module);
        }
        return $this;
    }

    /**
     * Retrieve request front name
     *
     * @return string|null
     */
    public function getFrontName()
    {
        $pathParts = explode('/', trim($this->getPathInfo(), '/'));
        return reset($pathParts);
    }

    /**
     * Retrieve route name
     *
     * @return string|null
     */
    public function getRouteName()
    {
        return $this->_route;
    }

    /**
     * Retrieve HTTP HOST
     *
     * @param bool $trimPort
     * @return string
     *
     * @todo getHttpHost should return only string (currently method return boolean value too)
     */
    public function getHttpHost($trimPort = true)
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return false;
        }
        if ($trimPort) {
            $host = explode(':', $_SERVER['HTTP_HOST']);
            return $host[0];
        }
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Set a member of the $_POST superglobal
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setPost($key, $value = null)
    {
        if (is_array($key)) {
            $_POST = $key;
        } else {
            $_POST[$key] = $value;
        }
        return $this;
    }

    /**
     * Specify module name where was found currently used controller
     *
     * @param string $module
     * @return $this
     */
    public function setControllerModule($module)
    {
        $this->_controllerModule = $module;
        return $this;
    }

    /**
     * Get module name of currently used controller
     *
     * @return  string
     */
    public function getControllerModule()
    {
        return $this->_controllerModule;
    }

    /**
     * Retrieve the module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_module;
    }

    /**
     * Retrieve the controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controller;
    }

    /**
     * Retrieve the action name
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->_action;
    }

    /**
     * Retrieve an alias
     *
     * Retrieve the actual key represented by the alias $name.
     *
     * @param string $name
     * @return string|null Returns null when no alias exists
     */
    public function getAlias($name)
    {
        $aliases = $this->getAliases();
        if (isset($aliases[$name])) {
            return $aliases[$name];
        }
        return null;
    }

    /**
     * Retrieve the list of all aliases
     *
     * @return array|string
     */
    public function getAliases()
    {
        if (isset($this->_routingInfo['aliases'])) {
            return $this->_routingInfo['aliases'];
        }
        return parent::getAliases();
    }

    /**
     * Get route name used in request (ignore rewrite)
     *
     * @return string
     */
    public function getRequestedRouteName()
    {
        if (isset($this->_routingInfo['requested_route'])) {
            return $this->_routingInfo['requested_route'];
        }
        if ($this->_requestedRouteName === null) {
            if ($this->_rewritedPathInfo !== null && isset($this->_rewritedPathInfo[0])) {
                $frontName = $this->_rewritedPathInfo[0];
                $this->_requestedRouteName = $this->_routeConfig->getRouteByFrontName($frontName);
            } else {
                // no rewritten path found, use default route name
                return $this->getRouteName();
            }
        }
        return $this->_requestedRouteName;
    }

    /**
     * Get controller name used in request (ignore rewrite)
     *
     * @return string
     */
    public function getRequestedControllerName()
    {
        if (isset($this->_routingInfo['requested_controller'])) {
            return $this->_routingInfo['requested_controller'];
        }
        if ($this->_rewritedPathInfo !== null && isset($this->_rewritedPathInfo[1])) {
            return $this->_rewritedPathInfo[1];
        }
        return $this->getControllerName();
    }

    /**
     * Get action name used in request (ignore rewrite)
     *
     * @return string
     */
    public function getRequestedActionName()
    {
        if (isset($this->_routingInfo['requested_action'])) {
            return $this->_routingInfo['requested_action'];
        }
        if ($this->_rewritedPathInfo !== null && isset($this->_rewritedPathInfo[2])) {
            return $this->_rewritedPathInfo[2];
        }
        return $this->getActionName();
    }

    /**
     * Set routing info data
     *
     * @param array $data
     * @return $this
     */
    public function setRoutingInfo($data)
    {
        if (is_array($data)) {
            $this->_routingInfo = $data;
        }
        return $this;
    }

    /**
     * Collect properties changed by _forward in protected storage
     * before _forward was called first time.
     *
     * @return $this
     */
    public function initForward()
    {
        if (empty($this->_beforeForwardInfo)) {
            $this->_beforeForwardInfo = [
                'params' => $this->getParams(),
                'action_name' => $this->getActionName(),
                'controller_name' => $this->getControllerName(),
                'module_name' => $this->getModuleName(),
                'route_name' => $this->getRouteName(),
            ];
        }

        return $this;
    }

    /**
     * Retrieve property's value which was before _forward call.
     * If property was not changed during _forward call null will be returned.
     * If passed name will be null whole state array will be returned.
     *
     * @param string $name
     * @return array|string|null
     */
    public function getBeforeForwardInfo($name = null)
    {
        if (is_null($name)) {
            return $this->_beforeForwardInfo;
        } elseif (isset($this->_beforeForwardInfo[$name])) {
            return $this->_beforeForwardInfo[$name];
        }

        return null;
    }

    /**
     * Specify/get _isStraight flag value
     *
     * @param bool $flag
     * @return bool
     */
    public function isStraight($flag = null)
    {
        if ($flag !== null) {
            $this->_isStraight = $flag;
        }
        return $this->_isStraight;
    }

    /**
     * Check is Request from AJAX
     *
     * @return boolean
     */
    public function isAjax()
    {
        if ($this->isXmlHttpRequest()) {
            return true;
        }
        if ($this->getParam('ajax') || $this->getParam('isAjax')) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve a member of the $_FILES super global
     *
     * If no $key is passed, returns the entire $_FILES array.
     *
     * @param string $key
     * @param array $default Default value to use if key not found
     * @return array
     */
    public function getFiles($key = null, $default = null)
    {
        if (null === $key) {
            return $_FILES;
        }

        return isset($_FILES[$key]) ? $_FILES[$key] : $default;
    }

    /**
     * Get website instance base url
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDistroBaseUrl()
    {
        if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['HTTP_HOST'])) {
            $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || isset(
                $_SERVER['SERVER_PORT']
            ) && $_SERVER['SERVER_PORT'] == '443';
            $scheme = ($secure ? 'https' : 'http') . '://';

            $hostArr = explode(':', $_SERVER['HTTP_HOST']);
            $host = $hostArr[0];
            $port = isset(
                $hostArr[1]
            ) && (!$secure && $hostArr[1] != 80 || $secure && $hostArr[1] != 443) ? ':' . $hostArr[1] : '';
            $path = $this->getBasePath();

            return $scheme . $host . $port . rtrim($path, '/') . '/';
        }
        return 'http://localhost/';
    }

    /**
     * Determines a base URL path from environment
     *
     * @param array $server
     * @return string
     */
    public static function getDistroBaseUrlPath($server)
    {
        $result = '';
        if (isset($server['SCRIPT_NAME'])) {
            $envPath = str_replace('\\', '/', dirname(str_replace('\\', '/', $server['SCRIPT_NAME'])));
            if ($envPath != '.' && $envPath != '/') {
                $result = $envPath;
            }
        }
        if (!preg_match('/\/$/', $result)) {
            $result .= '/';
        }
        return $result;
    }

    /**
     * Retrieve full action name
     *
     * @param string $delimiter
     * @return string
     */
    public function getFullActionName($delimiter = '_')
    {
        return $this->getRequestedRouteName() .
            $delimiter .
            $this->getRequestedControllerName() .
            $delimiter .
            $this->getRequestedActionName();
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * Retrieve a value from a cookie.
     *
     * @param string|null $name
     * @param string|null $default The default value to return if no value could be found for the given $name.
     * @return string|null
     */
    public function getCookie($name = null, $default = null)
    {
        return $this->cookieReader->getCookie($name, $default);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->immediateRequestSecure()) {
            return true;
        }
        /* TODO: Untangle Config dependence on Scope, so that this class can be instantiated even if app is not
        installed MAGETWO-31756 */
        // Check if a proxy sent a header indicating an initial secure request
        $config = $this->_objectManager->get('Magento\Framework\App\Config');
        $offLoaderHeader = trim(
            (string)$config->getValue(
                self::XML_PATH_OFFLOADER_HEADER,
                \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
            )
        );

        return $this->initialRequestSecure($offLoaderHeader);
    }

    /**
     * Checks if the immediate request is delivered over HTTPS
     *
     * @return bool
     */
    protected function immediateRequestSecure()
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
    }

    /**
     * In case there is a proxy server, checks if the initial request to the proxy was delivered over HTTPS
     *
     * @param string $offLoaderHeader
     * @return bool
     */
    protected function initialRequestSecure($offLoaderHeader)
    {
        return !empty($offLoaderHeader) &&
            (
                isset($_SERVER[$offLoaderHeader]) && $_SERVER[$offLoaderHeader] === 'https' ||
                isset($_SERVER['HTTP_' . $offLoaderHeader]) && $_SERVER['HTTP_' . $offLoaderHeader] === 'https'
            );
    }
}
