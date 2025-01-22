<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Authentication\Rest;

use OAuth\Common\Http\Uri\UriInterface;

/**
 * Custom Client implementation for cURL
 */
class CurlClient extends \OAuth\Common\Http\Client\CurlClient
{
    /**
     * @inheritdoc
     */
    public function retrieveResponse(
        UriInterface $endpoint,
        $requestBody,
        array $extraHeaders = [],
        $method = 'POST'
    ) {
        $this->setCurlParameters([CURLOPT_FAILONERROR => true]);
        return parent::retrieveResponse($endpoint, $requestBody, $extraHeaders, $method);
    }

    /**
     * @inheritdoc
     */
    public function normalizeHeaders($headers): array
    {
        $normalizeHeaders = [];
        foreach ($headers as $key => $val) {
            $val = ucfirst(strtolower($key)) . ': ' . $val;
            $normalizeHeaders[$key] = $val;
        }

        return $normalizeHeaders;
    }
}
