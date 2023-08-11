<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
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
