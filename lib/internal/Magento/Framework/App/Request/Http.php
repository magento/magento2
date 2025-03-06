<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Laminas\Stdlib\Parameters;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\RequestContentInterface;
use Magento\Framework\App\RequestSafetyInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Http request
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @api
 */
class Http extends Request implements
    RequestContentInterface,
    RequestSafetyInterface,
    HttpRequestInterface,
    ResetAfterRequestInterface
{
    /**#@+
     * HTTP Ports
     */
    public const DEFAULT_HTTP_PORT = 80;
    public const DEFAULT_HTTPS_PORT = 443;
    /**#@-*/

    // Configuration path
    public const XML_PATH_OFFLOADER_HEADER = 'web/secure/offloader_header';

    /**
     * @var string
     */
    protected $route;

    /**
     * @var string
     */
    protected $pathInfo = '';

    /**
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
     * @var PathInfo
     */
    private $pathInfoService;

    /**
     * @param CookieReaderInterface $cookieReader
     * @param StringUtils $converter
     * @param ConfigInterface $routeConfig
     * @param PathInfoProcessorInterface $pathInfoProcessor
     * @param ObjectManagerInterface $objectManager
     * @param \Laminas\Uri\UriInterface|string|null $uri
     * @param array $directFrontNames
     * @param PathInfo|null $pathInfoService
     */
    public function __construct(
        CookieReaderInterface $cookieReader,
        StringUtils $converter,
        ConfigInterface $routeConfig,
        PathInfoProcessorInterface $pathInfoProcessor,
        ObjectManagerInterface $objectManager,
        $uri = null,
        $directFrontNames = [],
        ?PathInfo $pathInfoService = null
    ) {
        parent::__construct($cookieReader, $converter, $uri);
        $this->routeConfig = $routeConfig;
        $this->pathInfoProcessor = $pathInfoProcessor;
        $this->objectManager = $objectManager;
        $this->directFrontNames = $directFrontNames;
        $this->pathInfoService = $pathInfoService ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            PathInfo::class
        );
    }

    /**
     * Return the ORIGINAL_PATH_INFO.
     * This value is calculated and processed from $_SERVER due to cross-platform differences.
     * instead of reading PATH_INFO
     *
     * @return string
     */
    public function getOriginalPathInfo()
    {
        if (empty($this->originalPathInfo)) {
            $originalPathInfoFromRequest = $this->pathInfoService->getPathInfo(
                $this->getRequestUri(),
                $this->getBaseUrl()
            );
            $this->originalPathInfo = (string)$this->pathInfoProcessor->process($this, $originalPathInfoFromRequest);
            $this->requestString = $this->originalPathInfo
                . $this->pathInfoService->getQueryString($this->getRequestUri());
        }
        return $this->originalPathInfo;
    }

    /**
     * Return the path info
     *
     * @return string
     */
    public function getPathInfo()
    {
        if (empty($this->pathInfo)) {
            $this->pathInfo = $this->getOriginalPathInfo();
        }
        return $this->pathInfo;
    }

    /**
     * Set the PATH_INFO string.
     *
     * Set the ORIGINAL_PATH_INFO string.
     *
     * @param string|null $pathInfo
     * @return $this
     */
    public function setPathInfo($pathInfo = null)
    {
        $this->pathInfo = (string)$pathInfo;
        return $this;
    }

    /**
     * Check if code declared as direct access frontend name.
     *
     * This means what this url can be used without store code.
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
        return empty($path) ? '/' : str_replace('\\', '/', $path);
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
     * @return string
     */
    public function getControllerModule()
    {
        return $this->controllerModule;
    }

    /**
     * Collect properties changed by _forward in protected storage before _forward was called first time.
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
        }

        return $this->beforeForwardInfo[$name] ?? null;
    }

    /**
     * Check is Request from AJAX
     *
     * @return boolean
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest()
            || $this->getParam('ajax')
            || $this->getParam('isAjax');
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

        if (isset($headerScriptName) && $headerHttpHost !== '') {
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
            if ($envPath !== '.' && $envPath !== '/') {
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

        if ($url !== null && ($pos = strripos($url, basename($_SERVER['SCRIPT_NAME']))) !== false) {
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
     * Sleep
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * @inheritdoc
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->setEnv(new Parameters($_ENV));
        $this->serverParams = new Parameters($_SERVER);
        $this->setQuery(new Parameters([]));
        $this->setPost(new Parameters([]));
        $this->setFiles(new Parameters([]));
        $this->module = null;
        $this->controller= null;
        $this->action = null;
        $this->pathInfo = '';
        $this->requestString = '';
        $this->params = [];
        $this->aliases = [];
        $this->dispatched = false;
        $this->forwarded = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->requestUri = null;
        $this->method = 'GET';
        $this->allowCustomMethods = true;
        $this->uri = null;
        $this->headers = null;
        $this->metadata = [];
        $this->content = '';
        $this->distroBaseUrl = null;
    }
}
