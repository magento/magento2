<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase\Webapi\Adapter\Rest;

use Magento\TestFramework\TestCase\HttpClient\CurlClient;
use Magento\TestFramework\Helper\JsonSerializer;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Client for invoking REST API
 */
class RestClient
{
    const EMPTY_REQUEST_BODY = 'Empty body';

    /**
     * @var string REST URL base path
     */
    private $restBasePath = '/rest/';

    /** @var CurlClient */
    private $curlClient;

    /** @var JsonSerializer */
    private $jsonSerializer;

    /**
     * CurlClient constructor.
     *
     * @param CurlClient|null $curlClient
     * @param JsonSerializer|null $jsonSerializer
     */
    public function __construct(
        \Magento\TestFramework\TestCase\HttpClient\CurlClient $curlClient = null,
        \Magento\TestFramework\Helper\JsonSerializer $jsonSerializer = null
    ) {
        $objectManager = Bootstrap::getObjectManager();
        $this->curlClient = $curlClient ? : $objectManager->get(CurlClient::class);
        $this->jsonSerializer = $jsonSerializer ? : $objectManager->get(JsonSerializer::class);
    }

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

        $responseBody = $this->curlClient->get($url, $data, $headers);
        return $this->jsonSerializer->jsonDecode($responseBody);
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
        $url = $this->constructResourceUrl($resourcePath);
        if (in_array("Content-Type: application/json", $headers)) {
            // json encode data
            if ($data != self::EMPTY_REQUEST_BODY) {
                $data = $this->jsonSerializer->jsonEncode($data);
            } else {
                $data = '';
            }
        }
        $responseBody = $this->curlClient->post($url, $data, $headers);
        return $this->jsonSerializer->jsonDecode($responseBody);
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
        $url = $this->constructResourceUrl($resourcePath);
        if (in_array("Content-Type: application/json", $headers)) {
            // json encode data
            if ($data != self::EMPTY_REQUEST_BODY) {
                $data = $this->jsonSerializer->jsonEncode($data);
            } else {
                $data = '';
            }
        }
        $responseBody = $this->curlClient->put($url, $data, $headers);
        return $this->jsonSerializer->jsonDecode($responseBody);
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
        $responseBody = $this->curlClient->delete($url, $headers);
        return $this->jsonSerializer->jsonDecode($responseBody);
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
}
