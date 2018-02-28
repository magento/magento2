<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\TestCase\GraphQl;

use Magento\TestFramework\TestCase\HttpClient\CurlClient;
use Magento\TestFramework\Helper\JsonSerializer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

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

        if (!is_array($responseBodyArray)) {
            throw new \Exception('Unknown GraphQL response body: ' . json_encode($responseBodyArray));
        }

        $this->processErrors($responseBodyArray);

        if (!isset($responseBodyArray['data'])) {
            throw new \Exception('Unknown GraphQL response body: ' . json_encode($responseBodyArray));
        } else {
            return $responseBodyArray['data'];
        }
    }

    /**
     * @param array $responseBodyArray
     * @throws \Exception
     */
    private function processErrors($responseBodyArray)
    {
        if (isset($responseBodyArray['errors'])) {
            $errorMessage = '';
            if (is_array($responseBodyArray['errors'])) {
                foreach ($responseBodyArray['errors'] as $error) {
                    if (isset($error['message'])) {
                        $errorMessage .= $error['message'] . PHP_EOL;
                        if (isset($error['debugMessage'])) {
                            $errorMessage .= $error['debugMessage'] . PHP_EOL;
                        }
                    }
                    if (isset($error['trace'])) {
                        $traceString = $error['trace'];
                        TestCase::assertNotEmpty($traceString, "trace is empty");
                    }
                }

                throw new \Exception('GraphQL response contains errors: ' . $errorMessage);
            }
            throw new \Exception('GraphQL responded with an unknown error: ' . json_encode($responseBodyArray));
        }
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
