<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Soap
 */

namespace Zend\Soap;

use Traversable;
use Zend\Server\Client as ServerClient;
use Zend\Stdlib\ArrayUtils;

/**
 * \Zend\Soap\Client
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Client
 */
class Client implements ServerClient
{
    /**
     * Encoding
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Array of SOAP type => PHP class pairings for handling return/incoming values
     * @var array
     */
    protected $classmap = null;

    /**
     * Registered fault exceptions
     * @var array
     */
    protected $faultExceptions = array();

    /**
     * SOAP version to use; SOAP_1_2 by default, to allow processing of headers
     * @var int
     */
    protected $soapVersion = SOAP_1_2;

    /** Set of other SoapClient options */
    protected $uri                 = null;
    protected $location            = null;
    protected $style               = null;
    protected $use                 = null;
    protected $login               = null;
    protected $password            = null;
    protected $proxy_host          = null;
    protected $proxy_port          = null;
    protected $proxy_login         = null;
    protected $proxy_password      = null;
    protected $local_cert          = null;
    protected $passphrase          = null;
    protected $compression         = null;
    protected $connection_timeout  = null;
    protected $stream_context      = null;
    protected $features            = null;
    protected $cache_wsdl          = null;
    protected $user_agent          = null;

    /**
     * WSDL used to access server
     * It also defines \Zend\Soap\Client working mode (WSDL vs non-WSDL)
     *
     * @var string
     */
    protected $wsdl = null;

    /**
     * SoapClient object
     *
     * @var \SoapClient
     */
    protected $soapClient;

    /**
     * Last invoked method
     *
     * @var string
     */
    protected $lastMethod = '';

    /**
     * SOAP request headers.
     *
     * Array of SoapHeader objects
     *
     * @var array
     */
    protected $soapInputHeaders = array();

    /**
     * Permanent SOAP request headers (shared between requests).
     *
     * Array of SoapHeader objects
     *
     * @var array
     */
    protected $permanentSoapInputHeaders = array();

    /**
     * Output SOAP headers.
     *
     * Array of SoapHeader objects
     *
     * @var array
     */
    protected $soapOutputHeaders = array();

    /**
     * Constructor
     *
     * @param  string $wsdl
     * @param  array|Traversable $options
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct($wsdl = null, $options = null)
    {
        if (!extension_loaded('soap')) {
            throw new Exception\ExtensionNotLoadedException('SOAP extension is not loaded.');
        }

        if ($wsdl !== null) {
            $this->setWSDL($wsdl);
        }
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Set wsdl
     *
     * @param string $wsdl
     * @return \Zend\Soap\Client
     */
    public function setWSDL($wsdl)
    {
        $this->wsdl = $wsdl;
        $this->soapClient = null;

        return $this;
    }

    /**
     * Get wsdl
     *
     * @return string
     */
    public function getWSDL()
    {
        return $this->wsdl;
    }

