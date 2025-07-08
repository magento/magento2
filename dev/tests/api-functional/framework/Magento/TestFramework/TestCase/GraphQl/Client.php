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
    public const GRAPHQL_METHOD_POST = 'POST';
    /**#@-*/

    private const SET_COOKIE_HEADER_NAME = 'Set-Cookie';

    /** @var CurlClient */
    private $curlClient;

    /** @var JsonSerializer */
    private $json;

    /**
     * @param CurlClient|null $curlClient
     * @param JsonSerializer|null $json
     */
    public function __construct(
        ?\Magento\TestFramework\TestCase\HttpClient\CurlClient $curlClient = null,
        ?\Magento\TestFramework\Helper\JsonSerializer $json = null
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
    public function post(string $query, array $variables = [], string $operationName = '', array $headers = [])
    {
        $url = $this->getEndpointUrl();
        $headers = array_merge($headers, ['Accept: application/json', 'Content-Type: application/json']);
        $requestArray = [
            'query' => $query,
            'variables' => !empty($variables) ? $variables : null,
            'operationName' => !empty($operationName) ? $operationName : null
        ];
        $postData = $this->json->jsonEncode($requestArray);
        try {
            $responseBody = $this->curlClient->post($url, $postData, $headers);
        } catch (\Exception $e) {
            // if response code > 400 then response is the exception message
            $responseBody = $e->getMessage();
        }

        return $this->processResponse($responseBody);
    }

    /**
     * Perform HTTP GET request for query
     *
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return mixed
     * @throws \Exception
     */
    public function get(string $query, array $variables = [], string $operationName = '', array $headers = [])
    {
        $url = $this->getEndpointUrl();
        $requestArray = [
            'query' => $query,
            'variables' => $variables ? $this->json->jsonEncode($variables) : null,
            'operationName' => $operationName ? $operationName : null
        ];
        array_filter($requestArray);

        try {
            $responseBody = $this->curlClient->get($url, $requestArray, $headers);
        } catch (\Exception $e) {
            // if response code > 400 then response is the exception message
            $responseBody = $e->getMessage();
        }
        return $this->processResponse($responseBody);
    }

    /**
     * Process response from GraphQL server.
     *
     * @param string $response
     * @param array $responseHeaders
     * @param array $responseCookies
     * @return mixed
     * @throws \Exception
     */
    private function processResponse(string $response, array $responseHeaders = [], array $responseCookies = [])
    {
        $responseArray = null;
        try {
            $responseArray = $this->json->jsonDecode($response);
        } catch (\Exception $exception) {
            // Note: We don't care about this exception because we have error checking bellow if it fails to decode.
        }
        if (!is_array($responseArray)) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Unknown GraphQL response body: ' . $response);
        }
        $this->processErrors($responseArray, $responseHeaders, $responseCookies);
        if (!isset($responseArray['data'])) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Unknown GraphQL response body: ' . $response);
        }
        return $responseArray['data'];
    }

    /**
     * Perform HTTP GET request, return response data and headers
     *
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @param bool $flushCookies
     *
     * @return array
     */
    public function getWithResponseHeaders(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = [],
        bool $flushCookies = false
    ): array {
        $url = $this->getEndpointUrl();
        $requestArray = [
            'query' => $query,
            'variables' => $variables ? $this->json->jsonEncode($variables) : null,
            'operationName' => !empty($operationName) ? $operationName : null
        ];
        array_filter($requestArray);

        $response = $this->curlClient->getWithFullResponse($url, $requestArray, $headers, $flushCookies);
        $responseHeaders = !empty($response['header']) ? $this->processResponseHeaders($response['header']) : [];
        $responseCookies = !empty($response['header']) ? $this->processResponseCookies($response['header']) : [];
        $responseBody = $this->processResponse($response['body'], $responseHeaders, $responseCookies);

        return ['headers' => $responseHeaders, 'body' => $responseBody, 'cookies' => $responseCookies];
    }

    /**
     * Perform HTTP POST request, return response data and headers
     *
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @param bool $flushCookies
     *
     * @return array
     */
    public function postWithResponseHeaders(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = [],
        bool $flushCookies = false
    ): array {
        $url = $this->getEndpointUrl();
        $headers = array_merge($headers, ['Accept: application/json', 'Content-Type: application/json']);
        $requestArray = [
            'query' => $query,
            'variables' => !empty($variables) ? $variables : null,
            'operationName' => !empty($operationName) ? $operationName : null
        ];
        $postData = $this->json->jsonEncode($requestArray);

        $response = $this->curlClient->postWithFullResponse($url, $postData, $headers, $flushCookies);
        $responseHeaders = !empty($response['header']) ? $this->processResponseHeaders($response['header']) : [];
        $responseCookies = !empty($response['header']) ? $this->processResponseCookies($response['header']) : [];
        $responseBody = $this->processResponse($response['body'], $responseHeaders, $responseCookies);

        return ['headers' => $responseHeaders, 'body' => $responseBody, 'cookies' => $responseCookies];
    }

    /**
     * Process errors.
     *
     * @param array $responseBodyArray
     * @param array $responseHeaders
     * @param array $responseCookies
     * @return void
     * @throws ResponseContainsErrorsException
     */
    private function processErrors($responseBodyArray, array $responseHeaders = [], array $responseCookies = [])
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

                throw new ResponseContainsErrorsException(
                    'GraphQL response contains errors: ' . $errorMessage . "\n" . var_export($responseBodyArray, true),
                    $responseBodyArray,
                    null,
                    0,
                    $responseHeaders,
                    $responseCookies
                );
            }
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('GraphQL responded with an unknown error: ' . json_encode($responseBodyArray));
        }
    }

    /**
     * Get endpoint url
     *
     * @return string resource URL
     * @throws \Exception
     */
    public function getEndpointUrl()
    {
        return rtrim(TESTS_BASE_URL, '/') . '/graphql';
    }

    /**
     * Parse response headers into associative array
     *
     * @param string $headers
     * @return array
     */
    private function processResponseHeaders(string $headers): array
    {
        $headersArray = [];

        $headerLines = preg_split('/((\r?\n)|(\r\n?))/', $headers);
        foreach ($headerLines as $headerLine) {
            $headerParts = preg_split('/: /', $headerLine, 2);
            if (count($headerParts) == 2) {
                $headerName = trim($headerParts[0]);
                if ($headerName === self::SET_COOKIE_HEADER_NAME) {
                    if (!isset($headersArray[self::SET_COOKIE_HEADER_NAME])) {
                        $headersArray[self::SET_COOKIE_HEADER_NAME] = [];
                    }
                    $headersArray[self::SET_COOKIE_HEADER_NAME][] = trim($headerParts[1]);
                } else {
                    $headersArray[$headerName] = trim($headerParts[1]);
                }
            } elseif (preg_match('/HTTP\/[\.0-9]+/', $headerLine)) {
                $headersArray[trim('Status-Line')] = trim($headerLine);
            }
        }

        return $headersArray;
    }

    /**
     * Prepare separate array of cookies.
     *
     * @param string $headers
     * @return array
     */
    private function processResponseCookies(string $headers): array
    {
        $cookiesArray = [];
        $headers = preg_split('/((\r?\n)|(\r\n?))/', $headers);
        foreach ($headers as $header) {
            if (strpos($header, 'Set-Cookie:') === 0) {
                $cookie = preg_split('/: /', $header, 2);
                if (isset($cookie[1]) && !empty($cookie[1])) {
                    $cookiesArray[] = $cookie[1];
                }
            }
        }
        return $cookiesArray;
    }
}
