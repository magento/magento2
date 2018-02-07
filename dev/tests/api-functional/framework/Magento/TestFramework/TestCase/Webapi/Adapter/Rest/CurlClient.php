<?php
/**
 * Client for invoking REST API
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase\Webapi\Adapter\Rest;

class CurlClient
{
    const EMPTY_REQUEST_BODY = 'Empty body';
    /**
     * @var string REST URL base path
     */
    protected $restBasePath = '/rest/';

    /**
     * @var array Response array
     */
    protected $responseArray;

    /**
     * @var array JSON Error code to error message mapping
     */
    protected $_jsonErrorMessages = [
        JSON_ERROR_DEPTH => 'Maximum depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'State mismatch',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, invalid JSON',
    ];

    /**
     * Perform HTTP GET request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $data
     * @param array $headers
     * @return mixed
     */
    public function get($resourcePath, $data = [], $headers = [])
    {
        $url = $this->constructResourceUrl($resourcePath);
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET;
        $resp = $this->_invokeApi($url, $curlOpts, $headers);
        $respArray = $this->_jsonDecode($resp["body"]);
        return $respArray;
    }

    /**
     * Perform HTTP POST request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $data
     * @param array $headers
     * @return mixed
     */
    public function post($resourcePath, $data, $headers = [])
    {
        return $this->_postOrPut($resourcePath, $data, false, $headers);
    }

    /**
     * Perform HTTP PUT request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $data
     * @param array $headers
     * @return mixed
     */
    public function put($resourcePath, $data, $headers = [])
    {
        return $this->_postOrPut($resourcePath, $data, true, $headers);
    }

    /**
     * Perform HTTP DELETE request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $headers
     * @return mixed
     */
    public function delete($resourcePath, $headers = [])
    {
        $url = $this->constructResourceUrl($resourcePath);

        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE;

        $resp = $this->_invokeApi($url, $curlOpts, $headers);
        $respArray = $this->_jsonDecode($resp["body"]);

        return $respArray;
    }

    /**
     * Perform HTTP POST or PUT request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $data
     * @param boolean $put Set true to post data as HTTP PUT operation (update). If this value is set to false,
     *        HTTP POST (create) will be used
     * @param array $headers
     * @return mixed
     */
    protected function _postOrPut($resourcePath, $data, $put = false, $headers = [])
    {
        $url = $this->constructResourceUrl($resourcePath);

        if (in_array("Content-Type: application/json", $headers)) {
            // json encode data
            if ($data != self::EMPTY_REQUEST_BODY) {
                $data = $this->_jsonEncode($data);
            } else {
                $data = '';
            }
        }

        $curlOpts = [];
        if ($put) {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT;
        } else {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST;
        }
        $headers[] = 'Content-Length: ' . strlen($data);
        $curlOpts[CURLOPT_POSTFIELDS] = $data;

        $this->responseArray = $this->_invokeApi($url, $curlOpts, $headers);
        $respBodyArray = $this->_jsonDecode($this->responseArray["body"]);

        return $respBodyArray;
    }

    /**
     * Set Rest base path if available
     *
     * @param string $restBasePath
     *
     * @return void
     */
    public function setRestBasePath($restBasePath)
    {
        $this->restBasePath = $restBasePath;
    }

    /**
     * Get current response array
     *
     * @return array
     */
    public function getCurrentResponseArray()
    {
        return $this->responseArray;
    }

    /**
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @return string resource URL
     * @throws \Exception
     */
    public function constructResourceUrl($resourcePath)
    {
        return rtrim(TESTS_BASE_URL, '/') . $this->restBasePath . ltrim($resourcePath, '/');
    }

    /**
     * Makes the REST api call using passed $curl object
     *
     * @param string $url
     * @param array $additionalCurlOpts cURL Options
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    protected function _invokeApi($url, $additionalCurlOpts, $headers = [])
    {
        // initialize cURL
        $curl = curl_init($url);
        if ($curl === false) {
            throw new \Exception("Error Initializing cURL for baseUrl: " . $url);
        }

        // get cURL options
        $curlOpts = $this->_getCurlOptions($additionalCurlOpts, $headers);

        // add CURL opts
        foreach ($curlOpts as $opt => $val) {
            curl_setopt($curl, $opt, $val);
        }

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception(curl_error($curl));
        }

        $resp = [];
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $resp["header"] = substr($response, 0, $headerSize);
        $resp["body"] = substr($response, $headerSize);

        $resp["meta"] = curl_getinfo($curl);
        if ($resp["meta"] === false) {
            throw new \Exception(curl_error($curl));
        }

        curl_close($curl);

        $meta = $resp["meta"];
        if ($meta && $meta['http_code'] >= 400) {
            throw new \Exception($resp["body"], $meta['http_code']);
        }

        return $resp;
    }

    /**
     * Constructs and returns a curl options array
     *
     * @param array $customCurlOpts Additional / overridden cURL options
     * @param array $headers
     * @return array
     */
    protected function _getCurlOptions($customCurlOpts = [], $headers = [])
    {
        // default curl options
        $curlOpts = [
            CURLOPT_RETURNTRANSFER => true, // return result instead of echoing
            CURLOPT_SSL_VERIFYPEER => false, // stop cURL from verifying the peer's certificate
            CURLOPT_FOLLOWLOCATION => false, // follow redirects, Location: headers
            CURLOPT_MAXREDIRS => 10, // but don't redirect more than 10 times
            CURLOPT_HTTPHEADER => [],
            CURLOPT_HEADER => 1,
        ];

        // merge headers
        $headers = array_merge($curlOpts[CURLOPT_HTTPHEADER], $headers);
        if (TESTS_XDEBUG_ENABLED) {
            $headers[] = 'Cookie: XDEBUG_SESSION=' . TESTS_XDEBUG_SESSION;
        }
        $curlOpts[CURLOPT_HTTPHEADER] = $headers;

        // merge custom Curl Options & return
        foreach ($customCurlOpts as $opt => $val) {
            $curlOpts[$opt] = $val;
        }

        return $curlOpts;
    }

    /**
     * JSON encode with error checking
     *
     * @param mixed $data
     * @return string
     * @throws \Exception
     */
    protected function _jsonEncode($data)
    {
        $ret = json_encode($data);
        $this->_checkJsonError($data);

        // return the json String
        return $ret;
    }

    /**
     * Decode a JSON string with error checking
     *
     * @param string $data
     * @param bool $asArray
     * @throws \Exception
     * @return mixed
     */
    protected function _jsonDecode($data, $asArray = true)
    {
        $ret = json_decode($data, $asArray);
        $this->_checkJsonError($data);

        // return the array
        return $ret;
    }

    /**
     * Checks for JSON error in the latest encoding / decoding and throws an exception in case of error
     *
     * @throws \Exception
     */
    protected function _checkJsonError()
    {
        $jsonError = json_last_error();
        if ($jsonError !== JSON_ERROR_NONE) {
            // find appropriate error message
            $message = 'Unknown JSON Error';
            if (isset($this->_jsonErrorMessages[$jsonError])) {
                $message = $this->_jsonErrorMessages[$jsonError];
            }

            throw new \Exception(
                'JSON Encoding / Decoding error: ' . $message . var_export(func_get_arg(0), true),
                $jsonError
            );
        }
    }
}
