<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\GetToken;
use Magento\AdobeIms\Model\OAuth\TokenResponse;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Get user token test
 */
class GetTokenTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var CurlFactory|MockObject
     */
    private $curlFactoryMock;

    /**
     * @var Json|MockObject
     */
    private $jsonMock;

    /**
     * @var TokenResponseInterfaceFactory|MockObject
     */
    private $tokenResponseFactoryMock;

    /**
     * @var GetToken $getToken
     */
    private $getToken;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
        $this->jsonMock = $this->createMock(Json::class);
        $this->tokenResponseFactoryMock = $this->createMock(TokenResponseInterfaceFactory::class);
        $this->getToken = new GetToken(
            $this->configMock,
            $this->curlFactoryMock,
            $this->jsonMock,
            $this->tokenResponseFactoryMock
        );
    }

    /**
     * Test save.
     */
    public function testExecute(): void
    {
        $curl = $this->createMock(Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('getTokenUrl')
            ->willReturn('http://www.some.url.com');
        $this->configMock->expects($this->once())
            ->method('getApiKey')
            ->willReturn('string');
        $this->configMock->expects($this->once())
            ->method('getPrivateKey')
            ->willReturn('string');
        $curl->expects($this->once())
            ->method('post')
            ->willReturn(null);

        $data = ['access_token' => 'string'];

        $this->jsonMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $tokenResponse = $this->createMock(TokenResponse::class);
        $this->tokenResponseFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $data])
            ->willReturn($tokenResponse);
        $this->assertEquals($tokenResponse, $this->getToken->execute('code'));
    }
}
