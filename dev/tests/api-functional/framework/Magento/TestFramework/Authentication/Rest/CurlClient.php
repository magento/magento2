<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\Authentication\Rest;

/**
 * Custom Client implementation for cURL
 */
class CurlClient extends \Magento\Framework\HTTP\ClientFactory
{

    /**
     * Fetch api response using curl client factory
     *
     * @param string $url
     * @param array $requestBody
     * @param array $headers
     * @param string $method
     * @return string
     */
    public function retrieveResponse(
        string $url,
        array $requestBody,
        array $headers,
        string $method = 'POST'
    ): string {
        $httpClient = $this->create();
        $httpClient->setHeaders($headers);
        $httpClient->setOption(CURLOPT_FAILONERROR, true);
        if ($method === 'GET') {
            $httpClient->get($url);
        } else {
            $httpClient->post($url, $requestBody);
        }

        return $httpClient->getBody();
    }
}
