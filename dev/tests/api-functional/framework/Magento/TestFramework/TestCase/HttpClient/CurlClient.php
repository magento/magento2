<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase\HttpClient;

/**
 * Generic cURL client wrapper for get/delete/post/put requests
 */
class CurlClient
{
    const EMPTY_REQUEST_BODY = 'Empty body';

    /**
     * Perform HTTP GET request
     *
     * @param string $url Resource URL like /V1/Resource1/123
     * @param array $data
     * @param array $headers
     * @return string
     */
    public function get($url, $data = [], $headers = [])
    {
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET;
        $resp = $this->invokeApi($url, $curlOpts, $headers);
        return $resp["body"];
    }

    /**
     * Perform HTTP DELETE request
     *
     * @param string $url
     * @param array $headers
     * @return string
     */
    public function delete($url, $headers = [])
    {
        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE;

        $resp = $this->invokeApi($url, $curlOpts, $headers);
        return $resp["body"];
    }

    /**
     * Perform HTTP POST request
     *
     * @param string $url
     * @param array|string $data
     * @param array $headers
     * @return string
     */
    public function post($url, $data, $headers = [])
    {
        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST;
        $headers[] = 'Content-Length: ' . strlen($data);
        $curlOpts[CURLOPT_POSTFIELDS] = $data;

        $resp = $this->invokeApi($url, $curlOpts, $headers);
        return $resp["body"];
    }

    /**
     * Perform HTTP PUT request
     *
     * @param string $url
     * @param array|string $data
     * @param array $headers
     * @return string
     */
    public function put($url, $data, $headers = [])
    {
        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT;
        $headers[] = 'Content-Length: ' . strlen($data);
        $curlOpts[CURLOPT_POSTFIELDS] = $data;

        $resp = $this->invokeApi($url, $curlOpts, $headers);
        return $resp["body"];
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
    public function invokeApi($url, $additionalCurlOpts, $headers = [])
    {
        // initialize cURL
        $curl = curl_init($url);
        if ($curl === false) {
            throw new \Exception("Error Initializing cURL for baseUrl: " . $url);
        }

        // get cURL options
        $curlOpts = $this->getCurlOptions($additionalCurlOpts, $headers);

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
    private function getCurlOptions($customCurlOpts = [], $headers = [])
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
}
