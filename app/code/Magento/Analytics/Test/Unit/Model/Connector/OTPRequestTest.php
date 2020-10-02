<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Analytics\Model\Connector\OTPRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * A unit test for testing of the representation of a 'OTP' request.
 */
class OTPRequestTest extends TestCase
{
    /**
     * @var OTPRequest
     */
    private $subject;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var ClientInterface|MockObject
     */
    private $httpClientMock;

    /**
     * @var AnalyticsToken|MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ResponseResolver|MockObject
     */
    private $responseResolverMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->httpClientMock = $this->getMockForAbstractClass(ClientInterface::class);

        $this->analyticsTokenMock = $this->createMock(AnalyticsToken::class);

        $this->responseResolverMock = $this->createMock(ResponseResolver::class);

        $this->subject = new OTPRequest(
            $this->analyticsTokenMock,
            $this->httpClientMock,
            $this->configMock,
            $this->responseResolverMock,
            $this->loggerMock
        );
    }

    /**
     * Returns test parameters for request.
     *
     * @return array
     */
    private function getTestData()
    {
        return [
            'otp' => 'thisisotp',
            'url' => 'http://www.mystore.com',
            'access-token' => 'thisisaccesstoken',
            'method' => ZendClient::POST,
            'body'=> ['access-token' => 'thisisaccesstoken','url' => 'http://www.mystore.com'],
        ];
    }

    /**
     * @return void
     */
    public function testCallSuccess()
    {
        $data = $this->getTestData();

        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($data['access-token']);

        $this->configMock
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body']
            )
            ->willReturn(new \Zend_Http_Response(201, []));
        $this->responseResolverMock->expects($this->once())
            ->method('getResult')
            ->willReturn($data['otp']);

        $this->assertEquals(
            $data['otp'],
            $this->subject->call()
        );
    }

    /**
     * @return void
     */
    public function testCallNoAccessToken()
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);

        $this->httpClientMock->expects($this->never())
            ->method('request');

        $this->assertFalse($this->subject->call());
    }

    /**
     * @return void
     */
    public function testCallNoOtp()
    {
        $data = $this->getTestData();

        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($data['access-token']);

        $this->configMock
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body']
            )
            ->willReturn(new \Zend_Http_Response(0, []));

        $this->responseResolverMock->expects($this->once())
            ->method('getResult')
            ->willReturn(false);

        $this->loggerMock->expects($this->once())
            ->method('warning');

        $this->assertFalse($this->subject->call());
    }
}
