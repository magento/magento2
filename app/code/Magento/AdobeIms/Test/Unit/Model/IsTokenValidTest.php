<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\IsTokenValid;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class IsTokenValidTest extends TestCase
{
    /**
     * @var ConfigInterface|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;
    /**
     * @var CurlFactory|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $curlFactoryMock;
    /**
     * @var Json|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $jsonMock;
    /**
     * @var mixed|\PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var IsTokenValid
     */
    private IsTokenValid $isValidToken;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
        $this->jsonMock = $this->createMock(Json::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->isValidToken = new IsTokenValid(
            $this->curlFactoryMock,
            $this->configMock,
            $this->jsonMock,
            $this->logger
        );
    }

    /**
     * Test validate token
     */
    public function testValidateToken()
    {
        $curl = $this->createMock(Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('getValidateTokenUrl')
            ->willReturn('http://www.some.url.com');
        $curl->expects($this->once())
            ->method('post')
            ->willReturn(null);
        $curl->expects($this->exactly(2))
            ->method('getBody')
            ->willReturn(null);
        $data = ['valid' => 1];
        $this->jsonMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $this->assertTrue($this->isValidToken->validateToken('ftXdatRdsafga'));
    }
}
