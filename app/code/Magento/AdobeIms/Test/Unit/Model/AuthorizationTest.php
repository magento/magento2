<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Laminas\Uri\Uri;
use Magento\AdobeIms\Model\Authorization;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Stdlib\Parameters;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    private const AUTH_URL = 'https://adobe-login-url.com/authorize' .
    '?client_id=AdobeCommerceIMS' .
    '&redirect_uri=https://magento-instance.local/imscallback/' .
    '&locale=en_US' .
    '&scope=openid,creative_sdk,email,profile,additional_info,additional_info.roles' .
    '&response_type=code';

    private const AUTH_URL_ERROR = 'https://adobe-login-url.com/authorize?error=invalid_scope';

    private const REDIRECT_URL = 'https://magento-instance.local';

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Authorization
     */
    private $authorizationUrl;
    /**
     * @var Parameters|\PHPUnit\Framework\MockObject\MockObject
     */
    private mixed $parametersMock;
    /**
     * @var Parameters|\PHPUnit\Framework\MockObject\MockObject
     */
    private mixed $uriMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $imsConfigMock = $this->createMock(ConfigInterface::class);
        $imsConfigMock
            ->method('getAuthUrl')
            ->willReturn(self::AUTH_URL);
        $this->curlFactory = $this->createMock(CurlFactory::class);
        $this->parametersMock = $this->createMock(Parameters::class);
        $this->uriMock = $this->createMock(Uri::class);
        $urlParts = [];
        $url = self::AUTH_URL;
        $this->uriMock->expects($this->any())
            ->method('parse')
            ->willReturnCallback(
                function ($url) use (&$urlParts) {
                    $urlParts = parse_url($url);
                }
            );
        $this->uriMock->expects($this->any())
            ->method('getHost')
            ->willReturnCallback(
                function () use (&$urlParts) {
                    return array_key_exists('host', $urlParts) ? $urlParts['host'] : '';
                }
            );
        $this->uriMock->expects($this->any())
            ->method('getQuery')
            ->willReturnCallback(
                function () {
                    return 'callback=' . self::REDIRECT_URL;
                }
            );
        $this->parametersMock->method('fromString')
            ->with('callback=' . self::REDIRECT_URL)
            ->willReturnSelf();
        $this->parametersMock->method('toArray')
            ->willReturn([
                'redirect_uri' => self::REDIRECT_URL
            ]);
        $this->authorizationUrl = $objectManagerHelper->getObject(
            Authorization::class,
            [
                'curlFactory' => $this->curlFactory,
                'imsConfig' => $imsConfigMock,
                'parameters' => $this->parametersMock,
                'uri' => $this->uriMock
            ]
        );
    }

    /**
     * Test IMS host belongs to correct project
     */
    public function testAuthUrlValidateImsHostBelongsToCorrectProject(): void
    {
        $curlMock = $this->createMock(Curl::class);
        $curlMock->method('getHeaders')
            ->willReturn(['location' => self::AUTH_URL]);
        $curlMock->method('getStatus')
            ->willReturn(302);

        $this->curlFactory->method('create')
            ->willReturn($curlMock);

        $this->assertEquals($this->authorizationUrl->getAuthUrl(), self::AUTH_URL);
    }

    /**
     * Test auth throws exception code when response code is 200
     */
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
        $this->authorizationUrl->getAuthUrl();
    }

    /**
     * Test auth throws exception code when response contains error
     */
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
        $this->authorizationUrl->getAuthUrl();
    }
}
