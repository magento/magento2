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
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Client.php 23076 2010-10-10 21:37:20Z padraic $
 */

/** Zend_Oauth */
#require_once 'Zend/Oauth.php';

/** Zend_Http_Client */
#require_once 'Zend/Http/Client.php';

/** Zend_Oauth_Http_Utility */
#require_once 'Zend/Oauth/Http/Utility.php';

/** Zend_Oauth_Config */
#require_once 'Zend/Oauth/Config.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Client extends Zend_Http_Client
{
    /**
     * Flag to indicate that the client has detected the server as supporting
     * OAuth 1.0a
     */
    public static $supportsRevisionA = false;

    /**
     * Holds the current OAuth Configuration set encapsulated in an instance
     * of Zend_Oauth_Config; it's not a Zend_Config instance since that level
     * of abstraction is unnecessary and doesn't let me escape the accessors
     * and mutators anyway!
     *
     * @var Zend_Oauth_Config
     */
    protected $_config = null;

    /**
     * True if this request is being made with data supplied by
     * a stream object instead of a raw encoded string.
     *
     * @var bool
     */
    protected $_streamingRequest = null;

    /**
     * Constructor; creates a new HTTP Client instance which itself is
     * just a typical Zend_Http_Client subclass with some OAuth icing to
     * assist in automating OAuth parameter generation, addition and
     * cryptographioc signing of requests.
     *
     * @param  array $oauthOptions
     * @param  string $uri
     * @param  array|Zend_Config $config
     * @return void
     */
    public function __construct($oauthOptions, $uri = null, $config = null)
    {
        if (!isset($config['rfc3986_strict'])) {
            $config['rfc3986_strict'] = true;
        }
        parent::__construct($uri, $config);
        $this->_config = new Zend_Oauth_Config;
        if ($oauthOptions !== null) {
            if ($oauthOptions instanceof Zend_Config) {
                $oauthOptions = $oauthOptions->toArray();
            }
            $this->_config->setOptions($oauthOptions);
        }
    }

    /**
     * Return the current connection adapter
     *
     * @return Zend_Http_Client_Adapter_Interface|string $adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

   /**
     * Load the connection adapter
     *
     * @param Zend_Http_Client_Adapter_Interface $adapter
     * @return void
     */
    public function setAdapter($adapter)
    {
        if ($adapter == null) {
            $this->adapter = $adapter;
        } else {
              parent::setAdapter($adapter);
        }
    }

    /**
     * Set the streamingRequest variable which controls whether we are
     * sending the raw (already encoded) POST data from a stream source.
     *
     * @param boolean $value The value to set.
     * @return void
     */
    public function setStreamingRequest($value)
    {
        $this->_streamingRequest = $value;
    }

    /**
     * Check whether the client is set to perform streaming requests.
     *
     * @return boolean True if yes, false otherwise.
     */
    public function getStreamingRequest()
    {
        if ($this->_streamingRequest) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepare the request body (for POST and PUT requests)
     *
     * @return string
     * @throws Zend_Http_Client_Exception
     */
    protected function _prepareBody()
    {
        if($this->_streamingRequest) {
            $this->setHeaders(self::CONTENT_LENGTH,
                $this->raw_post_data->getTotalSize());
            return $this->raw_post_data;
        }
        else {
            return parent::_prepareBody();
        }
    }

    /**
     * Clear all custom parameters we set.
     *
     * @return Zend_Http_Client
     */
    public function resetParameters($clearAll = false)
    {
        $this->_streamingRequest = false;
        return parent::resetParameters($clearAll);
    }

    /**
     * Set the raw (already encoded) POST data from a stream source.
     *
     * This is used to support POSTing from open file handles without
     * caching the entire body into memory. It is a wrapper around
     * Zend_Http_Client::setRawData().
     *
     * @param string $data The request data
     * @param string $enctype The encoding type
     * @return Zend_Http_Client
     */
    public function setRawDataStream($data, $enctype = null)
    {
        $this->_streamingRequest = true;
        return $this->setRawData($data, $enctype);
    }

    /**
     * Same as Zend_Http_Client::setMethod() except it also creates an
     * Oauth specific reference to the method type.
     * Might be defunct and removed in a later iteration.
     *
     * @param  string $method
     * @return Zend_Http_Client
     */
    public function setMethod($method = self::GET)
    {
        if ($method == self::GET) {
            $this->setRequestMethod(self::GET);
        } elseif($method == self::POST) {
            $this->setRequestMethod(self::POST);
        } elseif($method == self::PUT) {
            $this->setRequestMethod(self::PUT);
        }  elseif($method == self::DELETE) {
            $this->setRequestMethod(self::DELETE);
        }   elseif($method == self::HEAD) {
            $this->setRequestMethod(self::HEAD);
        }
        return parent::setMethod($method);
    }

    /**
     * Same as Zend_Http_Client::request() except just before the request is
     * executed, we automatically append any necessary OAuth parameters and
     * sign the request using the relevant signature method.
     *
     * @param  string $method
     * @return Zend_Http_Response
     */
    public function request($method = null)
    {
        if ($method !== null) {
            $this->setMethod($method);
        }
        $this->prepareOauth();
        return parent::request();
    }

    /**
     * Performs OAuth preparation on the request before sending.
     *
     * This primarily means taking a request, correctly encoding and signing
     * all parameters, and applying the correct OAuth scheme to the method
     * being used.
     *
     * @return void
     * @throws Zend_Oauth_Exception If POSTBODY scheme requested, but GET request method used; or if invalid request scheme provided
     */
    public function prepareOauth()
    {
        $requestScheme = $this->getRequestScheme();
        $requestMethod = $this->getRequestMethod();
        $query = null;
        if ($requestScheme == Zend_Oauth::REQUEST_SCHEME_HEADER) {
            $oauthHeaderValue = $this->getToken()->toHeader(
                $this->getUri(true),
                $this->_config,
                $this->_getSignableParametersAsQueryString()
            );
            $this->setHeaders('Authorization', $oauthHeaderValue);
        } elseif ($requestScheme == Zend_Oauth::REQUEST_SCHEME_POSTBODY) {
            if ($requestMethod == self::GET) {
                #require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception(
                    'The client is configured to'
                    . ' pass OAuth parameters through a POST body but request method'
                    . ' is set to GET'
                );
            }
            $raw = $this->getToken()->toQueryString(
                $this->getUri(true),
                $this->_config,
                $this->_getSignableParametersAsQueryString()
            );
            $this->setRawData($raw, 'application/x-www-form-urlencoded');
            $this->paramsPost = array();
        } elseif ($requestScheme == Zend_Oauth::REQUEST_SCHEME_QUERYSTRING) {
            $params = array();
            $query = $this->getUri()->getQuery();
            if ($query) {
                $queryParts = explode('&', $this->getUri()->getQuery());
                foreach ($queryParts as $queryPart) {
                    $kvTuple = explode('=', $queryPart);
                    $params[urldecode($kvTuple[0])] =
                        (array_key_exists(1, $kvTuple) ? urldecode($kvTuple[1]) : NULL);
                }
            }
            if (!empty($this->paramsPost)) {
                $params = array_merge($params, $this->paramsPost);
                $query  = $this->getToken()->toQueryString(
                    $this->getUri(true), $this->_config, $params
                );
            }
            $query = $this->getToken()->toQueryString(
                $this->getUri(true), $this->_config, $params
            );
            $this->getUri()->setQuery($query);
            $this->paramsGet = array();
        } else {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Invalid request scheme: ' . $requestScheme);
        }
    }

    /**
     * Collect all signable parameters into a single array across query string
     * and POST body. These are returned as a properly formatted single
     * query string.
     *
     * @return string
     */
    protected function _getSignableParametersAsQueryString()
    {
        $params = array();
            if (!empty($this->paramsGet)) {
                $params = array_merge($params, $this->paramsGet);
                $query  = $this->getToken()->toQueryString(
                    $this->getUri(true), $this->_config, $params
                );
            }
            if (!empty($this->paramsPost)) {
                $params = array_merge($params, $this->paramsPost);
                $query  = $this->getToken()->toQueryString(
                    $this->getUri(true), $this->_config, $params
                );
            }
            return $params;
    }

    /**
     * Simple Proxy to the current Zend_Oauth_Config method. It's that instance
     * which holds all configuration methods and values this object also presents
     * as it's API.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Oauth_Exception if method does not exist in config object
     */
    public function __call($method, array $args)
    {
        if (!method_exists($this->_config, $method)) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Method does not exist: ' . $method);
        }
        return call_user_func_array(array($this->_config,$method), $args);
    }
}
