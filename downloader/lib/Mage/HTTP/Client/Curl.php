<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_HTTP
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with HTTP protocol using curl library
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_HTTP_Client_Curl
implements Mage_HTTP_IClient
{
    /**
     * Session Cookie storage, magento_root/var directory used
     * @var string
     */
    const COOKIE_FILE = 'var/cookie';

    /**
     * Hostname
     * @var string
     */
    protected $_host = 'localhost';

    /**
     * Port
     * @var int
     */
    protected $_port = 80;

    /**
     * Stream resource
     * @var object
     */
    protected $_sock = null;

    /**
     * Request headers
     * @var array
     */
    protected $_headers = array();


    /**
     * Fields for POST method - hash
     * @var array
     */
    protected $_postFields = array();

    /**
     * Request cookies
     * @var array
     */
    protected $_cookies = array();

    /**
     * Response headers
     * @var array
     */
    protected $_responseHeaders = array();

    /**
     * Response body
     * @var string
     */
    protected $_responseBody = '';

    /**
     * Response status
     * @var int
     */
    protected $_responseStatus = 0;


    /**
     * Request timeout
     * @var intunknown_type
     */
    protected $_timeout = 300;

    /**
     * TODO
     * @var int
     */
    protected $_redirectCount = 0;

    /**
     * Curl
     * @var object
     */
    protected $_ch;


    /**
     * User ovverides options hash
     * Are applied before curl_exec
     *
     * @var array();
     */
    protected $_curlUserOptions = array();

    /**
     * User credentials
     *
     * @var array();
     */
    protected $_auth = array();

    /**
     * Set request timeout, msec
     *
     * @param int $value
     */
    public function setTimeout($value)
    {
        $this->_timeout = (int) $value;
    }

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Destructor
     * Removes temporary environment
     */
    public function __destruct()
    {
        if (is_file(self::COOKIE_FILE)) {
            @unlink(self::COOKIE_FILE);
        }
    }

    /**
     * Set headers from hash

     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;

    }

    /**
     * Add header
     *
     * @param $name name, ex. "Location"
     * @param $value value ex. "http://google.com"
     */
    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }

    /**
     * Remove specified header
     *
     * @param string $name
     */
    public function removeHeader($name)
    {
        unset($this->_headers[$name]);

    }

    /**
     * Authorization: Basic header
     * Login credentials support
     *
     * @param string $login username
     * @param string $pass password
     */
    public function setCredentials($login, $pass)
    {
        $this->_auth['login'] = $login;
        $this->_auth['password'] = $pass;
        //$val= base64_encode( "$login:$pass" );
        //$this->addHeader( "Authorization", "Basic $val" );
    }

    /**
     * Add cookie
     *
     * @param string $name
     * @param string $value
     */
    public function addCookie($name, $value)
    {
        $this->_cookies[$name] = $value;
    }

    /**
     * Remove cookie
     *
     * @param string $name
     */
    public function removeCookie($name)
    {
        unset($this->_cookies[$name]);
    }

    /**
     * Set cookies array
     *
     * @param array $cookies
     */
    public function setCookies($cookies)
    {
        $this->_cookies = $cookies;
    }

    /**
     * Clear cookies
     */
    public function removeCookies()
    {
        $this->setCookies(array());
    }


    /**
     * Make GET request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     */
    public function get($uri)
    {
        $this->makeRequest("GET", $uri);
    }

    /**
     * Make POST request
     * @see lib/Mage/HTTP/Mage_HTTP_Client#post($uri, $params)
     */
    public function post($uri, $params)
    {
        $this->makeRequest("POST", $uri, $params);
    }


    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_responseHeaders;
    }


    /**
     * Get response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_responseBody;
    }

    /**
     * Get cookies response hash
     *
     * @return array
     */
    public function getCookies()
    {
        if(empty($this->_responseHeaders['Set-Cookie'])) {
            return array();
        }
        $out = array();
        foreach( $this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row);
            $c = count($values);
            if(!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if(is_null($val)) {
                continue;
            }
            $out[trim($key)] = trim($val);
        }
        return $out;
    }


    /**
     * Get cookies array with details
     * (domain, expire time etc)
     * @return array
     */
    public function getCookiesFull()
    {
        if(empty($this->_responseHeaders['Set-Cookie'])) {
            return array();
        }
        $out = array();
        foreach( $this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row);
            $c = count($values);
            if(!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if(is_null($val)) {
                continue;
            }
            $out[trim($key)] = array('value'=>trim($val));
            array_shift($values);
            $c--;
            if(!$c) {
                continue;
            }
            for($i = 0; $i<$c; $i++) {
                list($subkey, $val) = explode("=", $values[$i]);
                $out[trim($key)][trim($subkey)] = trim($val);
            }
        }
        return $out;
    }

    /**
     * Get response status code
     * @see lib/Mage/HTTP/Mage_HTTP_Client#getStatus()
     */
    public function getStatus()
    {
        return $this->_responseStatus;
    }

    /**
     * Make request
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return null
     */
    protected function makeRequest($method, $uri, $params = array())
    {
        static $isAuthorizationRequired = 0;
        $this->_ch = curl_init();

        // make request via secured layer
        if ($isAuthorizationRequired && strpos($uri, 'https://') !== 0) {
            $uri = str_replace('http://', '', $uri);
            $uri = 'https://' . $uri;
        }

        $this->curlOption(CURLOPT_URL, $uri);
        $this->curlOption(CURLOPT_SSL_VERIFYPEER, FALSE);
        $this->curlOption(CURLOPT_SSL_VERIFYHOST, 2);

        // force method to POST if secured
        if ($isAuthorizationRequired) {
            $method = 'POST';
        }

        if($method == 'POST') {
            $this->curlOption(CURLOPT_POST, 1);
            $postFields = is_array($params) ? $params : array();
            if ($isAuthorizationRequired) {
                $this->curlOption(CURLOPT_COOKIEJAR, self::COOKIE_FILE);
                $this->curlOption(CURLOPT_COOKIEFILE, self::COOKIE_FILE);
                $postFields = array_merge($postFields, $this->_auth);
            }
            if (!empty($postFields)) {
                $this->curlOption(CURLOPT_POSTFIELDS, $postFields);
            }
        } elseif($method == "GET") {
            $this->curlOption(CURLOPT_HTTPGET, 1);
        } else {
            $this->curlOption(CURLOPT_CUSTOMREQUEST, $method);
        }

        if(count($this->_headers)) {
            $heads = array();
            foreach($this->_headers as $k=>$v) {
                $heads[] = $k.': '.$v;
            }
            $this->curlOption(CURLOPT_HTTPHEADER, $heads);
        }

        if(count($this->_cookies)) {
            $cookies = array();
            foreach($this->_cookies as $k=>$v) {
                $cookies[] = "$k=$v";
            }
            $this->curlOption(CURLOPT_COOKIE, implode(";", $cookies));
        }

        if($this->_timeout) {
            $this->curlOption(CURLOPT_TIMEOUT, $this->_timeout);
        }

        if($this->_port != 80) {
            $this->curlOption(CURLOPT_PORT, $this->_port);
        }

        $this->curlOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlOption(CURLOPT_FOLLOWLOCATION, 1);
        $this->curlOption(CURLOPT_HEADERFUNCTION, array($this,'parseHeaders'));

        if(count($this->_curlUserOptions)) {
            foreach($this->_curlUserOptions as $k=>$v) {
                $this->curlOption($k, $v);
            }
        }

        $this->_responseHeaders = array();
        $this->_responseBody = curl_exec($this->_ch);
        $err = curl_errno($this->_ch);
        if($err) {
            $this->doError(curl_error($this->_ch));
        }
        if(!$this->getStatus()) {
            return $this->doError("Invalid response headers returned from server.");
        }
        curl_close($this->_ch);
        if (403 == $this->getStatus()) {
            if (!$isAuthorizationRequired) {
                $isAuthorizationRequired++;
                $this->makeRequest($method, $uri, $params);
                $isAuthorizationRequired=0;
            } else {
                return $this->doError(sprintf('Access denied for %s@%s', $_SESSION['auth']['login'], $uri));
            }
        }
    }

    /**
     * Throw error excpetion
     * @param $string
     * @throws Exception
     */
    public function isAuthorizationRequired()
    {
        if (isset($_SESSION['auth']['username']) && isset($_SESSION['auth']['password']) && !empty($_SESSION['auth']['username'])) {
            return true;
        }
        return false;
    }

    /**
     * Throw error excpetion
     * @param $string
     * @throws Exception
     */
    public function doError($string)
    {
        throw new Exception($string);
    }


    /**
     * Parse headers - CURL callback functin
     *
     * @param resource $ch curl handle, not needed
     * @param string $data
     * @return int
     */
    protected function parseHeaders($ch, $data)
    {
        if(preg_match('/^HTTP\/[\d\.x]+ (\d+)/', $data, $m)) {
            if (isset($m[1])) {
                $this->_responseStatus = (int)$m[1];
            }
        } else {
            $name = $value = '';
            $out = explode(": ", trim($data), 2);
            if(count($out) == 2) {
                $name = $out[0];
                $value = $out[1];
            }

            if(strlen($name)) {
                if("Set-Cookie" == $name) {
                    if(!isset($this->_responseHeaders[$name])) {
                        $this->_responseHeaders[$name] = array();
                    }
                    $this->_responseHeaders[$name][] = $value;
                } else {
                    $this->_responseHeaders[$name] = $value;
                }
            }
        }

        return strlen($data);
    }

    /**
     * Set curl option directly
     *
     * @param string $name
     * @param string $value
     */
    protected function curlOption($name, $value)
    {
        curl_setopt($this->_ch, $name, $value);
    }

    /**
     * Set curl options array directly
     * @param array $array
     */
    protected function curlOptions($array)
    {
        curl_setopt_array($this->_ch, $arr);
    }

    /**
     * Set CURL options ovverides array	 *
     */
    public function setOptions($arr)
    {
        $this->_curlUserOptions = $arr;
    }

    /**
     * Set curl option
     */
    public function setOption($name, $value)
    {
        $this->_curlUserOptions[$name] = $value;
    }
}
