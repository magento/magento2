<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Client;

/**
 * Class to work with HTTP protocol using curl library
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Curl implements \Magento\Framework\HTTP\ClientInterface
{
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
    protected $_headers = [];

    /**
     * Fields for POST method - hash
     * @var array
     */
    protected $_postFields = [];

    /**
     * Request cookies
     * @var array
     */
    protected $_cookies = [];

    /**
     * Response headers
     * @var array
     */
    protected $_responseHeaders = [];

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
     * @var int type
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
     * User overrides options hash
     * Are applied before curl_exec
     *
     * @var array
     */
    protected $_curlUserOptions = [];

    /**
     * Header count, used while parsing headers
     * in CURL callback function
     * @var int
     */
    protected $_headerCount = 0;

    /**
     * Set request timeout, msec
     *
     * @param int $value
     * @return void
     */
    public function setTimeout($value)
    {
        $this->_timeout = (int)$value;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Set headers from hash
     *
     * @param array $headers
     * @return void
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * Add header
     *
     * @param string $name name, ex. "Location"
     * @param string $value value ex. "http://google.com"
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }

    /**
     * Remove specified header
     *
     * @param string $name
     * @return void
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
     * @return void
     */
    public function setCredentials($login, $pass)
    {
        $val = base64_encode("{$login}:{$pass}");
        $this->addHeader("Authorization", "Basic {$val}");
    }

    /**
     * Add cookie
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addCookie($name, $value)
    {
        $this->_cookies[$name] = $value;
    }

    /**
     * Remove cookie
     *
     * @param string $name
     * @return void
     */
    public function removeCookie($name)
    {
        unset($this->_cookies[$name]);
    }

    /**
     * Set cookies array
     *
     * @param array $cookies
     * @return void
     */
    public function setCookies($cookies)
    {
        $this->_cookies = $cookies;
    }

    /**
     * Clear cookies
     * @return void
     */
    public function removeCookies()
    {
        $this->setCookies([]);
    }

    /**
     * Make GET request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     * @return void
     */
    public function get($uri)
    {
        $this->makeRequest("GET", $uri);
    }

    /**
     * Make POST request
     *
     * @param string $uri
     * @param array $params
     * @return void
     *
     * @see \Magento\Framework\HTTP\Client#post($uri, $params)
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
        if (empty($this->_responseHeaders['Set-Cookie'])) {
            return [];
        }
        $out = [];
        foreach ($this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row);
            $c = count($values);
            if (!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if (is_null($val)) {
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
        if (empty($this->_responseHeaders['Set-Cookie'])) {
            return [];
        }
        $out = [];
        foreach ($this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row);
            $c = count($values);
            if (!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if (is_null($val)) {
                continue;
            }
            $out[trim($key)] = ['value' => trim($val)];
            array_shift($values);
            $c--;
            if (!$c) {
                continue;
            }
            for ($i = 0; $i < $c; $i++) {
                list($subkey, $val) = explode("=", $values[$i]);
                $out[trim($key)][trim($subkey)] = trim($val);
            }
        }
        return $out;
    }

    /**
     * Get response status code
     * @see lib\Magento\Framework\HTTP\Client#getStatus()
     *
     * @return int
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
     * @return void
     */
    protected function makeRequest($method, $uri, $params = [])
    {
        $this->_ch = curl_init();
        $this->curlOption(CURLOPT_URL, $uri);
        if ($method == 'POST') {
            $this->curlOption(CURLOPT_POST, 1);
            $this->curlOption(CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif ($method == "GET") {
            $this->curlOption(CURLOPT_HTTPGET, 1);
        } else {
            $this->curlOption(CURLOPT_CUSTOMREQUEST, $method);
        }

        if (count($this->_headers)) {
            $heads = [];
            foreach ($this->_headers as $k => $v) {
                $heads[] = $k . ': ' . $v;
            }
            $this->curlOption(CURLOPT_HTTPHEADER, $heads);
        }

        if (count($this->_cookies)) {
            $cookies = [];
            foreach ($this->_cookies as $k => $v) {
                $cookies[] = "{$k}={$v}";
            }
            $this->curlOption(CURLOPT_COOKIE, implode(";", $cookies));
        }

        if ($this->_timeout) {
            $this->curlOption(CURLOPT_TIMEOUT, $this->_timeout);
        }

        if ($this->_port != 80) {
            $this->curlOption(CURLOPT_PORT, $this->_port);
        }

        //$this->curlOption(CURLOPT_HEADER, 1);
        $this->curlOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlOption(CURLOPT_HEADERFUNCTION, [$this, 'parseHeaders']);

        if (count($this->_curlUserOptions)) {
            foreach ($this->_curlUserOptions as $k => $v) {
                $this->curlOption($k, $v);
            }
        }

        $this->_headerCount = 0;
        $this->_responseHeaders = [];
        $this->_responseBody = curl_exec($this->_ch);
        $err = curl_errno($this->_ch);
        if ($err) {
            $this->doError(curl_error($this->_ch));
        }
        curl_close($this->_ch);
    }

    /**
     * Throw error exception
     * @param string $string
     * @return void
     * @throws \Exception
     */
    public function doError($string)
    {
        throw new \Exception($string);
    }

    /**
     * Parse headers - CURL callback function
     *
     * @param resource $ch curl handle, not needed
     * @param string $data
     * @return int
     */
    protected function parseHeaders($ch, $data)
    {
        if ($this->_headerCount == 0) {
            $line = explode(" ", trim($data), 3);
            if (count($line) != 3) {
                return $this->doError("Invalid response line returned from server: " . $data);
            }
            $this->_responseStatus = intval($line[1]);
        } else {
            //var_dump($data);
            $name = $value = '';
            $out = explode(": ", trim($data), 2);
            if (count($out) == 2) {
                $name = $out[0];
                $value = $out[1];
            }

            if (strlen($name)) {
                if ("Set-Cookie" == $name) {
                    if (!isset($this->_responseHeaders[$name])) {
                        $this->_responseHeaders[$name] = [];
                    }
                    $this->_responseHeaders[$name][] = $value;
                } else {
                    $this->_responseHeaders[$name] = $value;
                }
            }
        }
        $this->_headerCount++;

        return strlen($data);
    }

    /**
     * Set curl option directly
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    protected function curlOption($name, $value)
    {
        curl_setopt($this->_ch, $name, $value);
    }

    /**
     * Set curl options array directly
     * @param array $arr
     * @return void
     */
    protected function curlOptions($arr)
    {
        curl_setopt_array($this->_ch, $arr);
    }

    /**
     * Set CURL options overrides array
     * @param array $arr
     * @return void
     */
    public function setOptions($arr)
    {
        $this->_curlUserOptions = $arr;
    }

    /**
     * Set curl option
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->_curlUserOptions[$name] = $value;
    }
}
