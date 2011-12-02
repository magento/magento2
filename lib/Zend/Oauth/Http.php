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
 * @version    $Id: Http.php 22662 2010-07-24 17:37:36Z mabe $
 */

/** Zend_Oauth_Http_Utility */
#require_once 'Zend/Oauth/Http/Utility.php';

/** Zend_Uri_Http */
#require_once 'Zend/Uri/Http.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Http
{
    /**
     * Array of all custom service parameters to be sent in the HTTP request
     * in addition to the usual OAuth parameters.
     *
     * @var array
     */
    protected $_parameters = array();

    /**
     * Reference to the Zend_Oauth_Consumer instance in use.
     *
     * @var string
     */
    protected $_consumer = null;

    /**
     * OAuth specifies three request methods, this holds the current preferred
     * one which by default uses the Authorization Header approach for passing
     * OAuth parameters, and a POST body for non-OAuth custom parameters.
     *
     * @var string
     */
    protected $_preferredRequestScheme = null;

    /**
     * Request Method for the HTTP Request.
     *
     * @var string
     */
    protected $_preferredRequestMethod = Zend_Oauth::POST;

    /**
     * Instance of the general Zend_Oauth_Http_Utility class.
     *
     * @var Zend_Oauth_Http_Utility
     */
    protected $_httpUtility = null;

    /**
     * Constructor
     *
     * @param  Zend_Oauth_Consumer $consumer
     * @param  null|array $parameters
     * @param  null|Zend_Oauth_Http_Utility $utility
     * @return void
     */
    public function __construct(
        Zend_Oauth_Consumer $consumer, 
        array $parameters = null,
        Zend_Oauth_Http_Utility $utility = null
    ) {
        $this->_consumer = $consumer;
        $this->_preferredRequestScheme = $this->_consumer->getRequestScheme();
        if ($parameters !== null) {
            $this->setParameters($parameters);
        }
        if ($utility !== null) {
            $this->_httpUtility = $utility;
        } else {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }

    /**
     * Set a preferred HTTP request method.
     *
     * @param  string $method
     * @return Zend_Oauth_Http
     */
    public function setMethod($method)
    {
        if (!in_array($method, array(Zend_Oauth::POST, Zend_Oauth::GET))) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('invalid HTTP method: ' . $method);
        }
        $this->_preferredRequestMethod = $method;
        return $this;
    }

    /**
     * Preferred HTTP request method accessor.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_preferredRequestMethod;
    }

    /**
     * Mutator to set an array of custom parameters for the HTTP request.
     *
     * @param  array $customServiceParameters
     * @return Zend_Oauth_Http
     */
    public function setParameters(array $customServiceParameters)
    {
        $this->_parameters = $customServiceParameters;
        return $this;
    }

    /**
     * Accessor for an array of custom parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Return the Consumer instance in use.
     *
     * @return Zend_Oauth_Consumer
     */
    public function getConsumer()
    {
        return $this->_consumer;
    }

    /**
     * Commence a request cycle where the current HTTP method and OAuth
     * request scheme set an upper preferred HTTP request style and where
     * failures generate a new HTTP request style further down the OAuth
     * preference list for OAuth Request Schemes.
     * On success, return the Request object that results for processing.
     *
     * @param  array $params
     * @return Zend_Http_Response
     * @throws Zend_Oauth_Exception on HTTP request errors
     * @todo   Remove cycling?; Replace with upfront do-or-die configuration
     */
    public function startRequestCycle(array $params)
    {
        $response = null;
        $body     = null;
        $status   = null;
        try {
            $response = $this->_attemptRequest($params);
        } catch (Zend_Http_Client_Exception $e) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Error in HTTP request', null, $e);
        }
        if ($response !== null) {
            $body   = $response->getBody();
            $status = $response->getStatus();
        }
        if ($response === null // Request failure/exception
            || $status == 500  // Internal Server Error
            || $status == 400  // Bad Request
            || $status == 401  // Unauthorized
            || empty($body)    // Missing token
        ) {
            $this->_assessRequestAttempt($response);
            $response = $this->startRequestCycle($params);
        }
        return $response;
    }

    /**
     * Return an instance of Zend_Http_Client configured to use the Query
     * String scheme for an OAuth driven HTTP request.
     *
     * @param array $params
     * @param string $url
     * @return Zend_Http_Client
     */
    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($url);
        $client->getUri()->setQuery(
            $this->_httpUtility->toEncodedQueryString($params)
        );
        $client->setMethod($this->_preferredRequestMethod);
        return $client;
    }

    /**
     * Manages the switch from OAuth request scheme to another lower preference
     * scheme during a request cycle.
     *
     * @param  Zend_Http_Response
     * @return void
     * @throws Zend_Oauth_Exception if unable to retrieve valid token response
     */
    protected function _assessRequestAttempt(Zend_Http_Response $response = null)
    {
        switch ($this->_preferredRequestScheme) {
            case Zend_Oauth::REQUEST_SCHEME_HEADER:
                $this->_preferredRequestScheme = Zend_Oauth::REQUEST_SCHEME_POSTBODY;
                break;
            case Zend_Oauth::REQUEST_SCHEME_POSTBODY:
                $this->_preferredRequestScheme = Zend_Oauth::REQUEST_SCHEME_QUERYSTRING;
                break;
            default:
                #require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception(
                    'Could not retrieve a valid Token response from Token URL:'
                    . ($response !== null 
                        ? PHP_EOL . $response->getBody()
                        : ' No body - check for headers')
                );
        }
    }

    /**
     * Generates a valid OAuth Authorization header based on the provided
     * parameters and realm.
     *
     * @param  array $params
     * @param  string $realm
     * @return string
     */
    protected function _toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = array();
        $headerValue[] = 'OAuth realm="' . $realm . '"';
        foreach ($params as $key => $value) {
            if (!preg_match("/^oauth_/", $key)) {
                continue;
            }
            $headerValue[] = Zend_Oauth_Http_Utility::urlEncode($key)
                           . '="'
                           . Zend_Oauth_Http_Utility::urlEncode($value)
                           . '"';
        }
        return implode(",", $headerValue);
    }
}