    /**
     * Set Options
     *
     * Allows setting options as an associative array of option => value pairs.
     *
     * @param  array|Traversable $options
     * @return \Zend\Soap\Client
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'classmap':
                case 'classMap':
                    $this->setClassmap($value);
                    break;
                case 'encoding':
                    $this->setEncoding($value);
                    break;
                case 'soapVersion':
                case 'soap_version':
                    $this->setSoapVersion($value);
                    break;
                case 'wsdl':
                    $this->setWSDL($value);
                    break;
                case 'uri':
                    $this->setUri($value);
                    break;
                case 'location':
                    $this->setLocation($value);
                    break;
                case 'style':
                    $this->setStyle($value);
                    break;
                case 'use':
                    $this->setEncodingMethod($value);
                    break;
                case 'login':
                    $this->setHttpLogin($value);
                    break;
                case 'password':
                    $this->setHttpPassword($value);
                    break;
                case 'proxy_host':
                    $this->setProxyHost($value);
                    break;
                case 'proxy_port':
                    $this->setProxyPort($value);
                    break;
                case 'proxy_login':
                    $this->setProxyLogin($value);
                    break;
                case 'proxy_password':
                    $this->setProxyPassword($value);
                    break;
                case 'local_cert':
                    $this->setHttpsCertificate($value);
                    break;
                case 'passphrase':
                    $this->setHttpsCertPassphrase($value);
                    break;
                case 'compression':
                    $this->setCompressionOptions($value);
                    break;
                case 'stream_context':
                    $this->setStreamContext($value);
                    break;
                case 'features':
                    $this->setSoapFeatures($value);
                    break;
                case 'cache_wsdl':
                    $this->setWSDLCache($value);
                    break;
                case 'useragent':
                case 'userAgent':
                case 'user_agent':
                    $this->setUserAgent($value);
                    break;

                // Not used now
                // case 'connection_timeout':
                //     $this->connection_timeout = $value;
                //    break;

                default:
                    throw new Exception\InvalidArgumentException('Unknown SOAP client option');
                    break;
            }
        }

        return $this;
    }

    /**
     * Return array of options suitable for using with SoapClient constructor
     *
     * @return array
     */
    public function getOptions()
    {
        $options = array();

        $options['classmap']       = $this->getClassmap();
        $options['encoding']       = $this->getEncoding();
        $options['soap_version']   = $this->getSoapVersion();
        $options['wsdl']           = $this->getWSDL();
        $options['uri']            = $this->getUri();
        $options['location']       = $this->getLocation();
        $options['style']          = $this->getStyle();
        $options['use']            = $this->getEncodingMethod();
        $options['login']          = $this->getHttpLogin();
        $options['password']       = $this->getHttpPassword();
        $options['proxy_host']     = $this->getProxyHost();
        $options['proxy_port']     = $this->getProxyPort();
        $options['proxy_login']    = $this->getProxyLogin();
        $options['proxy_password'] = $this->getProxyPassword();
        $options['local_cert']     = $this->getHttpsCertificate();
        $options['passphrase']     = $this->getHttpsCertPassphrase();
        $options['compression']    = $this->getCompressionOptions();
        //$options['connection_timeout'] = $this->connection_timeout;
        $options['stream_context'] = $this->getStreamContext();
        $options['cache_wsdl']     = $this->getWSDLCache();
        $options['features']       = $this->getSoapFeatures();
        $options['user_agent']     = $this->getUserAgent();

        foreach ($options as $key => $value) {
            /*
             * ugly hack as I don't know if checking for '=== null'
             * breaks some other option
             */
            if (in_array($key, array('user_agent', 'cache_wsdl', 'compression'))) {
                if ($value === null) {
                    unset($options[$key]);
                }
            } else {
                if ($value == null) {
                    unset($options[$key]);
                }
            }
        }

        return $options;
    }

