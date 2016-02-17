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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Oauth_Http_Utility */
#require_once 'Zend/Oauth/Http/Utility.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Oauth_Token
{
    /**@+
     * Token constants
     */
    const TOKEN_PARAM_KEY                = 'oauth_token';
    const TOKEN_SECRET_PARAM_KEY         = 'oauth_token_secret';
    const TOKEN_PARAM_CALLBACK_CONFIRMED = 'oauth_callback_confirmed';
    /**@-*/

    /**
     * Token parameters
     *
     * @var array
     */
    protected $_params = array();

    /**
     * OAuth response object
     *
     * @var Zend_Http_Response
     */
    protected $_response = null;

    /**
     * @var Zend_Oauth_Http_Utility
     */
    protected $_httpUtility = null;

    /**
     * Constructor; basic setup for any Token subclass.
     *
     * @param  null|Zend_Http_Response $response
     * @param  null|Zend_Oauth_Http_Utility $utility
     * @return void
     */
    public function __construct(
        Zend_Http_Response $response = null,
        Zend_Oauth_Http_Utility $utility = null
    ) {
        if ($response !== null) {
            $this->_response = $response;
            $params = $this->_parseParameters($response);
            if (count($params) > 0) {
                $this->setParams($params);
            }
        }
        if ($utility !== null) {
            $this->_httpUtility = $utility;
        } else {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }

    /**
     * Attempts to validate the Token parsed from the HTTP response - really
     * it's just very basic existence checks which are minimal.
     *
     * @return bool
     */
    public function isValid()
    {
        if (isset($this->_params[self::TOKEN_PARAM_KEY])
            && !empty($this->_params[self::TOKEN_PARAM_KEY])
            && isset($this->_params[self::TOKEN_SECRET_PARAM_KEY])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Return the HTTP response object used to initialise this instance.
     *
     * @return Zend_Http_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Sets the value for the this Token's secret which may be used when signing
     * requests with this Token.
     *
     * @param  string $secret
     * @return Zend_Oauth_Token
     */
    public function setTokenSecret($secret)
    {
        $this->setParam(self::TOKEN_SECRET_PARAM_KEY, $secret);
        return $this;
    }

    /**
     * Retrieve this Token's secret which may be used when signing
     * requests with this Token.
     *
     * @return string
     */
    public function getTokenSecret()
    {
        return $this->getParam(self::TOKEN_SECRET_PARAM_KEY);
    }

    /**
     * Sets the value for a parameter (e.g. token secret or other) and run
     * a simple filter to remove any trailing newlines.
     *
     * @param  string $key
     * @param  string $value
     * @return Zend_Oauth_Token
     */
    public function setParam($key, $value)
    {
        $this->_params[$key] = trim($value, "\n");
        return $this;
    }

    /**
     * Sets the value for some parameters (e.g. token secret or other) and run
     * a simple filter to remove any trailing newlines.
     *
     * @param  array $params
     * @return Zend_Oauth_Token
     */
    public function setParams(array $params)
    {
        foreach ($params as $key=>$value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    /**
     * Get the value for a parameter (e.g. token secret or other).
     *
     * @param  string $key
     * @return mixed
     */
    public function getParam($key)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
        return null;
    }

    /**
     * Sets the value for a Token.
     *
     * @param  string $token
     * @return Zend_Oauth_Token
     */
    public function setToken($token)
    {
        $this->setParam(self::TOKEN_PARAM_KEY, $token);
        return $this;
    }

    /**
     * Gets the value for a Token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getParam(self::TOKEN_PARAM_KEY);
    }

    /**
     * Generic accessor to enable access as public properties.
     *
     * @return string
     */
    public function __get($key)
    {
        return $this->getParam($key);
    }

    /**
     * Generic mutator to enable access as public properties.
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setParam($key, $value);
    }

    /**
     * Convert Token to a string, specifically a raw encoded query string.
     *
     * @return string
     */
    public function toString()
    {
        return $this->_httpUtility->toEncodedQueryString($this->_params);
    }

    /**
     * Convert Token to a string, specifically a raw encoded query string.
     * Aliases to self::toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Parse a HTTP response body and collect returned parameters
     * as raw url decoded key-value pairs in an associative array.
     *
     * @param  Zend_Http_Response $response
     * @return array
     */
    protected function _parseParameters(Zend_Http_Response $response)
    {
        $params = array();
        $body   = $response->getBody();
        if (empty($body)) {
            return;
        }

        // validate body based on acceptable characters...todo
        $parts = explode('&', $body);
        foreach ($parts as $kvpair) {
            $pair = explode('=', $kvpair);
            $params[rawurldecode($pair[0])] = rawurldecode($pair[1]);
        }
        return $params;
    }

    /**
     * Limit serialisation stored data to the parameters
     */
    public function __sleep()
    {
        return array('_params');
    }

    /**
     * After serialisation, re-instantiate a HTTP utility class for use
     */
    public function __wakeup()
    {
        if ($this->_httpUtility === null) {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }
}
