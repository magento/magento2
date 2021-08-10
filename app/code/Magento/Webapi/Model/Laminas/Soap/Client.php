<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap;

use Laminas\Stdlib\ArrayUtils;
use Magento\Webapi\Api\Data\ClientInterface;
use Magento\Webapi\Model\Laminas\Soap\Client\Common;
use Magento\Webapi\Model\Laminas\Soap\Exception\ExtensionNotLoadedException;
use Magento\Webapi\Model\Laminas\Soap\Exception\InvalidArgumentException;
use Magento\Webapi\Model\Laminas\Soap\Exception\UnexpectedValueException;
use SoapClient;
use SoapHeader;
use Traversable;

class Client implements ClientInterface
{
    /**
     * Array of SOAP type => PHP class pairings for handling return/incoming values
     * @var array
     */
    protected $classmap = null;

    /**
     * Encoding
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Registered fault exceptions
     * @var array
     */
    protected $faultExceptions = [];

    /**
     * Last invoked method
     * @var string
     */
    protected $lastMethod = '';

    /**
     * Permanent SOAP request headers (shared between requests).
     * @var array
     */
    protected $permanentSoapInputHeaders = [];

    /**
     * SoapClient object
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * Array of SoapHeader objects
     * @var SoapHeader[]
     */
    protected $soapInputHeaders = [];

    /**
     * Array of SoapHeader objects
     * @var array
     */
    protected $soapOutputHeaders = [];

    /**
     * SOAP version to use; SOAP_1_2 by default, to allow processing of headers
     * @var int
     */
    protected $soapVersion = SOAP_1_2;

    /**
     * @var array
     */
    protected $typemap              = null;

    /**
     * WSDL used to access server
     * It also defines Client working mode (WSDL vs non-WSDL)
     * @var string
     */
    protected $wsdl = null;

    /**
     * Whether to send the "Connection: Keep-Alive" header (true) or "Connection: close" header (false)
     * Available since PHP 5.4.0
     * @var bool
     */
    protected $keepAlive;

    /**
     * One of SOAP_SSL_METHOD_TLS, SOAP_SSL_METHOD_SSLv2, SOAP_SSL_METHOD_SSLv3 or SOAP_SSL_METHOD_SSLv23
     * Available since PHP 5.5.0
     * @var int
     */
    protected $sslMethod;

    /**#@+
     * @var string
     */
    protected $connectionTimeout    = null;
    protected $localCert            = null;
    protected $location             = null;
    protected $login                = null;
    protected $passphrase           = null;
    protected $password             = null;
    protected $proxyHost            = null;
    protected $proxyLogin           = null;
    protected $proxyPassword        = null;
    protected $proxyPort            = null;
    protected $streamContext        = null;
    protected $style                = null;
    protected $uri                  = null;
    protected $use                  = null;
    protected $userAgent            = null;
    /**#@-*/

    /**#@+
     * @var int
     */
    protected $cacheWsdl            = null;
    protected $compression          = null;
    protected $features             = null;
    /**#@-*/

