<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\TestCase\GraphQl;

use Magento\TestFramework\TestCase\HttpClient\CurlClient;
use Magento\TestFramework\Helper\JsonSerializer;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Curl client for GraphQL
 */
class Client
{
    /**#@+
     * GraphQL HTTP method
     */
    const GRAPHQL_METHOD_POST = 'POST';
    /**#@-*/

    /** @var CurlClient */
    private $curlClient;

    /** @var JsonSerializer */
    private $json;

    /**
     * CurlClient constructor.
     *
     * @param CurlClient|null $curlClient
     * @param JsonSerializer|null $json
     */
    public function __construct(
        \Magento\TestFramework\TestCase\HttpClient\CurlClient $curlClient = null,
        \Magento\TestFramework\Helper\JsonSerializer $json = null
    ) {
        $objectManager = Bootstrap::getObjectManager();
        $this->curlClient = $curlClient ?: $objectManager->get(CurlClient::class);
        $this->json = $json ?: $objectManager->get(JsonSerializer::class);
    }

    /**
     * Perform HTTP POST request for query
     *
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array|string|int|float|bool
     * @throws \Exception
     */
    public function postQuery(string $query, array $variables = [], string $operationName = '', array $headers = [])
    {
        $url = $this->getEndpointUrl();
        $headers = array_merge($headers, ['Accept: application/json', 'Content-Type: application/json']);
        $requestArray = [
            'query' => $query,
            'variables' => empty($variables) ? $variables : null,
            'operationName' => empty($operationName) ? $operationName : null
        ];
        $postData = $this->json->jsonEncode($requestArray);

        $responseBody = $this->curlClient->post($url, $postData, $headers);
        $responseBodyArray = $this->json->jsonDecode($responseBody);

        if (isset($responseBodyArray['errors'])) {
            $errorMessage = '';
            if (is_array($responseBodyArray['errors'])) {
                foreach ($responseBodyArray['errors'] as $error) {
                    if (isset($error['message'])) {
                        $errorMessage .= $error['message'] . PHP_EOL;
                    }
                }
                throw new \Exception('GraphQL response contains errors: ' . $errorMessage);
            }
            throw new \Exception('GraphQL responded with an unknown error: ' . $responseBody);
        } elseif (!isset($responseBodyArray['data'])) {
            throw new \Exception('Unknown GraphQL response body: ' . $responseBody);
        }

        return $responseBodyArray['data'];
    }

    /**
     * @return string resource URL
     * @throws \Exception
     */
    public function getEndpointUrl()
    {
        return rtrim(TESTS_BASE_URL, '/') . '/graphql';
    }
}
