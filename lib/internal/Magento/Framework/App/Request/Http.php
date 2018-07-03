<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Request;

use Magento\Framework\App\RequestContentInterface;
use Magento\Framework\App\RequestSafetyInterface;
use Magento\Framework\App\Route\ConfigInterface\Proxy as ConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Http request
 */
class Http extends Request implements RequestContentInterface, RequestSafetyInterface
{
    /**#@+
     * HTTP Ports
     */
    const DEFAULT_HTTP_PORT = 80;
    const DEFAULT_HTTPS_PORT = 443;
    /**#@-*/

    // Configuration path
    const XML_PATH_OFFLOADER_HEADER = 'web/secure/offloader_header';

    /**
     * @var string
     */
    protected $route;

    /**
     * PATH_INFO
     *
     * @var string
     */
    protected $pathInfo = '';

    /**
     * ORIGINAL_PATH_INFO
     *
     * @var string
     */
    protected $originalPathInfo = '';

    /**
     * @var array
     */
    protected $directFrontNames;

    /**
     * @var string
     */
    protected $controllerModule;

    /**
     * Request's original information before forward.
     *
     * @var array
     */
    protected $beforeForwardInfo = [];

    /**
     * @var ConfigInterface
     */
    protected $routeConfig;

    /**
     * @var PathInfoProcessorInterface
     */
    protected $pathInfoProcessor;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var bool|null
     */
    protected $isSafeMethod = null;

    /**
     * @var array
     */
    protected $safeRequestTypes = ['GET', 'HEAD', 'TRACE', 'OPTIONS'];

    /**
     * @var string
     */
    private $distroBaseUrl;

    /**
     * @param CookieReaderInterface $cookieReader
     * @param StringUtils $converter
     * @param ConfigInterface $routeConfig
     * @param PathInfoProcessorInterface $pathInfoProcessor
     * @param ObjectManagerInterface  $objectManager
     * @param \Zend\Uri\UriInterface|string|null $uri
     * @param array $directFrontNames
     */
    public function __construct(
        CookieReaderInterface $cookieReader,
        StringUtils $converter,
        ConfigInterface $routeConfig,
        PathInfoProcessorInterface $pathInfoProcessor,
        ObjectManagerInterface $objectManager,
        $uri = null,
        $directFrontNames = []
    ) {
        parent::__construct($cookieReader, $converter, $uri);
        $this->routeConfig = $routeConfig;
        $this->pathInfoProcessor = $pathInfoProcessor;
        $this->objectManager = $objectManager;
        $this->directFrontNames = $directFrontNames;
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
        if (empty($this->originalPathInfo)) {
            $this->setPathInfo();
        }
        return $this->originalPathInfo;
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
            if ('/' === $requestUri) {
                return $this;
            }

            $requestUri = $this->removeRepeatedSlashes($requestUri);
            $parsedRequestUri = explode('?', $requestUri, 2);
            $queryString = !isset($parsedRequestUri[1]) ? '' : '?' . $parsedRequestUri[1];
            $baseUrl = $this->getBaseUrl();
            $pathInfo = (string)substr($parsedRequestUri[0], (int)strlen($baseUrl));

            if ($this->isNoRouteUri($baseUrl, $pathInfo)) {
                $pathInfo = 'noroute';
            }
            $pathInfo = $this->pathInfoProcessor->process($this, $pathInfo);
            $this->originalPathInfo = (string)$pathInfo;
            $this->requestString = $pathInfo . $queryString;
        }
        $this->pathInfo = (string)$pathInfo;
        return $this;
    }

    /**
     * Remove repeated slashes from the start of the path.
     *
     * @param string $pathInfo
     * @return string
     */
    private function removeRepeatedSlashes($pathInfo)
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        if ($firstChar == '/') {
            $pathInfo = '/' . ltrim($pathInfo, '/');
        }

        return $pathInfo;
    }

    /**
     * Check is URI should be marked as no route, helps route to 404 URI like `index.phpadmin`.
     *
     * @param string $baseUrl
     * @param string $pathInfo
     * @return bool
     */
    private function isNoRouteUri($baseUrl, $pathInfo)
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        return $baseUrl !== '' && !in_array($firstChar, ['/', '']);
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
        return isset($this->directFrontNames[$code]);
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
     * Set route name
     *
     * @param string $route
     * @return $this
     */
    public function setRouteName($route)
    {
        $this->route = $route;
        $module = $this->routeConfig->getRouteFrontName($route);
        if ($module) {
            $this->setModuleName($module);
        }
        return $this;
    }

    /**
     * Retrieve route name
     *
     * @return string|null
     */
    public function getRouteName()
    {
        return $this->route;
    }

    /**
     * Specify module name where was found currently used controller
     *
     * @param string $module
     * @return $this
     */
    public function setControllerModule($module)
    {
        $this->controllerModule = $module;
        return $this;
    }

    /**
     * Get module name of currently used controller
     *
     * @return  string
     */
    public function getControllerModule()
    {
        return $this->controllerModule;
    }

    /**
     * Collect properties changed by _forward in protected storage
     * before _forward was called first time.
     *
     * @return $this
     */
    public function initForward()
    {
        if (empty($this->beforeForwardInfo)) {
            $this->beforeForwardInfo = [
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
        if ($name === null) {
            return $this->beforeForwardInfo;
        } elseif (isset($this->beforeForwardInfo[$name])) {
            return $this->beforeForwardInfo[$name];
        }
        return null;
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
     * Get website instance base url
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDistroBaseUrl()
    {
        if ($this->distroBaseUrl) {
            return $this->distroBaseUrl;
        }
        $headerHttpHost = $this->getServer('HTTP_HOST');
        $headerHttpHost = $this->converter->cleanString($headerHttpHost);
        $headerScriptName = $this->getServer('SCRIPT_NAME');

        if (isset($headerScriptName) && isset($headerHttpHost)) {
            if ($secure = $this->isSecure()) {
                $scheme = 'https://';
            } else {
                $scheme = 'http://';
            }

            $hostArr = explode(':', $headerHttpHost);
            $host = $hostArr[0];
            $port = isset($hostArr[1])
                && (!$secure && $hostArr[1] != 80 || $secure && $hostArr[1] != 443) ? ':' . $hostArr[1] : '';
            $path = $this->getBasePath();

            return $this->distroBaseUrl = $scheme . $host . $port . rtrim($path, '/') . '/';
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
     * Return url with no script name
     *
     * @param  string $url
     * @return string
     */
    public static function getUrlNoScript($url)
    {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return $url;
        }

        if (($pos = strripos($url, basename($_SERVER['SCRIPT_NAME']))) !== false) {
            $url = substr($url, 0, $pos);
        }

        return $url;
    }

    /**
     * Retrieve full action name
     *
     * @param string $delimiter
     * @return string
     */
    public function getFullActionName($delimiter = '_')
    {
        return $this->getRouteName() .
            $delimiter .
            $this->getControllerName() .
            $delimiter .
            $this->getActionName();
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isSafeMethod()
    {
        if ($this->isSafeMethod === null) {
            if (isset($_SERVER['REQUEST_METHOD']) && (in_array($_SERVER['REQUEST_METHOD'], $this->safeRequestTypes))) {
                $this->isSafeMethod = true;
            } else {
                $this->isSafeMethod = false;
            }
        }
        return $this->isSafeMethod;
    }
}
