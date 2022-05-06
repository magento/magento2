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

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $adminImsConfigMock = $this->createMock(ImsConfig::class);
        $adminImsConfigMock
            ->method('getAuthUrl')
            ->willReturn(self::AUTH_URL);

        $this->curlFactory = $this->createMock(CurlFactory::class);

        $json = $this->createMock(Json::class);

        $this->adminImsConnection = $objectManagerHelper->getObject(
            ImsConnection::class,
            [
                'curlFactory' => $this->curlFactory,
                'adminImsConfig' => $adminImsConfigMock,
                'json' => $json,
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
}
