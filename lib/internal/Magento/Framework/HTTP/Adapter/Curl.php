<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * HTTP CURL Adapter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\HTTP\Adapter;

class Curl implements \Zend_Http_Client_Adapter_Interface
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Curl handle
     *
     * @var resource
     */
    protected $_resource;

    /**
     * Allow parameters
     *
     * @var array
     */
    protected $_allowedParams = [
        'timeout'      => CURLOPT_TIMEOUT,
        'maxredirects' => CURLOPT_MAXREDIRS,
        'proxy'        => CURLOPT_PROXY,
        'ssl_cert'     => CURLOPT_SSLCERT,
        'userpwd'      => CURLOPT_USERPWD,
        'useragent'    => CURLOPT_USERAGENT,
        'referer'      => CURLOPT_REFERER,
        'protocols'    => CURLOPT_PROTOCOLS,
        'verifypeer'   => CURLOPT_SSL_VERIFYPEER,
        'verifyhost'   => CURLOPT_SSL_VERIFYHOST,
    ];

    /**
     * Array of CURL options
     *
     * @var array
     */
    protected $_options = [];

    public function __construct()
    {
        // as we support PHP 5.5.x in Magento 2.0.x we can't do this in declaration
        $this->_config['protocols'] = (CURLPROTO_HTTP | CURLPROTO_HTTPS |  CURLPROTO_FTP | CURLPROTO_FTPS);
        $this->_config['verifypeer'] = true;
        $this->_config['verifyhost'] = 2;
    }

    /**
     * Apply current configuration array to transport resource
     *
     * @return \Magento\Framework\HTTP\Adapter\Curl
     */
    protected function _applyConfig()
    {
        // apply additional options to cURL
        foreach ($this->_options as $option => $value) {
            curl_setopt($this->_getResource(), $option, $value);
        }

        // apply config options
        foreach ($this->getDefaultConfig() as $option => $value) {
            curl_setopt($this->_getResource(), $option, $value);
        }

        return $this;
    }

    /**
     * Get default options
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        $config = [];
        foreach (array_keys($this->_config) as $param) {
            if (array_key_exists($param, $this->_allowedParams)) {
                $config[$this->_allowedParams[$param]] = $this->_config[$param];
            }
        }
        return $config;
    }

    /**
     * Set array of additional cURL options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Add additional option to cURL
     *
     * @param  int $option      the CURLOPT_* constants
     * @param  mixed $value
     * @return $this
     */
    public function addOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param array $config
     * @return $this
     */
    public function setConfig($config = [])
    {
        foreach ($config as $key => $value) {
            $this->_config[$key] = $value;
        }
        return $this;
    }

    /**
     * Connect to the remote server
     *
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function connect($host, $port = 80, $secure = false)
    {
        return $this->_applyConfig();
    }

    /**
     * Send request to the remote server
     *
     * @param string $method
     * @param \Zend_Uri_Http|string $url
     * @param string $http_ver
     * @param array $headers
     * @param string $body
     * @return string Request as text
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function write($method, $url, $http_ver = '1.1', $headers = [], $body = '')
    {
        if ($url instanceof \Zend_Uri_Http) {
            $url = $url->getUri();
        }
        $this->_applyConfig();

        // set url to post to
        curl_setopt($this->_getResource(), CURLOPT_URL, $url);
        curl_setopt($this->_getResource(), CURLOPT_RETURNTRANSFER, true);
        if ($method == \Zend_Http_Client::POST) {
            curl_setopt($this->_getResource(), CURLOPT_POST, true);
            curl_setopt($this->_getResource(), CURLOPT_POSTFIELDS, $body);
        } elseif ($method == \Zend_Http_Client::GET) {
            curl_setopt($this->_getResource(), CURLOPT_HTTPGET, true);
        }

        if (is_array($headers)) {
            curl_setopt($this->_getResource(), CURLOPT_HTTPHEADER, $headers);
        }

        /**
         * @internal Curl options setter have to be re-factored
         */
        $header = isset($this->_config['header']) ? $this->_config['header'] : true;
        curl_setopt($this->_getResource(), CURLOPT_HEADER, $header);

        return $body;
    }

    /**
     * Read response from server
     *
     * @return string
     */
    public function read()
    {
        $response = curl_exec($this->_getResource());

        // Remove 100 and 101 responses headers
        while (\Zend_Http_Response::extractCode($response) == 100
            || \Zend_Http_Response::extractCode($response) == 101
        ) {
            $response = preg_split('/^\r?$/m', $response, 2);
            $response = trim($response[1]);
        }

        // CUrl will handle chunked data but leave the header.
        $response = preg_replace('/Transfer-Encoding:\s+chunked\r?\n/i', '', $response);

        return $response;
    }

    /**
     * Close the connection to the server
     *
     * @return $this
     */
    public function close()
    {
        curl_close($this->_getResource());
        $this->_resource = null;
        return $this;
    }

    /**
     * Returns a cURL handle on success
     *
     * @return resource
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = curl_init();
        }
        return $this->_resource;
    }

    /**
     * Get last error number
     *
     * @return int
     */
    public function getErrno()
    {
        return curl_errno($this->_getResource());
    }

    /**
     * Get string with last error for the current session
     *
     * @return string
     */
    public function getError()
    {
        return curl_error($this->_getResource());
    }

    /**
     * Get information regarding a specific transfer
     *
     * @param int $opt CURLINFO option
     * @return mixed
     */
    public function getInfo($opt = 0)
    {
        return curl_getinfo($this->_getResource(), $opt);
    }

    /**
     * curl_multi_* requests support
     *
     * @param array $urls
     * @param array $options
     * @return array
     */
    public function multiRequest($urls, $options = [])
    {
        $handles = [];
        $result = [];

        $multihandle = curl_multi_init();

        // add default parameters
        foreach ($this->getDefaultConfig() as $defaultOption => $defaultValue) {
            if (!isset($options[$defaultOption])) {
                $options[$defaultOption] = $defaultValue;
            }
        }

        foreach ($urls as $key => $url) {
            $handles[$key] = curl_init();
            curl_setopt($handles[$key], CURLOPT_URL, $url);
            curl_setopt($handles[$key], CURLOPT_HEADER, 0);
            curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, 1);
            if (!empty($options)) {
                curl_setopt_array($handles[$key], $options);
            }
            curl_multi_add_handle($multihandle, $handles[$key]);
        }
        $process = null;
        do {
            curl_multi_exec($multihandle, $process);
            usleep(100);
        } while ($process > 0);

        foreach ($handles as $key => $handle) {
            $result[$key] = curl_multi_getcontent($handle);
            curl_multi_remove_handle($multihandle, $handle);
        }
        curl_multi_close($multihandle);
        return $result;
    }
}
