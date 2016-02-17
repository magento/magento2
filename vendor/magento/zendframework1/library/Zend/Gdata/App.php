<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Gdata_Feed
 */
#require_once 'Zend/Gdata/App/Feed.php';

/**
 * Zend_Gdata_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * Zend_Version
 */
#require_once 'Zend/Version.php';

/**
 * Zend_Gdata_App_MediaSource
 */
#require_once 'Zend/Gdata/App/MediaSource.php';

/**
 * Zend_Uri/Http
 */
#require_once 'Zend/Uri/Http.php';

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/**
 * Provides Atom Publishing Protocol (APP) functionality.  This class and all
 * other components of Zend_Gdata_App are designed to work independently from
 * other Zend_Gdata components in order to interact with generic APP services.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App
{

    /** Default major protocol version.
      *
      * @see _majorProtocolVersion
      */
    const DEFAULT_MAJOR_PROTOCOL_VERSION = 1;

    /** Default minor protocol version.
      *
      * @see _minorProtocolVersion
      */
    const DEFAULT_MINOR_PROTOCOL_VERSION = null;

    /**
     * Client object used to communicate
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient;

    /**
     * Client object used to communicate in static context
     *
     * @var Zend_Http_Client
     */
    protected static $_staticHttpClient = null;

    /**
     * Override HTTP PUT and DELETE request methods?
     *
     * @var boolean
     */
    protected static $_httpMethodOverride = false;

    /**
     * Enable gzipped responses?
     *
     * @var boolean
     */
    protected static $_gzipEnabled = false;

    /**
     * Use verbose exception messages.  In the case of HTTP errors,
     * use the body of the HTTP response in the exception message.
     *
     * @var boolean
     */
    protected static $_verboseExceptionMessages = true;

    /**
     * Default URI to which to POST.
     *
     * @var string
     */
    protected $_defaultPostUri = null;

    /**
     * Packages to search for classes when using magic __call method, in order.
     *
     * @var array
     */
    protected $_registeredPackages = array(
            'Zend_Gdata_App_Extension',
            'Zend_Gdata_App');

    /**
     * Maximum number of redirects to follow during HTTP operations
     *
     * @var int
     */
    protected static $_maxRedirects = 5;

    /**
      * Indicates the major protocol version that should be used.
      * At present, recognized values are either 1 or 2. However, any integer
      * value >= 1 is considered valid.
      *
      * Under most circumtances, this will be automatically set by
      * Zend_Gdata_App subclasses.
      *
      * @see setMajorProtocolVersion()
      * @see getMajorProtocolVersion()
      */
    protected $_majorProtocolVersion;

    /**
      * Indicates the minor protocol version that should be used. Can be set
      * to either an integer >= 0, or NULL if no minor version should be sent
      * to the server.
      *
      * At present, this field is not used by any Google services, but may be
      * used in the future.
      *
      * Under most circumtances, this will be automatically set by
      * Zend_Gdata_App subclasses.
      *
      * @see setMinorProtocolVersion()
      * @see getMinorProtocolVersion()
      */
    protected $_minorProtocolVersion;

    /**
     * Whether we want to use XML to object mapping when fetching data.
     *
     * @var boolean
     */
    protected $_useObjectMapping = true;

    /**
     * Create Gdata object
     *
     * @param Zend_Http_Client $client
     * @param string $applicationId
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->setHttpClient($client, $applicationId);
        // Set default protocol version. Subclasses should override this as
        // needed once a given service supports a new version.
        $this->setMajorProtocolVersion(self::DEFAULT_MAJOR_PROTOCOL_VERSION);
        $this->setMinorProtocolVersion(self::DEFAULT_MINOR_PROTOCOL_VERSION);
    }

    /**
     * Adds a Zend Framework package to the $_registeredPackages array.
     * This array is searched when using the magic __call method below
     * to instantiante new objects.
     *
     * @param string $name The name of the package (eg Zend_Gdata_App)
     * @return void
     */
    public function registerPackage($name)
    {
        array_unshift($this->_registeredPackages, $name);
    }

    /**
     * Retrieve feed as string or object
     *
     * @param string $uri The uri from which to retrieve the feed
     * @param string $className The class which is used as the return type
     * @return string|Zend_Gdata_App_Feed Returns string only if the object
     *                                    mapping has been disabled explicitly
     *                                    by passing false to the
     *                                    useObjectMapping() function.
     */
    public function getFeed($uri, $className='Zend_Gdata_App_Feed')
    {
        return $this->importUrl($uri, $className, null);
    }

    /**
     * Retrieve entry as string or object
     *
     * @param string $uri
     * @param string $className The class which is used as the return type
     * @return string|Zend_Gdata_App_Entry Returns string only if the object
     *                                     mapping has been disabled explicitly
     *                                     by passing false to the
     *                                     useObjectMapping() function.
     */
    public function getEntry($uri, $className='Zend_Gdata_App_Entry')
    {
        return $this->importUrl($uri, $className, null);
    }

    /**
     * Get the Zend_Http_Client object used for communication
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        return $this->_httpClient;
    }

    /**
     * Set the Zend_Http_Client object used for communication
     *
     * @param Zend_Http_Client $client The client to use for communication
     * @throws Zend_Gdata_App_HttpException
     * @return Zend_Gdata_App Provides a fluent interface
     */
    public function setHttpClient($client,
        $applicationId = 'MyCompany-MyApp-1.0')
    {
        if ($client === null) {
            $client = new Zend_Http_Client();
        }
        if (!$client instanceof Zend_Http_Client) {
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException(
                'Argument is not an instance of Zend_Http_Client.');
        }
        $userAgent = $applicationId . ' Zend_Framework_Gdata/' .
            Zend_Version::VERSION;
        $client->setHeaders('User-Agent', $userAgent);
        $client->setConfig(array(
            'strictredirects' => true
            )
        );
        $this->_httpClient = $client;
        self::setStaticHttpClient($client);
        return $this;
    }

    /**
     * Set the static HTTP client instance
     *
     * Sets the static HTTP client object to use for retrieving the feed.
     *
     * @param  Zend_Http_Client $httpClient
     * @return void
     */
    public static function setStaticHttpClient(Zend_Http_Client $httpClient)
    {
        self::$_staticHttpClient = $httpClient;
    }


    /**
     * Gets the HTTP client object. If none is set, a new Zend_Http_Client will be used.
     *
     * @return Zend_Http_Client
     */
    public static function getStaticHttpClient()
    {
        if (!self::$_staticHttpClient instanceof Zend_Http_Client) {
            $client = new Zend_Http_Client();
            $userAgent = 'Zend_Framework_Gdata/' . Zend_Version::VERSION;
            $client->setHeaders('User-Agent', $userAgent);
            $client->setConfig(array(
                'strictredirects' => true
                )
            );
            self::$_staticHttpClient = $client;
        }
        return self::$_staticHttpClient;
    }

    /**
     * Toggle using POST instead of PUT and DELETE HTTP methods
     *
     * Some feed implementations do not accept PUT and DELETE HTTP
     * methods, or they can't be used because of proxies or other
     * measures. This allows turning on using POST where PUT and
     * DELETE would normally be used; in addition, an
     * X-Method-Override header will be sent with a value of PUT or
     * DELETE as appropriate.
     *
     * @param  boolean $override Whether to override PUT and DELETE with POST.
     * @return void
     */
    public static function setHttpMethodOverride($override = true)
    {
        self::$_httpMethodOverride = $override;
    }

    /**
     * Get the HTTP override state
     *
     * @return boolean
     */
    public static function getHttpMethodOverride()
    {
        return self::$_httpMethodOverride;
    }

    /**
     * Toggle requesting gzip encoded responses
     *
     * @param  boolean $enabled Whether or not to enable gzipped responses
     * @return void
     */
    public static function setGzipEnabled($enabled = false)
    {
        if ($enabled && !function_exists('gzinflate')) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You cannot enable gzipped responses if the zlib module ' .
                    'is not enabled in your PHP installation.');

        }
        self::$_gzipEnabled = $enabled;
    }

    /**
     * Get the HTTP override state
     *
     * @return boolean
     */
    public static function getGzipEnabled()
    {
        return self::$_gzipEnabled;
    }

    /**
     * Get whether to use verbose exception messages
     *
     * In the case of HTTP errors,  use the body of the HTTP response
     * in the exception message.
     *
     * @return boolean
     */
    public static function getVerboseExceptionMessages()
    {
        return self::$_verboseExceptionMessages;
    }

    /**
     * Set whether to use verbose exception messages
     *
     * In the case of HTTP errors, use the body of the HTTP response
     * in the exception message.
     *
     * @param boolean $verbose Whether to use verbose exception messages
     */
    public static function setVerboseExceptionMessages($verbose)
    {
        self::$_verboseExceptionMessages = $verbose;
    }

    /**
     * Set the maximum number of redirects to follow during HTTP operations
     *
     * @param int $maxRedirects Maximum number of redirects to follow
     * @return void
     */
    public static function setMaxRedirects($maxRedirects)
    {
        self::$_maxRedirects = $maxRedirects;
    }

    /**
     * Get the maximum number of redirects to follow during HTTP operations
     *
     * @return int Maximum number of redirects to follow
     */
    public static function getMaxRedirects()
    {
        return self::$_maxRedirects;
    }

    /**
     * Set the major protocol version that should be used. Values < 1 will
     * cause a Zend_Gdata_App_InvalidArgumentException to be thrown.
     *
     * @see _majorProtocolVersion
     * @param int $value The major protocol version to use.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function setMajorProtocolVersion($value)
    {
        if (!($value >= 1)) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Major protocol version must be >= 1');
        }
        $this->_majorProtocolVersion = $value;
    }

    /**
     * Get the major protocol version that is in use.
     *
     * @see _majorProtocolVersion
     * @return int The major protocol version in use.
     */
    public function getMajorProtocolVersion()
    {
        return $this->_majorProtocolVersion;
    }

    /**
     * Set the minor protocol version that should be used. If set to NULL, no
     * minor protocol version will be sent to the server. Values < 0 will
     * cause a Zend_Gdata_App_InvalidArgumentException to be thrown.
     *
     * @see _minorProtocolVersion
     * @param (int|NULL) $value The minor protocol version to use.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function setMinorProtocolVersion($value)
    {
        if (!($value >= 0)) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Minor protocol version must be >= 0');
        }
        $this->_minorProtocolVersion = $value;
    }

    /**
     * Get the minor protocol version that is in use.
     *
     * @see _minorProtocolVersion
     * @return (int|NULL) The major protocol version in use, or NULL if no
     *         minor version is specified.
     */
    public function getMinorProtocolVersion()
    {
        return $this->_minorProtocolVersion;
    }

    /**
     * Provides pre-processing for HTTP requests to APP services.
     *
     * 1. Checks the $data element and, if it's an entry, extracts the XML,
     *    multipart data, edit link (PUT,DELETE), etc.
     * 2. If $data is a string, sets the default content-type  header as
     *    'application/atom+xml' if it's not already been set.
     * 3. Adds a x-http-method override header and changes the HTTP method
     *    to 'POST' if necessary as per getHttpMethodOverride()
     *
     * @param string $method The HTTP method for the request - 'GET', 'POST',
     *                       'PUT', 'DELETE'
     * @param string $url The URL to which this request is being performed,
     *                    or null if found in $data
     * @param array $headers An associative array of HTTP headers for this
     *                       request
     * @param mixed $data The Zend_Gdata_App_Entry or XML for the
     *                    body of the request
     * @param string $contentTypeOverride The override value for the
     *                                    content type of the request body
     * @return array An associative array containing the determined
     *               'method', 'url', 'data', 'headers', 'contentType'
     */
    public function prepareRequest($method,
                                   $url = null,
                                   $headers = array(),
                                   $data = null,
                                   $contentTypeOverride = null)
    {
        // As a convenience, if $headers is null, we'll convert it back to
        // an empty array.
        if ($headers === null) {
            $headers = array();
        }

        $rawData = null;
        $finalContentType = null;
        if ($url == null) {
            $url = $this->_defaultPostUri;
        }

        if (is_string($data)) {
            $rawData = $data;
            if ($contentTypeOverride === null) {
                $finalContentType = 'application/atom+xml';
            }
        } elseif ($data instanceof Zend_Gdata_App_MediaEntry) {
            $rawData = $data->encode();
            if ($data->getMediaSource() !== null) {
                $finalContentType = $rawData->getContentType();
                $headers['MIME-version'] = '1.0';
                $headers['Slug'] = $data->getMediaSource()->getSlug();
            } else {
                $finalContentType = 'application/atom+xml';
            }
            if ($method == 'PUT' || $method == 'DELETE') {
                $editLink = $data->getEditLink();
                if ($editLink != null && $url == null) {
                    $url = $editLink->getHref();
                }
            }
        } elseif ($data instanceof Zend_Gdata_App_Entry) {
            $rawData = $data->saveXML();
            $finalContentType = 'application/atom+xml';
            if ($method == 'PUT' || $method == 'DELETE') {
                $editLink = $data->getEditLink();
                if ($editLink != null) {
                    $url = $editLink->getHref();
                }
            }
        } elseif ($data instanceof Zend_Gdata_App_MediaSource) {
            $rawData = $data->encode();
            if ($data->getSlug() !== null) {
                $headers['Slug'] = $data->getSlug();
            }
            $finalContentType = $data->getContentType();
        }

        if ($method == 'DELETE') {
            $rawData = null;
        }

        // Set an If-Match header if:
        //   - This isn't a DELETE
        //   - If this isn't a GET, the Etag isn't weak
        //   - A similar header (If-Match/If-None-Match) hasn't already been
        //     set.
        if ($method != 'DELETE' && (
                !array_key_exists('If-Match', $headers) &&
                !array_key_exists('If-None-Match', $headers)
                ) ) {
            $allowWeak = $method == 'GET';
            if ($ifMatchHeader = $this->generateIfMatchHeaderData(
                    $data, $allowWeak)) {
                $headers['If-Match'] = $ifMatchHeader;
            }
        }

        if ($method != 'POST' && $method != 'GET' && Zend_Gdata_App::getHttpMethodOverride()) {
            $headers['x-http-method-override'] = $method;
            $method = 'POST';
        } else {
            $headers['x-http-method-override'] = null;
        }

        if ($contentTypeOverride != null) {
            $finalContentType = $contentTypeOverride;
        }

        return array('method' => $method, 'url' => $url,
            'data' => $rawData, 'headers' => $headers,
            'contentType' => $finalContentType);
    }

    /**
     * Performs a HTTP request using the specified method
     *
     * @param string $method The HTTP method for the request - 'GET', 'POST',
     *                       'PUT', 'DELETE'
     * @param string $url The URL to which this request is being performed
     * @param array $headers An associative array of HTTP headers
     *                       for this request
     * @param string $body The body of the HTTP request
     * @param string $contentType The value for the content type
     *                                of the request body
     * @param int $remainingRedirects Number of redirects to follow if request
     *                              s results in one
     * @return Zend_Http_Response The response object
     */
    public function performHttpRequest($method, $url, $headers = null,
        $body = null, $contentType = null, $remainingRedirects = null)
    {
        #require_once 'Zend/Http/Client/Exception.php';
        if ($remainingRedirects === null) {
            $remainingRedirects = self::getMaxRedirects();
        }
        if ($headers === null) {
            $headers = array();
        }
        // Append a Gdata version header if protocol v2 or higher is in use.
        // (Protocol v1 does not use this header.)
        $major = $this->getMajorProtocolVersion();
        $minor = $this->getMinorProtocolVersion();
        if ($major >= 2) {
            $headers['GData-Version'] = $major +
                    (($minor === null) ? '.' + $minor : '');
        }

        // check the overridden method
        if (($method == 'POST' || $method == 'PUT') && $body === null &&
            $headers['x-http-method-override'] != 'DELETE') {
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                        'You must specify the data to post as either a ' .
                        'string or a child of Zend_Gdata_App_Entry');
        }
        if ($url === null) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'You must specify an URI to which to post.');
        }
        $headers['Content-Type'] = $contentType;
        if (Zend_Gdata_App::getGzipEnabled()) {
            // some services require the word 'gzip' to be in the user-agent
            // header in addition to the accept-encoding header
            if (strpos($this->_httpClient->getHeader('User-Agent'),
                'gzip') === false) {
                $headers['User-Agent'] =
                    $this->_httpClient->getHeader('User-Agent') . ' (gzip)';
            }
            $headers['Accept-encoding'] = 'gzip, deflate';
        } else {
            $headers['Accept-encoding'] = 'identity';
        }

        // Make sure the HTTP client object is 'clean' before making a request
        // In addition to standard headers to reset via resetParameters(),
        // also reset the Slug and If-Match headers
        $this->_httpClient->resetParameters();
        $this->_httpClient->setHeaders(array('Slug', 'If-Match'));

        // Set the params for the new request to be performed
        $this->_httpClient->setHeaders($headers);
        #require_once 'Zend/Uri/Http.php';
        $uri = Zend_Uri_Http::fromString($url);
        preg_match("/^(.*?)(\?.*)?$/", $url, $matches);
        $this->_httpClient->setUri($matches[1]);
        $queryArray = $uri->getQueryAsArray();
        foreach ($queryArray as $name => $value) {
            $this->_httpClient->setParameterGet($name, $value);
        }


        $this->_httpClient->setConfig(array('maxredirects' => 0));

        // Set the proper adapter if we are handling a streaming upload
        $usingMimeStream = false;
        $oldHttpAdapter = null;

        if ($body instanceof Zend_Gdata_MediaMimeStream) {
            $usingMimeStream = true;
            $this->_httpClient->setRawDataStream($body, $contentType);
            $oldHttpAdapter = $this->_httpClient->getAdapter();

            if ($oldHttpAdapter instanceof Zend_Http_Client_Adapter_Proxy) {
                #require_once 'Zend/Gdata/HttpAdapterStreamingProxy.php';
                $newAdapter = new Zend_Gdata_HttpAdapterStreamingProxy();
            } else {
                #require_once 'Zend/Gdata/HttpAdapterStreamingSocket.php';
                $newAdapter = new Zend_Gdata_HttpAdapterStreamingSocket();
            }
            $this->_httpClient->setAdapter($newAdapter);
        } else {
            $this->_httpClient->setRawData($body, $contentType);
        }

        try {
            $response = $this->_httpClient->request($method);
            // reset adapter
            if ($usingMimeStream) {
                $this->_httpClient->setAdapter($oldHttpAdapter);
            }
        } catch (Zend_Http_Client_Exception $e) {
            // reset adapter
            if ($usingMimeStream) {
                $this->_httpClient->setAdapter($oldHttpAdapter);
            }
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }
        if ($response->isRedirect() && $response->getStatus() != '304') {
            if ($remainingRedirects > 0) {
                $newUrl = $response->getHeader('Location');
                $response = $this->performHttpRequest(
                    $method, $newUrl, $headers, $body,
                    $contentType, $remainingRedirects);
            } else {
                #require_once 'Zend/Gdata/App/HttpException.php';
                throw new Zend_Gdata_App_HttpException(
                        'Number of redirects exceeds maximum', null, $response);
            }
        }
        if (!$response->isSuccessful()) {
            #require_once 'Zend/Gdata/App/HttpException.php';
            $exceptionMessage = 'Expected response code 200, got ' .
                $response->getStatus();
            if (self::getVerboseExceptionMessages()) {
                $exceptionMessage .= "\n" . $response->getBody();
            }
            $exception = new Zend_Gdata_App_HttpException($exceptionMessage);
            $exception->setResponse($response);
            throw $exception;
        }
        return $response;
    }

    /**
     * Imports a feed located at $uri.
     *
     * @param  string $uri
     * @param  Zend_Http_Client $client The client used for communication
     * @param  string $className The class which is used as the return type
     * @param  bool $useObjectMapping Enable/disable the use of XML to object mapping.
     * @throws Zend_Gdata_App_Exception
     * @return string|Zend_Gdata_App_Feed Returns string only if the fourth
     *                                    parameter ($useObjectMapping) is set
     *                                    to false.
     */
    public static function import($uri, $client = null,
        $className='Zend_Gdata_App_Feed', $useObjectMapping = true)
    {
        $app = new Zend_Gdata_App($client);
        $requestData = $app->prepareRequest('GET', $uri);
        $response = $app->performHttpRequest(
            $requestData['method'], $requestData['url']);

        $feedContent = $response->getBody();
        if (false === $useObjectMapping) {
            return $feedContent;
        }
        $feed = self::importString($feedContent, $className);
        if ($client != null) {
            $feed->setHttpClient($client);
        }
        return $feed;
    }

    /**
     * Imports the specified URL (non-statically).
     *
     * @param  string $url The URL to import
     * @param  string $className The class which is used as the return type
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @throws Zend_Gdata_App_Exception
     * @return string|Zend_Gdata_App_Feed Returns string only if the object
     *                                    mapping has been disabled explicitly
     *                                    by passing false to the
     *                                    useObjectMapping() function.
     */
    public function importUrl($url, $className='Zend_Gdata_App_Feed',
        $extraHeaders = array())
    {
        $response = $this->get($url, $extraHeaders);

        $feedContent = $response->getBody();
        if (!$this->_useObjectMapping) {
            return $feedContent;
        }

        $protocolVersionStr = $response->getHeader('GData-Version');
        $majorProtocolVersion = null;
        $minorProtocolVersion = null;
        if ($protocolVersionStr !== null) {
            // Extract protocol major and minor version from header
            $delimiterPos = strpos($protocolVersionStr, '.');
            $length = strlen($protocolVersionStr);
            $major = substr($protocolVersionStr, 0, $delimiterPos);
            $minor = substr($protocolVersionStr, $delimiterPos + 1, $length);
            $majorProtocolVersion = $major;
            $minorProtocolVersion = $minor;
        }

        $feed = self::importString($feedContent, $className,
            $majorProtocolVersion, $minorProtocolVersion);
        if ($this->getHttpClient() != null) {
            $feed->setHttpClient($this->getHttpClient());
        }
        $etag = $response->getHeader('ETag');
        if ($etag !== null) {
            $feed->setEtag($etag);
        }
        return $feed;
    }


    /**
     * Imports a feed represented by $string.
     *
     * @param string $string
     * @param string $className The class which is used as the return type
     * @param integer $majorProcolVersion (optional) The major protocol version
     *        of the data model object that is to be created.
     * @param integer $minorProcolVersion (optional) The minor protocol version
     *        of the data model object that is to be created.
     * @throws Zend_Gdata_App_Exception
     * @return Zend_Gdata_App_Feed
     */
    public static function importString($string,
        $className='Zend_Gdata_App_Feed', $majorProtocolVersion = null,
        $minorProtocolVersion = null)
    {
        if (!class_exists($className, false)) {
          #require_once 'Zend/Loader.php';
          @Zend_Loader::loadClass($className);
        }

        // Load the feed as an XML DOMDocument object
        @ini_set('track_errors', 1);
        $doc = new DOMDocument();
        $doc = @Zend_Xml_Security::scan($string, $doc);
        @ini_restore('track_errors');

        if (!$doc) {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                "DOMDocument cannot parse XML: $php_errormsg");
        }

        $feed = new $className();
        $feed->setMajorProtocolVersion($majorProtocolVersion);
        $feed->setMinorProtocolVersion($minorProtocolVersion);
        $feed->transferFromXML($string);
        $feed->setHttpClient(self::getstaticHttpClient());
        return $feed;
    }


    /**
     * Imports a feed from a file located at $filename.
     *
     * @param  string $filename
     * @param  string $className The class which is used as the return type
     * @param  string $useIncludePath Whether the include_path should be searched
     * @throws Zend_Gdata_App_Exception
     * @return Zend_Gdata_App_Feed
     */
    public static function importFile($filename,
            $className='Zend_Gdata_App_Feed', $useIncludePath = false)
    {
        @ini_set('track_errors', 1);
        $feed = @file_get_contents($filename, $useIncludePath);
        @ini_restore('track_errors');
        if ($feed === false) {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                "File could not be loaded: $php_errormsg");
        }
        return self::importString($feed, $className);
    }

    /**
     * GET a URI using client object.
     *
     * @param string $uri GET URI
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @throws Zend_Gdata_App_HttpException
     * @return Zend_Http_Response
     */
    public function get($uri, $extraHeaders = array())
    {
        $requestData = $this->prepareRequest('GET', $uri, $extraHeaders);
        return $this->performHttpRequest(
            $requestData['method'], $requestData['url'],
            $requestData['headers']);
    }

    /**
     * POST data with client object
     *
     * @param mixed $data The Zend_Gdata_App_Entry or XML to post
     * @param string $uri POST URI
     * @param array $headers Additional HTTP headers to insert.
     * @param string $contentType Content-type of the data
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return Zend_Http_Response
     * @throws Zend_Gdata_App_Exception
     * @throws Zend_Gdata_App_HttpException
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function post($data, $uri = null, $remainingRedirects = null,
            $contentType = null, $extraHeaders = null)
    {
        $requestData = $this->prepareRequest(
            'POST', $uri, $extraHeaders, $data, $contentType);
        return $this->performHttpRequest(
                $requestData['method'], $requestData['url'],
                $requestData['headers'], $requestData['data'],
                $requestData['contentType']);
    }

    /**
     * PUT data with client object
     *
     * @param mixed $data The Zend_Gdata_App_Entry or XML to post
     * @param string $uri PUT URI
     * @param array $headers Additional HTTP headers to insert.
     * @param string $contentType Content-type of the data
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return Zend_Http_Response
     * @throws Zend_Gdata_App_Exception
     * @throws Zend_Gdata_App_HttpException
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function put($data, $uri = null, $remainingRedirects = null,
            $contentType = null, $extraHeaders = null)
    {
        $requestData = $this->prepareRequest(
            'PUT', $uri, $extraHeaders, $data, $contentType);
        return $this->performHttpRequest(
                $requestData['method'], $requestData['url'],
                $requestData['headers'], $requestData['data'],
                $requestData['contentType']);
    }

    /**
     * DELETE entry with client object
     *
     * @param mixed $data The Zend_Gdata_App_Entry or URL to delete
     * @return void
     * @throws Zend_Gdata_App_Exception
     * @throws Zend_Gdata_App_HttpException
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function delete($data, $remainingRedirects = null)
    {
        if (is_string($data)) {
            $requestData = $this->prepareRequest('DELETE', $data);
        } else {
            $headers = array();

            $requestData = $this->prepareRequest(
                'DELETE', null, $headers, $data);
        }
        return $this->performHttpRequest($requestData['method'],
                                         $requestData['url'],
                                         $requestData['headers'],
                                         '',
                                         $requestData['contentType'],
                                         $remainingRedirects);
    }

    /**
     * Inserts an entry to a given URI and returns the response as a
     * fully formed Entry.
     *
     * @param mixed  $data The Zend_Gdata_App_Entry or XML to post
     * @param string $uri POST URI
     * @param string $className The class of entry to be returned.
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return Zend_Gdata_App_Entry The entry returned by the service after
     *         insertion.
     */
    public function insertEntry($data, $uri, $className='Zend_Gdata_App_Entry',
        $extraHeaders = array())
    {
        if (!class_exists($className, false)) {
          #require_once 'Zend/Loader.php';
          @Zend_Loader::loadClass($className);
        }

        $response = $this->post($data, $uri, null, null, $extraHeaders);

        $returnEntry = new $className($response->getBody());
        $returnEntry->setHttpClient(self::getstaticHttpClient());

        $etag = $response->getHeader('ETag');
        if ($etag !== null) {
            $returnEntry->setEtag($etag);
        }

        return $returnEntry;
    }

    /**
     * Update an entry
     *
     * @param mixed $data Zend_Gdata_App_Entry or XML (w/ID and link rel='edit')
     * @param string|null The URI to send requests to, or null if $data
     *        contains the URI.
     * @param string|null The name of the class that should be deserialized
     *        from the server response. If null, then 'Zend_Gdata_App_Entry'
     *        will be used.
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return Zend_Gdata_App_Entry The entry returned from the server
     * @throws Zend_Gdata_App_Exception
     */
    public function updateEntry($data, $uri = null, $className = null,
        $extraHeaders = array())
    {
        if ($className === null && $data instanceof Zend_Gdata_App_Entry) {
            $className = get_class($data);
        } elseif ($className === null) {
            $className = 'Zend_Gdata_App_Entry';
        }

        if (!class_exists($className, false)) {
          #require_once 'Zend/Loader.php';
          @Zend_Loader::loadClass($className);
        }

        $response = $this->put($data, $uri, null, null, $extraHeaders);
        $returnEntry = new $className($response->getBody());
        $returnEntry->setHttpClient(self::getstaticHttpClient());

        $etag = $response->getHeader('ETag');
        if ($etag !== null) {
            $returnEntry->setEtag($etag);
        }

        return $returnEntry;
    }

    /**
     * Provides a magic factory method to instantiate new objects with
     * shorter syntax than would otherwise be required by the Zend Framework
     * naming conventions.  For instance, to construct a new
     * Zend_Gdata_Calendar_Extension_Color, a developer simply needs to do
     * $gCal->newColor().  For this magic constructor, packages are searched
     * in the same order as which they appear in the $_registeredPackages
     * array
     *
     * @param string $method The method name being called
     * @param array $args The arguments passed to the call
     * @throws Zend_Gdata_App_Exception
     */
    public function __call($method, $args)
    {
        if (preg_match('/^new(\w+)/', $method, $matches)) {
            $class = $matches[1];
            $foundClassName = null;
            foreach ($this->_registeredPackages as $name) {
                 try {
                     // Autoloading disabled on next line for compatibility
                     // with magic factories. See ZF-6660.
                     if (!class_exists($name . '_' . $class, false)) {
                        #require_once 'Zend/Loader.php';
                        @Zend_Loader::loadClass($name . '_' . $class);
                     }
                     $foundClassName = $name . '_' . $class;
                     break;
                 } catch (Zend_Exception $e) {
                     // package wasn't here- continue searching
                 } catch (ErrorException $e) {
                     // package wasn't here- continue searching
                     // @see ZF-7013 and ZF-11959
                 }
            }
            if ($foundClassName != null) {
                $reflectionObj = new ReflectionClass($foundClassName);
                $instance = $reflectionObj->newInstanceArgs($args);
                if ($instance instanceof Zend_Gdata_App_FeedEntryParent) {
                    $instance->setHttpClient($this->_httpClient);

                    // Propogate version data
                    $instance->setMajorProtocolVersion(
                            $this->_majorProtocolVersion);
                    $instance->setMinorProtocolVersion(
                            $this->_minorProtocolVersion);
                }
                return $instance;
            } else {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception(
                        "Unable to find '${class}' in registered packages");
            }
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception("No such method ${method}");
        }
    }

    /**
     * Retrieve all entries for a feed, iterating through pages as necessary.
     * Be aware that calling this function on a large dataset will take a
     * significant amount of time to complete. In some cases this may cause
     * execution to timeout without proper precautions in place.
     *
     * @param object $feed The feed to iterate through.
     * @return mixed A new feed of the same type as the one originally
     *          passed in, containing all relevent entries.
     */
    public function retrieveAllEntriesForFeed($feed) {
        $feedClass = get_class($feed);
        $reflectionObj = new ReflectionClass($feedClass);
        $result = $reflectionObj->newInstance();
        do {
            foreach ($feed as $entry) {
                $result->addEntry($entry);
            }

            $next = $feed->getLink('next');
            if ($next !== null) {
                $feed = $this->getFeed($next->href, $feedClass);
            } else {
                $feed = null;
            }
        }
        while ($feed != null);
        return $result;
    }

    /**
     * This method enables logging of requests by changing the
     * Zend_Http_Client_Adapter used for performing the requests.
     * NOTE: This will not work if you have customized the adapter
     * already to use a proxy server or other interface.
     *
     * @param string $logfile The logfile to use when logging the requests
     */
    public function enableRequestDebugLogging($logfile)
    {
        $this->_httpClient->setConfig(array(
            'adapter' => 'Zend_Gdata_App_LoggingHttpClientAdapterSocket',
            'logfile' => $logfile
            ));
    }

    /**
     * Retrieve next set of results based on a given feed.
     *
     * @param Zend_Gdata_App_Feed $feed The feed from which to
     *          retreive the next set of results.
     * @param string $className (optional) The class of feed to be returned.
     *          If null, the next feed (if found) will be the same class as
     *          the feed that was given as the first argument.
     * @return Zend_Gdata_App_Feed|null Returns a
     *          Zend_Gdata_App_Feed or null if no next set of results
     *          exists.
     */
    public function getNextFeed($feed, $className = null)
    {
        $nextLink = $feed->getNextLink();
        if (!$nextLink) {
            return null;
        }
        $nextLinkHref = $nextLink->getHref();

        if ($className === null) {
            $className = get_class($feed);
        }

        return $this->getFeed($nextLinkHref, $className);
    }

    /**
     * Retrieve previous set of results based on a given feed.
     *
     * @param Zend_Gdata_App_Feed $feed The feed from which to
     *          retreive the previous set of results.
     * @param string $className (optional) The class of feed to be returned.
     *          If null, the previous feed (if found) will be the same class as
     *          the feed that was given as the first argument.
     * @return Zend_Gdata_App_Feed|null Returns a
     *          Zend_Gdata_App_Feed or null if no previous set of results
     *          exists.
     */
    public function getPreviousFeed($feed, $className = null)
    {
        $previousLink = $feed->getPreviousLink();
        if (!$previousLink) {
            return null;
        }
        $previousLinkHref = $previousLink->getHref();

        if ($className === null) {
            $className = get_class($feed);
        }

        return $this->getFeed($previousLinkHref, $className);
    }

    /**
     * Returns the data for an If-Match header based on the current Etag
     * property. If Etags are not supported by the server or cannot be
     * extracted from the data, then null will be returned.
     *
     * @param boolean $allowWeak If false, then if a weak Etag is detected,
     *        then return null rather than the Etag.
     * @return string|null $data
     */
    public function generateIfMatchHeaderData($data, $allowWeek)
    {
        $result = '';
        // Set an If-Match header if an ETag has been set (version >= 2 only)
        if ($this->_majorProtocolVersion >= 2 &&
                $data instanceof Zend_Gdata_App_Entry) {
            $etag = $data->getEtag();
            if (($etag !== null) &&
                    ($allowWeek || substr($etag, 0, 2) != 'W/')) {
                $result = $data->getEtag();
            }
        }
        return $result;
    }

    /**
     * Determine whether service object is using XML to object mapping.
     *
     * @return boolean True if service object is using XML to object mapping,
     *                 false otherwise.
     */
    public function usingObjectMapping()
    {
        return $this->_useObjectMapping;
    }

    /**
     * Enable/disable the use of XML to object mapping.
     *
     * @param boolean $value Pass in true to use the XML to object mapping.
     *                       Pass in false or null to disable it.
     * @return void
     */
    public function useObjectMapping($value)
    {
        if ($value === True) {
            $this->_useObjectMapping = true;
        } else {
            $this->_useObjectMapping = false;
        }
    }

}
