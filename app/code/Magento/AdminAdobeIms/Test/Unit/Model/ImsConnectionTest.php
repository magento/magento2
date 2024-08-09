<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Model;

use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class ImsConnectionTest extends TestCase
{
    private const AUTH_URL = 'https://adobe-login-url.com/authorize' .
        '?client_id=AdobeCommerceIMS' .
        '&redirect_uri=https://magento-instance.local/imscallback/' .
        '&locale=en_US' .
        '&scope=openid,creative_sdk,email,profile,additional_info,additional_info.roles' .
        '&response_type=code';

    private const AUTH_URL_ERROR = 'https://adobe-login-url.com/authorize?error=invalid_scope';

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var ImsConnection
     */
    private $adminImsConnection;

    /**
     * @var Json|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $json;

    /**
     * @var ImsConfig|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $adminImsConfigMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->adminImsConfigMock = $this->createMock(ImsConfig::class);
        $this->adminImsConfigMock
            ->method('getAuthUrl')
            ->willReturn(self::AUTH_URL);

        $this->curlFactory = $this->createMock(CurlFactory::class);

        $this->json = $this->createMock(Json::class);

        $this->adminImsConnection = $objectManagerHelper->getObject(
            ImsConnection::class,
            [
                'curlFactory' => $this->curlFactory,
                'adminImsConfig' => $this->adminImsConfigMock,
                'json' => $this->json,
            ]
        );
    }

    public function testAuthThrowsExceptionWhenResponseCodeIs200(): void
    {
        $curlMock = $this->createMock(Curl::class);
        $curlMock->method('getHeaders')
            ->willReturn(['location' => self::AUTH_URL]);
        $curlMock->method('getStatus')
            ->willReturn(200);

        $this->curlFactory->method('create')
            ->willReturn($curlMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not get a valid response from Adobe IMS Service.');
        $this->adminImsConnection->auth();
    }

    public function testAuthThrowsExceptionWhenResponseContainsError(): void
    {
        $curlMock = $this->createMock(Curl::class);
        $curlMock->method('getHeaders')
            ->willReturn(['location' => self::AUTH_URL_ERROR]);
        $curlMock->method('getStatus')
            ->willReturn(302);

        $this->curlFactory->method('create')
            ->willReturn($curlMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not connect to Adobe IMS Service: invalid_scope.');
        $this->adminImsConnection->auth();
    }

    /**
     * Token validate test
     *
     * @return void
     */
    public function testValidateToken(): void
    {
        $this->adminImsConfigMock->method('getValidateTokenUrl')
            ->willReturn('https://ims-na1-stg1.adobelogin.com/ims/validate_token/v1');
        $this->adminImsConfigMock->method('getApiKey')
            ->willReturn('api_key');
        $curlMock = $this->createMock(Curl::class);
        $curlMock->expects($this->once())
            ->method('post')
            ->willReturn(null);
        $curlMock->method('getBody')
            ->willReturn('{"valid":1}');
        $curlMock->method('getStatus')
            ->willReturn(302);
        $this->json->method('unserialize')
            ->with('{"valid":1}')
            ->willReturn(['valid' => true]);
        $this->curlFactory->method('create')
            ->willReturn($curlMock);
        $this->assertTrue($this->adminImsConnection->validateToken('valid_token'));
    }
}
