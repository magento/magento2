<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector;

/**
 * A unit test for testing of the representation of a 'OTP' request.
 */
class OTPRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Analytics\Model\Connector\OTPRequest
     */
    private $subject;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \Zend_Http_Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Analytics\Model\Connector\Http\ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var \Magento\Analytics\Model\AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(
            \Psr\Log\LoggerInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->configMock = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->responseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->httpClientMock = $this->getMockBuilder(
            \Magento\Analytics\Model\Connector\Http\ClientInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->analyticsTokenMock = $this->getMockBuilder(
            \Magento\Analytics\Model\AnalyticsToken::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\Model\Connector\OTPRequest::class,
            [
                'analyticsToken' => $this->analyticsTokenMock,
                'config' => $this->configMock,
                'httpClient' => $this->httpClientMock,
                'logger' => $this->loggerMock
            ]
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
            'headers' => ['Content-Type: application/json'],
            'method' => \Magento\Framework\HTTP\ZendClient::POST,
            'body'=> '{"access-token":"thisisaccesstoken","url":"http:\/\/www.mystore.com"}',
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

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body'],
                $data['headers']
            )
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->any())
            ->method('getStatus')
            ->willReturn(201);
        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn('{"otp": "' . $data['otp'] . '"}');

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
    public function testCallTransportFailure()
    {
        $data = $this->getTestData();

        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($data['access-token']);

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body'],
                $data['headers']
            )
            ->willReturn(false);

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

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body'],
                $data['headers']
            )
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->any())
            ->method('getStatus')
            ->willReturn(409);

        $this->loggerMock->expects($this->once())
            ->method('warning');

        $this->assertFalse($this->subject->call());
    }

    /**
     * @return void
     */
    public function testCallException()
    {
        $data = $this->getTestData();

        $exception = new \Exception('Test Exception');

        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($data['access-token']);

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($data['url']);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body'],
                $data['headers']
            )
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->assertFalse($this->subject->call());
    }
}
