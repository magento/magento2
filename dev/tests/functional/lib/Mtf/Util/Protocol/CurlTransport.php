<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Util\Protocol;

/**
 * HTTP CURL Adapter
 */
class CurlTransport implements CurlInterface
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
        'timeout' => CURLOPT_TIMEOUT,
        'maxredirects' => CURLOPT_MAXREDIRS,
        'proxy' => CURLOPT_PROXY,
        'ssl_cert' => CURLOPT_SSLCERT,
        'userpwd' => CURLOPT_USERPWD,
    ];

    /**
     * Array of CURL options
     *
     * @var array
     */
    protected $_options = [];

    /**
     * Apply current configuration array to curl resource
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _applyConfig()
    {
        // apply additional options to cURL
        foreach ($this->_options as $option => $value) {
            curl_setopt($this->_getResource(), $option, $value);
        }

        if (empty($this->_config)) {
            return $this;
        }
        foreach ($this->_config as $param => $curlOption) {
            if (array_key_exists($param, $this->_allowedParams)) {
                curl_setopt($this->_getResource(), $this->_allowedParams[$param], $this->_config[$param]);
            }
        }
        return $this;
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
        $this->_config = $config;
        return $this;
    }

    /**
     * Send request to the remote server
     *
     * @param $method
     * @param $url
     * @param string $httpVer
     * @param array $headers
     * @param array $params
     * @return void
     */
    public function write($method, $url, $httpVer = '1.1', $headers = [], $params = [])
    {
        $this->_applyConfig();
        $options = [
            CURLOPT_URL                 => $url,
            CURLOPT_RETURNTRANSFER      => true,
            CURLOPT_FOLLOWLOCATION      => true,
            CURLOPT_COOKIEFILE          => '',
            CURLOPT_HTTPHEADER          => $headers,
        ];
        if ($method == CurlInterface::POST) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $params;
        } elseif ($method == CurlInterface::GET) {
            $options[CURLOPT_HTTPGET] = true;
        }

        curl_setopt_array($this->_getResource(), $options);
    }

    /**
     * Read response from server
     *
     * @return string
     */
    public function read()
    {
        $response = curl_exec($this->_getResource());
        return $response;
    }

    /**
     * Close the connection to the server
     */
    public function close()
    {
        curl_close($this->_getResource());
        $this->_resource = null;
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

    /**
     * Extract the response code from a response string
     *
     * @param string $responseStr
     * @return int
     */
    public static function extractCode($responseStr)
    {
        preg_match("|^HTTP/[\d\.x]+ (\d+)|", $responseStr, $m);

        if (isset($m[1])) {
            return (int)$m[1];
        } else {
            return false;
        }
    }
}
