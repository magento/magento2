<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model;

use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Shipping\Model\Shipment\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ups\Model\UpsAuth;
use PHPUnit\Framework\TestCase;

class UpsAuthTest extends TestCase
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
     * @var UpsAuth
     */
    private $upsAuth;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->asyncHttpClientMock = Bootstrap::getObjectManager()->get(AsyncClientInterface::class);
        $this->upsAuth = $this->objectManager->create(
            UpsAuth::class,
            ['asyncHttpClient' => $this->asyncHttpClientMock]
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetAccessToken()
    {
        // Prepare test data
        $clientId = 'user';
        $clientSecret = 'pass';
        $clientUrl = 'https://wwwcie.ups.com/security/v1/oauth/token';

        // Prepare the expected response data
        $expectedAccessToken = 'abcdefghijklmnop';
        $responseData = '{
            "token_type":"Bearer",
            "issued_at":"1690460887368",
            "client_id":"abcdef",
            "access_token":"abcdefghijklmnop",
            "expires_in":"14399",
            "status":"approved"
            }';

        // Mock the HTTP client behavior to return a mock response
        $request = new Request(
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'x-merchant-id' => 'string',
                'Authorization' => 'Basic ' . base64_encode("$clientId:$clientSecret")
            ],
        );

        $this->asyncHttpClientMock->nextResponses(
            [
                new Response(
                    200,
                    [],
                    $responseData
                )
            ]
        );

        // Call the getAccessToken method and assert the result
        $accessToken = $this->upsAuth->getAccessToken($clientId, $clientSecret, $clientUrl);
        $this->assertEquals($expectedAccessToken, $accessToken);
    }
}
