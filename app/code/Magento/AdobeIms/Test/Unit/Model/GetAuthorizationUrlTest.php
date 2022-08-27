<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\Authorization;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class GetAuthorizationUrlTest extends TestCase
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
     * @var Authorization
     */
    private $authorizationUrl;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $imsConfigMock = $this->createMock(ConfigInterface::class);
        $imsConfigMock
            ->method('getAuthUrl')
            ->willReturn(self::AUTH_URL);
        $this->curlFactory = $this->createMock(CurlFactory::class);

        $this->authorizationUrl = $objectManagerHelper->getObject(
            Authorization::class,
            [
                'curlFactory' => $this->curlFactory,
                'imsConfig' => $imsConfigMock
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
        $this->authorizationUrl->getAuthUrl();
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
        $this->authorizationUrl->getAuthUrl();
    }
}