    /**
     * Set SOAP version
     *
     * @param  int $version One of the SOAP_1_1 or SOAP_1_2 constants
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface with invalid soap version argument
     */
    public function setSoapVersion($version)
    {
        if (!in_array($version, array(SOAP_1_1, SOAP_1_2))) {
            throw new Exception\InvalidArgumentException('Invalid soap version specified. Use SOAP_1_1 or SOAP_1_2 constants.');
        }
        $this->soapVersion = $version;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Get SOAP version
     *
     * @return int
     */
    public function getSoapVersion()
    {
        return $this->soapVersion;
    }

    /**
     * Set classmap
     *
     * @param  array $classmap
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface for any invalid class in the class map
     */
    public function setClassmap(array $classmap)
    {
        foreach ($classmap as $class) {
            if (!class_exists($class)) {
                throw new Exception\InvalidArgumentException('Invalid class in class map');
            }
        }

        $this->classmap = $classmap;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve classmap
     *
     * @return mixed
     */
    public function getClassmap()
    {
        return $this->classmap;
    }

    /**
     * Set encoding
     *
     * @param  string $encoding
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface with invalid encoding argument
     */
    public function setEncoding($encoding)
    {
        if (!is_string($encoding)) {
            throw new Exception\InvalidArgumentException('Invalid encoding specified');
        }

        $this->encoding = $encoding;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Check for valid URN
     *
     * @param  string $urn
     * @return boolean
     * @throws \Zend\Soap\Exception\ExceptionInterface on invalid URN
     */
    public function validateUrn($urn)
    {
        $scheme = parse_url($urn, PHP_URL_SCHEME);
        if ($scheme === false || $scheme === null) {
            throw new Exception\InvalidArgumentException('Invalid URN');
        }

        return true;

    }

    /**
     * Set URI
     *
     * URI in Web Service the target namespace
     *
     * @param  string $uri
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface with invalid uri argument
     */
    public function setUri($uri)
    {
        $this->validateUrn($uri);
        $this->uri = $uri;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set Location
     *
     * URI in Web Service the target namespace
     *
     * @param  string $location
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface with invalid uri argument
     */
    public function setLocation($location)
    {
        $this->validateUrn($location);
        $this->location = $location;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve URI
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set request style
     *
     * @param  int $style One of the SOAP_RPC or SOAP_DOCUMENT constants
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface with invalid style argument
     */
    public function setStyle($style)
    {
        if (!in_array($style, array(SOAP_RPC, SOAP_DOCUMENT))) {
            throw new Exception\InvalidArgumentException('Invalid request style specified. Use SOAP_RPC or SOAP_DOCUMENT constants.');
        }

        $this->style = $style;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Get request style
     *
     * @return int
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set message encoding method
     *
     * @param  int $use One of the SOAP_ENCODED or SOAP_LITERAL constants
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface with invalid message encoding method argument
     */
    public function setEncodingMethod($use)
    {
        if (!in_array($use, array(SOAP_ENCODED, SOAP_LITERAL))) {
            throw new Exception\InvalidArgumentException('Invalid message encoding method. Use SOAP_ENCODED or SOAP_LITERAL constants.');
        }

        $this->use = $use;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Get message encoding method
     *
     * @return int
     */
    public function getEncodingMethod()
    {
        return $this->use;
    }

    /**
     * Set HTTP login
     *
     * @param  string $login
     * @return \Zend\Soap\Client
     */
    public function setHttpLogin($login)
    {
        $this->login = $login;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve HTTP Login
     *
     * @return string
     */
    public function getHttpLogin()
    {
        return $this->login;
    }

    /**
     * Set HTTP password
     *
     * @param  string $password
     * @return \Zend\Soap\Client
     */
    public function setHttpPassword($password)
    {
        $this->password = $password;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve HTTP Password
     *
     * @return string
     */
    public function getHttpPassword()
    {
        return $this->password;
    }

    /**
     * Set proxy host
     *
     * @param  string $proxyHost
     * @return \Zend\Soap\Client
     */
    public function setProxyHost($proxyHost)
    {
        $this->proxy_host = $proxyHost;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve proxy host
     *
     * @return string
     */
    public function getProxyHost()
    {
        return $this->proxy_host;
    }

    /**
     * Set proxy port
     *
     * @param  int $proxyPort
     * @return \Zend\Soap\Client
     */
    public function setProxyPort($proxyPort)
    {
        $this->proxy_port = (int)$proxyPort;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve proxy port
     *
     * @return int
     */
    public function getProxyPort()
    {
        return $this->proxy_port;
    }

    /**
     * Set proxy login
     *
     * @param  string $proxyLogin
     * @return \Zend\Soap\Client
     */
    public function setProxyLogin($proxyLogin)
    {
        $this->proxy_login = $proxyLogin;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Retrieve proxy login
     *
     * @return string
     */
    public function getProxyLogin()
    {
        return $this->proxy_login;
    }

    /**
     * Set proxy password
     *
     * @param  string $proxyPassword
     * @return \Zend\Soap\Client
     */
    public function setProxyPassword($proxyPassword)
    {
        $this->proxy_password = $proxyPassword;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Set HTTPS client certificate path
     *
     * @param  string $localCert local certificate path
     * @return \Zend\Soap\Client
     * @throws \Zend\Soap\Exception\ExceptionInterface with invalid local certificate path argument
     */
    public function setHttpsCertificate($localCert)
    {
        if (!is_readable($localCert)) {
            throw new Exception\InvalidArgumentException('Invalid HTTPS client certificate path.');
        }

        $this->local_cert = $localCert;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Get HTTPS client certificate path
     *
     * @return string
     */
    public function getHttpsCertificate()
    {
        return $this->local_cert;
    }

    /**
     * Set HTTPS client certificate passphrase
     *
     * @param  string $passphrase
     * @return \Zend\Soap\Client
     */
    public function setHttpsCertPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;

        $this->soapClient = null;

        return $this;
    }

    /**
     * Get HTTPS client certificate passphrase
     *
     * @return string
     */
    public function getHttpsCertPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * Set compression options
     *
     * @param  int|null $compressionOptions
     * @return \Zend\Soap\Client
     */
    public function setCompressionOptions($compressionOptions)
    {
        if ($compressionOptions === null) {
            $this->compression = null;
        } else {
            $this->compression = (int)$compressionOptions;
        }
        $this->soapClient = null;

        return $this;
    }

    /**
     * Get Compression options
     *
     * @return int
     */
    public function getCompressionOptions()
    {
        return $this->compression;
    }

    /**
     * Retrieve proxy password
     *
     * @return string
     */
    public function getProxyPassword()
    {
        return $this->proxy_password;
    }

    /**
     * Set Stream Context
     *
     * @param resource $context
     * @return \Zend\Soap\Client
     */
    public function setStreamContext($context)
    {
        if (!is_resource($context) || get_resource_type($context) !== "stream-context") {
            throw new Exception\InvalidArgumentException('Invalid stream context resource given.');
        }

        $this->stream_context = $context;
        return $this;
    }

    /**
     * Get Stream Context
     *
     * @return resource
     */
    public function getStreamContext()
    {
        return $this->stream_context;
    }

    /**
     * Set the SOAP Feature options.
     *
     * @param  string|int $feature
     * @return \Zend\Soap\Client
     */
    public function setSoapFeatures($feature)
    {
        $this->features = $feature;

        $this->soapClient = null;
        return $this;
    }

    /**
     * Return current SOAP Features options
     *
     * @return int
     */
    public function getSoapFeatures()
    {
        return $this->features;
    }

    /**
     * Set the SOAP WSDL Caching Options
     *
     * @param string|int|boolean|null $caching
     * @return \Zend\Soap\Client
     */
    public function setWSDLCache($caching)
    {
        if ($caching === null) {
            $this->cache_wsdl = null;
        } else {
            $this->cache_wsdl = (int)$caching;
        }
        return $this;
    }

    /**
     * Get current SOAP WSDL Caching option
     *
     * @return int
     */
    public function getWSDLCache()
    {
        return $this->cache_wsdl;
    }

    /**
     * Set the string to use in User-Agent header
     *
     * @param  string|null $userAgent
     * @return \Zend\Soap\Client
     */
    public function setUserAgent($userAgent)
    {
        if ($userAgent === null) {
            $this->user_agent = null;
        } else {
            $this->user_agent = (string) $userAgent;
        }
        return $this;
    }

    /**
     * Get current string to use in User-Agent header
     *
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     * Retrieve request XML
     *
     * @return string
     */
    public function getLastRequest()
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastRequest();
        }

        return '';
    }

    /**
     * Get response XML
     *
     * @return string
     */
    public function getLastResponse()
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastResponse();
        }

        return '';
    }

    /**
     * Retrieve request headers
     *
     * @return string
     */
    public function getLastRequestHeaders()
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastRequestHeaders();
        }

        return '';
    }

    /**
     * Retrieve response headers (as string)
     *
     * @return string
     */
    public function getLastResponseHeaders()
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastResponseHeaders();
        }

        return '';
    }

    /**
     * Retrieve last invoked method
     *
     * @return string
     */
    public function getLastMethod()
    {
        return $this->lastMethod;
    }

    /**
     * Do request proxy method.
     *
     * May be overridden in subclasses
     *
     * @internal
     * @param \Zend\Soap\Client\Common $client
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int    $version
     * @param int    $one_way
     * @return mixed
     */
    public function _doRequest(Client\Common $client, $request, $location, $action, $version, $one_way = null)
    {
        // Perform request as is
        if ($one_way === null) {
            return call_user_func(array($client,'SoapClient::__doRequest'), $request, $location, $action, $version);
        }
        return call_user_func(array($client, 'SoapClient::__doRequest'), $request, $location, $action, $version, $one_way);
    }

    /**
     * Initialize SOAP Client object
     *
     * @throws \Zend\Soap\Exception\ExceptionInterface
     */
    protected function _initSoapClientObject()
    {
        $wsdl = $this->getWSDL();
        $options = array_merge($this->getOptions(), array('trace' => true));

        if ($wsdl == null) {
            if (!isset($options['location'])) {
                throw new Exception\UnexpectedValueException('\'location\' parameter is required in non-WSDL mode.');
            }
            if (!isset($options['uri'])) {
                throw new Exception\UnexpectedValueException('\'uri\' parameter is required in non-WSDL mode.');
            }
        } else {
            if (isset($options['use'])) {
                throw new Exception\UnexpectedValueException('\'use\' parameter only works in non-WSDL mode.');
            }
            if (isset($options['style'])) {
                throw new Exception\UnexpectedValueException('\'style\' parameter only works in non-WSDL mode.');
            }
        }
        unset($options['wsdl']);

        $this->soapClient = new Client\Common(array($this, '_doRequest'), $wsdl, $options);
    }


    /**
     * Perform arguments pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param array $arguments
     * @return array
     */
    protected function _preProcessArguments($arguments)
    {
        // Do nothing
        return $arguments;
    }

    /**
     * Perform result pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param array $result
     * @return array
     */
    protected function _preProcessResult($result)
    {
        // Do nothing
        return $result;
    }

    /**
     * Add SOAP input header
     *
     * @param \SoapHeader $header
     * @param boolean $permanent
     * @return \Zend\Soap\Client
     */
    public function addSoapInputHeader(\SoapHeader $header, $permanent = false)
    {
        if ($permanent) {
            $this->permanentSoapInputHeaders[] = $header;
        } else {
            $this->soapInputHeaders[] = $header;
        }

        return $this;
    }

    /**
     * Reset SOAP input headers
     *
     * @return \Zend\Soap\Client
     */
    public function resetSoapInputHeaders()
    {
        $this->permanentSoapInputHeaders = array();
        $this->soapInputHeaders = array();

        return $this;
    }

    /**
     * Get last SOAP output headers
     *
     * @return array
     */
    public function getLastSoapOutputHeaderObjects()
    {
        return $this->soapOutputHeaders;
    }

    /**
     * Perform a SOAP call
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $soapClient = $this->getSoapClient();

        $this->lastMethod = $name;

        $soapHeaders = array_merge($this->permanentSoapInputHeaders, $this->soapInputHeaders);
        $result = $soapClient->__soapCall($name,
                                          $this->_preProcessArguments($arguments),
                                          null, /* Options are already set to the SOAP client object */
                                          (count($soapHeaders) > 0)? $soapHeaders : null,
                                          $this->soapOutputHeaders);

        // Reset non-permanent input headers
        $this->soapInputHeaders = array();

        return $this->_preProcessResult($result);
    }

    /**
     * Send an RPC request to the service for a specific method.
     *
     * @param  string $method Name of the method we want to call.
     * @param  array $params List of parameters for the method.
     * @return mixed Returned results.
     */
    public function call($method, $params = array())
    {
        return call_user_func_array(array($this, '__call'), $params);
    }

    /**
     * Return a list of available functions
     *
     * @return array
     * @throws \Zend\Soap\Exception\ExceptionInterface
     */
    public function getFunctions()
    {
        if ($this->getWSDL() == null) {
            throw new Exception\UnexpectedValueException(__METHOD__ . ' is available only in WSDL mode.');
        }

        $soapClient = $this->getSoapClient();
        return $soapClient->__getFunctions();
    }


    /**
     * Get used types.
     *
     * @return array
     */

    /**
     * Return a list of SOAP types
     *
     * @return array
     * @throws \Zend\Soap\Exception\ExceptionInterface
     */
    public function getTypes()
    {
        if ($this->getWSDL() == null) {
            throw new Exception\UnexpectedValueException(__METHOD__ . ' method is available only in WSDL mode.');
        }

        $soapClient = $this->getSoapClient();

        return $soapClient->__getTypes();
    }

    /**
     * @param \SoapClient $soapClient
     * @return \Zend\Soap\Client
     */
    public function setSoapClient(\SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
        return $this;
    }

    /**
     * @return \SoapClient
     */
    public function getSoapClient()
    {
        if ($this->soapClient == null) {
            $this->_initSoapClientObject();
        }
        return $this->soapClient;
    }

    /**
     * @param string $cookieName
     * @param string $cookieValue
     * @return \Zend\Soap\Client
     */
    public function setCookie($cookieName, $cookieValue=null)
    {
        $soapClient = $this->getSoapClient();
        $soapClient->__setCookie($cookieName, $cookieValue);
        return $this;
    }
}
