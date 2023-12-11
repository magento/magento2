<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Adapter;

use Laminas\Http\Client\Adapter\AdapterInterface;
use Laminas\Http\Request;

/**
 * Curl http adapter
 *
 * @api
 */
class Curl implements AdapterInterface
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $_config = [
        'protocols' => (CURLPROTO_HTTP
            | CURLPROTO_HTTPS
            | CURLPROTO_FTP
            | CURLPROTO_FTPS
        ),
        'verifypeer' => true,
        'verifyhost' => 2
    ];

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
        'sslversion'   => CURLOPT_SSLVERSION,
    ];

    /**
     * Apply current configuration array to transport resource
     *
     * @return \Magento\Framework\HTTP\Adapter\Curl
     */
    protected function _applyConfig()
    {
        // apply config options
        foreach ($this->_config as $k => $v) {
            if (is_string($k) && array_key_exists($k, $this->_allowedParams)) {
                $k = $this->_allowedParams[$k];
            }
            if (is_int($k)) {
                curl_setopt($this->_getResource(), $k, $v);
            }
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
     * Set the configuration array for the adapter
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options = [])
    {
        foreach ($options as $k => $v) {
            $this->_config[$k] = $v;
        }
        return $this;
    }

    /**
     * Add configuration option to cURL
     *
     * @param int $option the CURLOPT_* constants
     * @param mixed $value
     * @return $this
     * @deprecated To avoid confusion after migration from ZF1 to Laminas (`setConfig` method renamed to `setOptions`).
     * @see Use \Magento\Framework\HTTP\Adapter\Curl::setOptions instead.
     */
    public function addOption($option, $value)
    {
        $this->_config[$option] = $value;
        return $this;
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param array $config
     * @return $this
     * @deprecated To avoid confusion after migration from ZF1 to Laminas (`setConfig` method renamed to `setOptions`).
     * @see Use \Magento\Framework\HTTP\Adapter\Curl::setOptions instead.
     */
    public function setConfig($config = [])
    {
        return $this->setOptions($config);
    }

    /**
     * Connect to the remote server
     *
     * @param string $host
     * @param int $port
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
     * @param string $url
     * @param string $http_ver
     * @param array $headers
     * @param string $body
     * @return string Request as text
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function write($method, $url, $http_ver = '1.1', $headers = [], $body = '')
    {
        $this->_applyConfig();

        // set url to post to
        curl_setopt($this->_getResource(), CURLOPT_URL, $url);
        curl_setopt($this->_getResource(), CURLOPT_RETURNTRANSFER, true);
        if ($method === Request::METHOD_POST) {
            curl_setopt($this->_getResource(), CURLOPT_POST, true);
            curl_setopt($this->_getResource(), CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->_getResource(), CURLOPT_POSTFIELDS, $body);
        } elseif ($method === Request::METHOD_PUT) {
            curl_setopt($this->_getResource(), CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($this->_getResource(), CURLOPT_POSTFIELDS, $body);
        } elseif ($method === Request::METHOD_GET) {
            curl_setopt($this->_getResource(), CURLOPT_HTTPGET, true);
            curl_setopt($this->_getResource(), CURLOPT_CUSTOMREQUEST, 'GET');
        } elseif ($method === Request::METHOD_DELETE) {
            curl_setopt($this->_getResource(), CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($this->_getResource(), CURLOPT_POSTFIELDS, $body);
        }

        if ($http_ver === Request::VERSION_11) {
            curl_setopt($this->_getResource(), CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        } elseif ($http_ver === Request::VERSION_10) {
            curl_setopt($this->_getResource(), CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        }

        if (is_array($headers)) {
            curl_setopt($this->_getResource(), CURLOPT_HTTPHEADER, $headers);
        }

        /**
         * @internal Curl options setter have to be re-factored
         */
        curl_setopt($this->_getResource(), CURLOPT_HEADER, $this->_config['header'] ?? true);

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
        if ($response === false) {
            return '';
        }

        // Remove 100 and 101 responses headers
        while ($this->extractCodeFromResponse($response) === 100 || $this->extractCodeFromResponse($response) === 101) {
            $response = preg_split('/^\r?$/m', $response, 2);
            $response = trim($response[1]);
        }

        // Curl will handle chunked data but leave the header.
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
        if ($this->_resource === null) {
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
     * Curl_multi_* requests support
     *
     * @param array $urls
     * @param array $options
     * @return array
     * @deprecated Because of migration from Zend_Http to laminas-http.
     * @see No alternatives.
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

    /**
     * Extract the response code from a response string
     *
     * @param string $responseString
     *
     * @return false|int
     */
    private function extractCodeFromResponse(string $responseString)
    {
        preg_match("|^HTTP/[\d\.x]+ (\d+)|", $responseString, $matches);
        if (isset($matches[1])) {
            return (int)$matches[1];
        }

        return false;
    }
}
