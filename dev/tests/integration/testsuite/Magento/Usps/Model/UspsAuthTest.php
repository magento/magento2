<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Usps\Model;

use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Shipping\Model\Shipment\Request;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class UspsAuthTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AsyncClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $asyncHttpClientMock;

    /**
     * @var UspsAuth
     */
    private $uspsAuth;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->asyncHttpClientMock = Bootstrap::getObjectManager()->get(AsyncClientInterface::class);
        $this->uspsAuth = $this->objectManager->create(
            UspsAuth::class,
            ['asyncHttpClient' => $this->asyncHttpClientMock]
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Throwable
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetAccessToken()
    {
        // Prepare test data
        $clientId = 'test_user';
        $clientSecret = 'test_password';
        $clientUrl = 'https://apis-tem.usps.com/oauth2/v3/token';

        // Prepare the expected response data
        $expectedAccessToken = 'test_eyJraWQiOiJ5MmRGRGY3eDdFQkFsQXlob0RLYld2ejlNaWxHTzlnaEJZS2c3OV9zRko4IiwidHlw';
        $responseData = '{
            "access_token":"test_eyJraWQiOiJ5MmRGRGY3eDdFQkFsQXlob0RLYld2ejlNaWxHTzlnaEJZS2c3OV9zRko4IiwidHlw",
            "token_type":"Bearer",
            "issued_at": 1744028583433,
            "expires_in": 28799,
            "status": "approved",
            "scope": "prices shipments tracking labels payments international-labels",
            "issuer": "https://cat-keyc.usps.com/realms/USPS",
            "client_id": "test_client_id",
            "application_name": "Magento",
            "api_products": "[Shipping Version 3]",
            "public_key": "test_MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7v+2x1j3J4g"
            }';

        // Mock the HTTP client behavior to return a mock response
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials'
        ];

        $this->asyncHttpClientMock->nextResponses(
            [
                new Response(
                    200,
                    $headers,
                    $responseData
                )
            ]
        );

        // Call the getAccessToken method and assert the result
        $accessToken = $this->uspsAuth->getAccessToken($clientId, $clientSecret, $clientUrl);
        $this->assertEquals($expectedAccessToken, $accessToken);
    }
}