    /**
     * @param  string $wsdl
     * @param  array|Traversable $options
     * @throws ExtensionNotLoadedException
     */
    public function __construct($wsdl = null, $options = null)
    {
        if (! extension_loaded('soap')) {
            throw new ExtensionNotLoadedException('SOAP extension is not loaded.');
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
     * @param  string $wsdl
     * @return self
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
     * @return self
     * @throws InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'classmap':
                case 'class_map':
                    $this->setClassmap($value);
                    break;

                case 'encoding':
                    $this->setEncoding($value);
                    break;

                case 'soapversion':
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

                case 'proxyhost':
                case 'proxy_host':
                    $this->setProxyHost($value);
                    break;

                case 'proxyport':
                case 'proxy_port':
                    $this->setProxyPort($value);
                    break;

                case 'proxylogin':
                case 'proxy_login':
                    $this->setProxyLogin($value);
                    break;

                case 'proxypassword':
                case 'proxy_password':
                    $this->setProxyPassword($value);
                    break;

                case 'localcert':
                case 'local_cert':
                    $this->setHttpsCertificate($value);
                    break;

                case 'passphrase':
                    $this->setHttpsCertPassphrase($value);
                    break;

                case 'compression':
                    $this->setCompressionOptions($value);
                    break;

                case 'streamcontext':
                case 'stream_context':
                    $this->setStreamContext($value);
                    break;

                case 'features':
                    $this->setSoapFeatures($value);
                    break;

                case 'cachewsdl':
                case 'cache_wsdl':
                    $this->setWSDLCache($value);
                    break;

                case 'useragent':
                case 'user_agent':
                    $this->setUserAgent($value);
                    break;

                case 'typemap':
                case 'type_map':
                    $this->setTypemap($value);
                    break;

                case 'connectiontimeout':
                case 'connection_timeout':
                    $this->connectionTimeout = $value;
                    break;

                case 'keepalive':
                case 'keep_alive':
                    $this->setKeepAlive($value);
                    break;

                case 'sslmethod':
                case 'ssl_method':
                    $this->setSslMethod($value);
                    break;

                default:
                    throw new InvalidArgumentException('Unknown SOAP client option');
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
        $options = [];

        $options['classmap']       = $this->getClassmap();
        $options['typemap']        = $this->getTypemap();
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
        $options['connection_timeout'] = $this->connectionTimeout;
        $options['stream_context'] = $this->getStreamContext();
        $options['cache_wsdl']     = $this->getWSDLCache();
        $options['features']       = $this->getSoapFeatures();
        $options['user_agent']     = $this->getUserAgent();
        $options['keep_alive']     = $this->getKeepAlive();
        $options['ssl_method']     = $this->getSslMethod();

        foreach ($options as $key => $value) {
            /*
             * ugly hack as I don't know if checking for '=== null'
             * breaks some other option
             */
            if (in_array($key, ['user_agent', 'cache_wsdl', 'compression'])) {
                if ($value === null) {
                    unset($options[$key]);
                }
            } else {
                if ($value === null) {
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
     * @return self
     * @throws InvalidArgumentException with invalid soap version argument
     */
    public function setSoapVersion($version)
    {
        if (! in_array($version, [SOAP_1_1, SOAP_1_2])) {
            throw new InvalidArgumentException(
                'Invalid soap version specified. Use SOAP_1_1 or SOAP_1_2 constants.'
            );
        }

        $this->soapVersion = $version;
        $this->soapClient  = null;
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
     * @return self
     * @throws InvalidArgumentException for any invalid class in the class map
     */
    public function setClassmap(array $classmap)
    {
        foreach ($classmap as $class) {
            if (! class_exists($class)) {
                throw new InvalidArgumentException('Invalid class in class map: ' . $class);
            }
        }

        $this->classmap   = $classmap;
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
     * Set typemap with xml to php type mappings with appropriate validation.
     *
     * @param array $typeMap
     * @return self
     * @throws InvalidArgumentException
     */
    public function setTypemap(array $typeMap)
    {
        foreach ($typeMap as $type) {
            if (! is_callable($type['from_xml'])) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid from_xml callback for type: %s',
                    $type['type_name']
                ));
            }
            if (! is_callable($type['to_xml'])) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid to_xml callback for type: %s',
                    $type['type_name']
                ));
            }
        }

        $this->typemap = $typeMap;
        $this->soapClient = null;
        return $this;
    }

    /**
     * Retrieve typemap
     *
     * @return array
     */
    public function getTypemap()
    {
        return $this->typemap;
    }

    /**
     * Set encoding
     *
     * @param  string $encoding
     * @return self
     * @throws InvalidArgumentException with invalid encoding argument
     */
    public function setEncoding($encoding)
    {
        if (! is_string($encoding)) {
            throw new InvalidArgumentException('Invalid encoding specified');
        }

        $this->encoding   = $encoding;
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
     * @return bool
     * @throws InvalidArgumentException on invalid URN
     */
    public function validateUrn($urn)
    {
        $scheme = parse_url($urn, PHP_URL_SCHEME);
        if ($scheme === false || $scheme === null) {
            throw new InvalidArgumentException('Invalid URN');
        }
        return true;
    }

    /**
     * Set URI
     *
     * URI in Web Service the target namespace
     *
     * @param  string $uri
     * @return self
     * @throws InvalidArgumentException with invalid uri argument
     */
    public function setUri($uri)
    {
        $this->validateUrn($uri);
        $this->uri        = $uri;
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
     * @return self
     * @throws InvalidArgumentException with invalid uri argument
     */
    public function setLocation($location)
    {
        $this->validateUrn($location);
        $this->location   = $location;
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
     * @return self
     * @throws InvalidArgumentException with invalid style argument
     */
    public function setStyle($style)
    {
        if (! in_array($style, [SOAP_RPC, SOAP_DOCUMENT])) {
            throw new InvalidArgumentException(
                'Invalid request style specified. Use SOAP_RPC or SOAP_DOCUMENT constants.'
            );
        }

        $this->style      = $style;
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
     * @return self
     * @throws InvalidArgumentException with invalid message encoding method argument
     */
    public function setEncodingMethod($use)
    {
        if (! in_array($use, [SOAP_ENCODED, SOAP_LITERAL])) {
            throw new InvalidArgumentException(
                'Invalid message encoding method. Use SOAP_ENCODED or SOAP_LITERAL constants.'
            );
        }

        $this->use        = $use;
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
     * @return self
     */
    public function setHttpLogin($login)
    {
        $this->login      = $login;
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
     * @return self
     */
    public function setHttpPassword($password)
    {
        $this->password   = $password;
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
     * @return self
     */
    public function setProxyHost($proxyHost)
    {
        $this->proxyHost  = $proxyHost;
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
        return $this->proxyHost;
    }

    /**
     * Set proxy port
     *
     * @param  int $proxyPort
     * @return self
     */
    public function setProxyPort($proxyPort)
    {
        $this->proxyPort  = (int) $proxyPort;
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
        return $this->proxyPort;
    }

    /**
     * Set proxy login
     *
     * @param  string $proxyLogin
     * @return self
     */
    public function setProxyLogin($proxyLogin)
    {
        $this->proxyLogin = $proxyLogin;
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
        return $this->proxyLogin;
    }

    /**
     * Set proxy password
     *
     * @param  string $proxyPassword
     * @return self
     */
    public function setProxyPassword($proxyPassword)
    {
        $this->proxyPassword = $proxyPassword;
        $this->soapClient    = null;
        return $this;
    }

    /**
     * Set HTTPS client certificate path
     *
     * @param  string $localCert local certificate path
     * @return self
     * @throws InvalidArgumentException with invalid local certificate path argument
     */
    public function setHttpsCertificate($localCert)
    {
        if (!is_readable($localCert)) {
            throw new InvalidArgumentException('Invalid HTTPS client certificate path.');
        }

        $this->localCert  = $localCert;
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
        return $this->localCert;
    }

    /**
     * Set HTTPS client certificate passphrase
     *
     * @param  string $passphrase
     * @return self
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
     * @return self
     */
    public function setCompressionOptions($compressionOptions)
    {
        if ($compressionOptions === null) {
            $this->compression = null;
        } else {
            $this->compression = (int) $compressionOptions;
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
        return $this->proxyPassword;
    }

    /**
     * Set Stream Context
     *
     * @param  resource $context
     * @return self
     * @throws InvalidArgumentException
     */
    public function setStreamContext($context)
    {
        if (! is_resource($context) || get_resource_type($context) !== "stream-context") {
            throw new InvalidArgumentException('Invalid stream context resource given.');
        }

        $this->streamContext = $context;
        return $this;
    }

    /**
     * Get Stream Context
     *
     * @return resource
     */
    public function getStreamContext()
    {
        return $this->streamContext;
    }

    /**
     * Set the SOAP Feature options.
     *
     * @param  string|int $feature
     * @return self
     */
    public function setSoapFeatures($feature)
    {
        $this->features   = $feature;
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
     * @param  string|int|bool|null $caching
     * @return self
     */
    public function setWSDLCache($caching)
    {
        //@todo check WSDL_CACHE_* constants?
        if ($caching === null) {
            $this->cacheWsdl = null;
        } else {
            $this->cacheWsdl = (int) $caching;
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
        return $this->cacheWsdl;
    }

    /**
     * Set the string to use in User-Agent header
     *
     * @param  string|null $userAgent
     * @return self
     */
    public function setUserAgent($userAgent)
    {
        if ($userAgent === null) {
            $this->userAgent = null;
        } else {
            $this->userAgent = (string) $userAgent;
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
        return $this->userAgent;
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

    // @codingStandardsIgnoreStart
    /**
     * Do request proxy method.
     *
     * May be overridden in subclasses
     *
     * @param  Common $client
     * @param  string $request
     * @param  string $location
     * @param  string $action
     * @param  int    $version
     * @param  int    $oneWay
     * @return mixed
     */
    public function _doRequest(Common $client, $request, $location, $action, $version, $oneWay = null)
    {
        // Perform request as is
        if ($oneWay === null) {
            return call_user_func(
                [$client, 'SoapClient::__doRequest'],
                $request,
                $location,
                $action,
                $version
            );
        }
        return call_user_func(
            [$client, 'SoapClient::__doRequest'],
            $request,
            $location,
            $action,
            $version,
            $oneWay
        );
    }
    // @codingStandardsIgnoreEnd

    /**
     * Initialize SOAP Client object
     *
     * @throws UnexpectedValueException
     */
    protected function initSoapClientObject()
    {
        $wsdl = $this->getWSDL();
        $options = array_merge($this->getOptions(), ['trace' => true]);

        if ($wsdl === null) {
            if (! isset($options['location'])) {
                throw new UnexpectedValueException('"location" parameter is required in non-WSDL mode.');
            }
            if (! isset($options['uri'])) {
                throw new UnexpectedValueException('"uri" parameter is required in non-WSDL mode.');
            }
        } else {
            if (isset($options['use'])) {
                throw new UnexpectedValueException('"use" parameter only works in non-WSDL mode.');
            }
            if (isset($options['style'])) {
                throw new UnexpectedValueException('"style" parameter only works in non-WSDL mode.');
            }
        }
        unset($options['wsdl']);

        $this->soapClient = new Common([$this, '_doRequest'], $wsdl, $options);
    }

    // @codingStandardsIgnoreStart
    /**
     * Perform arguments pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param  array $arguments
     * @return array
     */
    protected function _preProcessArguments($arguments)
    {
        // Do nothing
        return $arguments;
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * Perform result pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param  array $result
     * @return array
     */
    protected function _preProcessResult($result)
    {
        // Do nothing
        return $result;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Add SOAP input header
     *
     * @param  SoapHeader $header
     * @param  bool $permanent
     * @return self
     */
    public function addSoapInputHeader(SoapHeader $header, $permanent = false)
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
     * @return self
     */
    public function resetSoapInputHeaders()
    {
        $this->permanentSoapInputHeaders = [];
        $this->soapInputHeaders          = [];
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
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (! is_array($arguments)) {
            $arguments = [$arguments];
        }
        $soapClient = $this->getSoapClient();

        $this->lastMethod = $name;

        $soapHeaders = array_merge($this->permanentSoapInputHeaders, $this->soapInputHeaders);
        $result = $soapClient->__soapCall(
            $name,
            $this->_preProcessArguments($arguments),
            [], /* Options are already set to the SOAP client object */
            (count($soapHeaders) > 0) ? $soapHeaders : [],
            $this->soapOutputHeaders
        );

        // Reset non-permanent input headers
        $this->soapInputHeaders = [];

        return $this->_preProcessResult($result);
    }

    /**
     * @inheritdoc
     */
    public function call($method, $params = [])
    {
        return call_user_func_array([$this, '__call'], [$method, $params]);
    }

    /**
     * Return a list of available functions
     *
     * @return array
     * @throws UnexpectedValueException
     */
    public function getFunctions()
    {
        if ($this->getWSDL() === null) {
            throw new UnexpectedValueException(sprintf(
                '%s method is available only in WSDL mode.',
                __METHOD__
            ));
        }

        $soapClient = $this->getSoapClient();
        return $soapClient->__getFunctions();
    }

    /**
     * Return a list of SOAP types
     *
     * @return array
     * @throws UnexpectedValueException
     */
    public function getTypes()
    {
        if ($this->getWSDL() === null) {
            throw new UnexpectedValueException(sprintf(
                '%s method is available only in WSDL mode.',
                __METHOD__
            ));
        }

        $soapClient = $this->getSoapClient();
        return $soapClient->__getTypes();
    }

    /**
     * Set SoapClient object
     *
     * @param  SoapClient $soapClient
     * @return self
     */
    public function setSoapClient(SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
        return $this;
    }

    /**
     * Get SoapClient object
     *
     * @return SoapClient
     */
    public function getSoapClient()
    {
        if ($this->soapClient === null) {
            $this->initSoapClientObject();
        }
        return $this->soapClient;
    }

    /**
     * Set cookie
     *
     * @param  string $cookieName
     * @param  string $cookieValue
     * @return self
     */
    public function setCookie($cookieName, $cookieValue = null)
    {
        $soapClient = $this->getSoapClient();
        $soapClient->__setCookie($cookieName, $cookieValue);
        return $this;
    }

    /**
     * @return boolean
     */
    public function getKeepAlive()
    {
        return $this->keepAlive;
    }

    /**
     * @param boolean $keepAlive
     * @return self
     */
    public function setKeepAlive($keepAlive)
    {
        $this->keepAlive = (bool) $keepAlive;
        return $this;
    }

    /**
     * @return int
     */
    public function getSslMethod()
    {
        return $this->sslMethod;
    }

    /**
     * @param int $sslMethod
     * @return self
     */
    public function setSslMethod($sslMethod)
    {
        $this->sslMethod = $sslMethod;
        return $this;
    }
}
