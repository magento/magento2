<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\Stdlib\StringUtils;
use Zend\Http\Header\HeaderInterface;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ParametersInterface;
use Zend\Uri\UriFactory;
use Zend\Uri\UriInterface;

/**
 * HTTP Request for current PHP environment.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Request extends \Zend\Http\PhpEnvironment\Request
{
    /**#@+
     * Protocols
     */
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';
    /**#@-*/

    // Configuration path for SSL Offload http header
    const XML_PATH_OFFLOADER_HEADER = 'web/secure/offloader_header';

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * PATH_INFO
     *
     * @var string
     */
    protected $pathInfo = '';

    /**
     * @var string
     */
    protected $requestString = '';

    /**
     * Request parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * Has the action been dispatched?
     *
     * @var boolean
     */
    protected $dispatched = false;

    /**
     * Flag for whether the request is forwarded or not
     *
     * @var bool
     */
    protected $forwarded;

    /**
     * @var CookieReaderInterface
     */
    protected $cookieReader;

    /**
     * @var StringUtils
     */
    protected $converter;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $appConfig;

    /**
     * Name of http header to check for ssl offloading default value is X-Forwarded-Proto
     *
     * @var string
     */
    protected $sslOffloadHeader;

    /**
     * @param CookieReaderInterface $cookieReader
     * @param StringUtils $converter
     * @param UriInterface|string|null $uri
     */
    public function __construct(
        CookieReaderInterface $cookieReader,
        StringUtils $converter,
        $uri = null
    ) {
        $this->cookieReader = $cookieReader;
        if (null !== $uri) {
            if (!$uri instanceof UriInterface) {
                $uri = UriFactory::factory($uri);
            }
            if ($uri->isValid()) {
                $path  = $uri->getPath();
                $query = $uri->getQuery();
                if (!empty($query)) {
                    $path .= '?' . $query;
                }
                $this->setRequestUri($path);
            } else {
                throw new \InvalidArgumentException('Invalid URI provided to constructor');
            }
        }
        $this->converter = $converter;
        parent::__construct();
    }

    /**
     * Retrieve the module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->module;
    }

    /**
     * Set the module name to use
     *
     * @param string $value
     * @return $this
     */
    public function setModuleName($value)
    {
        $this->module = $value;
        return $this;
    }

    /**
     * Retrieve the controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * Set the controller name to use
     *
     * @param string $value
     * @return $this
     */
    public function setControllerName($value)
    {
        $this->controller = $value;
        return $this;
    }

    /**
     * Retrieve the action name
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->action;
    }

    /**
     * Set the action name
     *
     * @param string $value
     * @return $this
     */
    public function setActionName($value)
    {
        $this->action = $value;
        return $this;
    }

    /**
     * Returns everything between the BaseUrl and QueryString.
     * This value is calculated instead of reading PATH_INFO
     * directly from $_SERVER due to cross-platform differences.
     *
     * @return string
     */
    public function getPathInfo()
    {
        if (empty($this->pathInfo)) {
            $this->setPathInfo();
        }
        return $this->pathInfo;
    }

    /**
     * Set the PATH_INFO string
     *
     * @param string|null $pathInfo
     * @return $this
     */
    public function setPathInfo($pathInfo = null)
    {
        if ($pathInfo === null) {
            $requestUri = $this->getRequestUri();
            if ('/' == $requestUri) {
                return $this;
            }

            // Remove the query string from REQUEST_URI
            $pos = strpos($requestUri, '?');
            if ($pos) {
                $requestUri = substr($requestUri, 0, $pos);
            }

            $baseUrl = $this->getBaseUrl();
            $pathInfo = substr($requestUri, strlen($baseUrl));
            if (!empty($baseUrl) && '/' === $pathInfo) {
                $pathInfo = '';
            } elseif (null === $baseUrl) {
                $pathInfo = $requestUri;
            }
            $this->requestString = $pathInfo . ($pos !== false ? substr($requestUri, $pos) : '');
        }
        $this->pathInfo = (string)$pathInfo;
        return $this;
    }

    /**
     * Get request string
     *
     * @return string
     */
    public function getRequestString()
    {
        return $this->requestString;
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
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }
        return null;
    }

    /**
     * Set a key alias
     *
     * Set an alias used for key lookups. $name specifies the alias, $target
     * specifies the actual key to use.
     *
     * @param string $name
     * @param string $target
     * @return $this
     */
    public function setAlias($name, $target)
    {
        $this->aliases[$name] = $target;
        return $this;
    }

    /**
     * Get an action parameter
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        $key = (string) $key;
        $keyName = (null !== ($alias = $this->getAlias($key))) ? $alias : $key;
        if (isset($this->params[$keyName])) {
            return $this->params[$keyName];
        } elseif (isset($this->queryParams[$keyName])) {
            return $this->queryParams[$keyName];
        } elseif (isset($this->postParams[$keyName])) {
            return $this->postParams[$keyName];
        }
        return $default;
    }

    /**
     * Set an action parameter
     *
     * A $value of null will unset the $key if it exists
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $key = (string) $key;
        $keyName = (null !== ($alias = $this->getAlias($key))) ? $alias : $key;
        if ((null === $value) && isset($this->params[$keyName])) {
            unset($this->params[$keyName]);
        } elseif (null !== $value) {
            $this->params[$keyName] = $value;
        }
        return $this;
    }

    /**
     * Get all action parameters
     *
     * @return array
     */
    public function getParams()
    {
        $params = $this->params;
        if ($value = (array)$this->getQuery()) {
            $params += $value;
        }
        if ($value = (array)$this->getPost()) {
            $params += $value;
        }
        return $params;
    }

    /**
     * Set action parameters en masse; does not overwrite
     *
     * Null values will unset the associated key.
     *
     * @param array $array
     * @return $this
     */
    public function setParams(array $array)
    {
        foreach ($array as $key => $value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    /**
     * Unset all user parameters
     *
     * @return $this
     */
    public function clearParams()
    {
        $this->params = [];
        return $this;
    }

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecure() ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }

    /**
     * Set flag indicating whether or not request has been dispatched
     *
     * @param boolean $flag
     * @return $this
     */
    public function setDispatched($flag = true)
    {
        $this->dispatched = $flag ? true : false;
        return $this;
    }

    /**
     * Determine if the request has been dispatched
     *
     * @return boolean
     */
    public function isDispatched()
    {
        return $this->dispatched;
    }

    /**
     * Is https secure request
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->immediateRequestSecure()) {
            return true;
        }

        return $this->initialRequestSecure($this->getSslOffloadHeader());
    }

    /**
     * Get value of SSL offload http header from configuration - defaults to X-Forwarded-Proto
     *
     * @return string
     */
    private function getSslOffloadHeader()
    {
        // Lets read from db only one time okay.
        if ($this->sslOffloadHeader === null) {
            // @todo: Untangle Config dependence on Scope, so that this class can be instantiated even if app is not
            // installed MAGETWO-31756
            // Check if a proxy sent a header indicating an initial secure request
            $this->sslOffloadHeader = trim(
                (string)$this->getAppConfig()->getValue(
                    self::XML_PATH_OFFLOADER_HEADER,
                    \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                )
            );
        }

        return $this->sslOffloadHeader;
    }

    /**
     * Create an instance of Magento\Framework\App\Config
     *
     * @return \Magento\Framework\App\Config
     * @deprecated 100.1.0
     */
    private function getAppConfig()
    {
        if ($this->appConfig == null) {
            $this->appConfig =
                \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\Config::class);
        }
        return $this->appConfig;
    }

    /**
     * Checks if the immediate request is delivered over HTTPS
     *
     * @return bool
     */
    protected function immediateRequestSecure()
    {
        $https = $this->getServer('HTTPS');
        $headerServerPort = $this->getServer('SERVER_PORT');
        return (!empty($https) && $https != 'off') || $headerServerPort == 443;
    }

    /**
     * In case there is a proxy server, checks if the initial request to the proxy was delivered over HTTPS
     *
     * @param string $offLoaderHeader
     * @return bool
     */
    protected function initialRequestSecure($offLoaderHeader)
    {
        // Transform http header to $_SERVER format ie X-Forwarded-Proto becomes $_SERVER['HTTP_X_FORWARDED_PROTO']
        $offLoaderHeader = str_replace('-', '_', strtoupper($offLoaderHeader));
        // Some webservers do not append HTTP_
        $header = $this->getServer($offLoaderHeader);
        // Apache appends HTTP_
        $httpHeader = $this->getServer('HTTP_' . $offLoaderHeader);
        return !empty($offLoaderHeader) && ($header === 'https' || $httpHeader === 'https');
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
     * Retrieve SERVER parameters
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|ParametersInterface
     */
    public function getServerValue($name = null, $default = null)
    {
        $server = $this->getServer($name, $default);
        if ($server instanceof ParametersInterface) {
            return $server->toArray();
        }
        return $server;
    }

    /**
     * Retrieve GET parameters
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|ParametersInterface
     */
    public function getQueryValue($name = null, $default = null)
    {
        $query = $this->getQuery($name, $default);
        if ($query instanceof ParametersInterface) {
            return $query->toArray();
        }
        return $query;
    }

    /**
     * Set GET parameters
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setQueryValue($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->getQuery()->set($key, $value);
            }
            return $this;
        }
        $this->getQuery()->set($name, $value);
        return $this;
    }

    /**
     * Retrieve POST parameters
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|ParametersInterface
     */
    public function getPostValue($name = null, $default = null)
    {
        $post = $this->getPost($name, $default);
        if ($post instanceof ParametersInterface) {
            return $post->toArray();
        }
        return $post;
    }

    /**
     * Set POST parameters
     *
     * @param string|array $name
     * @param mixed $value
     * @return $this
     */
    public function setPostValue($name, $value = null)
    {
        if (is_array($name)) {
            $this->setPost(new Parameters($name));
            return $this;
        }
        $this->getPost()->set($name, $value);
        return $this;
    }

    /**
     * Access values contained in the superglobals as public members
     *
     * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
     *
     * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        switch (true) {
            case isset($this->params[$key]):
                return $this->params[$key];

            case isset($this->queryParams[$key]):
                return $this->queryParams[$key];

            case isset($this->postParams[$key]):
                return $this->postParams[$key];

            case isset($_COOKIE[$key]):
                return $_COOKIE[$key];

            case ($key == 'REQUEST_URI'):
                return $this->getRequestUri();

            case ($key == 'PATH_INFO'):
                return $this->getPathInfo();

            case isset($this->serverParams[$key]):
                return $this->serverParams[$key];

            case isset($this->envParams[$key]):
                return $this->envParams[$key];

            default:
                return null;
        }
    }

    /**
     * Alias to __get
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * Check to see if a property is set
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        switch (true) {
            case isset($this->params[$key]):
                return true;

            case isset($this->queryParams[$key]):
                return true;

            case isset($this->postParams[$key]):
                return true;

            case isset($_COOKIE[$key]):
                return true;

            case isset($this->serverParams[$key]):
                return true;

            case isset($this->envParams[$key]):
                return true;

            default:
                return false;
        }
    }

    /**
     * Alias to __isset()
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->__isset($key);
    }

    /**
     * Get all headers of a certain name/type.
     *
     * @param string $name Header name to retrieve.
     * @param mixed|null $default Default value to use when the requested header is missing.
     * @return bool|string
     */
    public function getHeader($name, $default = false)
    {
        $header = parent::getHeader($name, $default);
        if ($header instanceof HeaderInterface) {
            return $header->getFieldValue();
        }
        return false;
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
        $httpHost = $this->getServer('HTTP_HOST');
        $httpHost = $this->converter->cleanString($httpHost);
        if (empty($httpHost)) {
            return false;
        }
        if ($trimPort) {
            $host = explode(':', $httpHost);
            return $host[0];
        }
        return $httpHost;
    }

    /**
     * Get the client's IP addres
     *
     * @param  boolean $checkProxy
     * @return string
     */
    public function getClientIp($checkProxy = true)
    {
        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } elseif ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }
        return $ip;
    }

    /**
     * Retrieve only user params
     *
     * @return array
     */
    public function getUserParams()
    {
        return $this->params;
    }

    /**
     * Retrieve a single user param
     *
     * @param string $key
     * @param string $default Default value to use if key not found
     * @return mixed
     */
    public function getUserParam($key, $default = null)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return $default;
    }

    /**
     * Set the REQUEST_URI
     *
     * @param string $requestUri
     * @return $this
     */
    public function setRequestUri($requestUri = null)
    {
        if ($requestUri === null) {
            $requestUri = $this->detectRequestUri();
        } elseif (!is_string($requestUri)) {
            return $this;
        } else {
            if (false !== ($pos = strpos($requestUri, '?'))) {
                $query = substr($requestUri, $pos + 1);
                parse_str($query, $vars);
                $this->setQueryValue($vars);
            }
        }
        $this->requestUri = $requestUri;
        return $this;
    }

    /**
     * Get base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $url = urldecode(parent::getBaseUrl());
        $url = str_replace('\\', '/', $url);
        return $url;
    }

    /**
     * Get flag value for whether the request is forwarded or not.
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isForwarded()
    {
        return $this->forwarded;
    }

    /**
     * Set flag value for whether the request is forwarded or not.
     *
     * @param bool $forwarded
     * @return $this
     * @codeCoverageIgnore
     */
    public function setForwarded($forwarded)
    {
        $this->forwarded = $forwarded;
        return $this;
    }
}
