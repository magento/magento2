<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestModuleFedex\Model;

/**
 * Mock Fedex rest client factory
 */
class MockCurlClient extends \Magento\Framework\HTTP\Client\Curl
{
    /**
     * @var MockResponseBodyLoader
     */
    private $mockResponseBodyLoader;

    /**
     * Oauth End point to get Access Token
     *
     * @var string
     */
    private const OAUTH_REQUEST_END_POINT = 'oauth/token';

    /**
     * REST end point for Rate API
     *
     * @var string
     */
    private const RATE_REQUEST_END_POINT = 'rate/v1/rates/quotes';

    /**
     * @param MockResponseBodyLoader $mockResponseBodyLoader
     */
    public function __construct(
        MockResponseBodyLoader $mockResponseBodyLoader,
    ) {
        $this->mockResponseBodyLoader = $mockResponseBodyLoader;
    }

    /**
     * Fetch mock Fedex rates
     *
     * @param string $url
     * @param array $request
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function post($url, $request): void
    {
        if (strpos($url, self::OAUTH_REQUEST_END_POINT) !== false) {
            $this->_responseBody = $this->mockResponseBodyLoader->loadForAuthRequest();
        }

        if (strpos($url, self::RATE_REQUEST_END_POINT) !== false) {
            $this->_responseBody = $this->mockResponseBodyLoader->loadForRestRequest(json_decode($request, true));
        }
    }
}
