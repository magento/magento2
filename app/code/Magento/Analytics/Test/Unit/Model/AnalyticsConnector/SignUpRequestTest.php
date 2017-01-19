<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\AnalyticsConnector;

use Magento\Config\Model\Config;
use Magento\Analytics\Model\AnalyticsConnector\SignUpRequest;
use Magento\Framework\HTTP\ZendClientFactory as HttpClientFactory;
use Magento\Framework\HTTP\ZendClient as HttpClient;
use Zend_Http_Response as HttpResponse;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;


class SignUpRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var HttpClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientFactoryMock;

    /**
     * @var HttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpResponseMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var SignUpRequest
     */
    private $signUpRequest;

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientFactoryMock = $this->getMockBuilder(HttpClientFactory::class)
            ->setMethods(['create'])
            ->getMock();
        $this->httpClientMock = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $this->httpResponseMock = $this->getMockBuilder(HttpResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->signUpRequest = $objectManagerHelper->getObject(
            SignUpRequest::class,
            [
                'config' => $this->configMock,
                'httpClientFactory' => $this->httpClientFactoryMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testCallSuccess()
    {
        $requestRawData = json_encode(
            [
                'token' => 'IntegrationToken',
                'url' => 'magento-url'
            ]
        );
        $responseRawData = json_encode(
                        [
                'token' => 'MAToken'
            ]
        );
        $this->configMock->expects($this->exactly(2))
            ->method('getConfigDataValue')
            ->willReturnMap(
                [
                    ['analytics/url/signup', null, null, 'ma-signup-url'],
                    [Store::XML_PATH_UNSECURE_BASE_URL, null, null, 'magento-url']
                ]
            );
        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);
        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with('ma-signup-url')
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setRawData')
            ->with($requestRawData)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with(HttpClient::POST)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($this->httpResponseMock);
        $this->httpResponseMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);
        $this->httpResponseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($responseRawData);
        $this->assertEquals('MAToken', $this->signUpRequest->call('IntegrationToken'));
    }

    public function testCallFailure_EmptyResponse()
    {
        $requestRawData = json_encode(
            [
                'token' => 'IntegrationToken',
                'url' => 'magento-url'
            ]
        );
        $this->configMock->expects($this->exactly(2))
            ->method('getConfigDataValue')
            ->willReturnMap(
                [
                    ['analytics/url/signup', null, null, 'ma-signup-url'],
                    [Store::XML_PATH_UNSECURE_BASE_URL, null, null, 'magento-url']
                ]
            );
        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);
        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with('ma-signup-url')
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setRawData')
            ->with($requestRawData)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with(HttpClient::POST)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($this->httpResponseMock);
        $this->httpResponseMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(300);
        $this->httpResponseMock->expects($this->never())
            ->method('getBody');
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('The attempt of subscription was unsuccessful on step sign-up.');
        $this->assertEquals(false, $this->signUpRequest->call('IntegrationToken'));
    }

    public function testCallFailure_Exception()
    {
        $exception = new \Exception('exception');
        $requestRawData = json_encode(
            [
                'token' => 'IntegrationToken',
                'url' => 'magento-url'
            ]
        );
        $this->configMock->expects($this->exactly(2))
            ->method('getConfigDataValue')
            ->willReturnMap(
                [
                    ['analytics/url/signup', null, null, 'ma-signup-url'],
                    [Store::XML_PATH_UNSECURE_BASE_URL, null, null, 'magento-url']
                ]
            );
        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);
        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with('ma-signup-url')
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setRawData')
            ->with($requestRawData)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with(HttpClient::POST)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willThrowException($exception);
        $this->httpResponseMock->expects($this->never())
            ->method('getStatus');
        $this->httpResponseMock->expects($this->never())
            ->method('getBody');
        $this->loggerMock->expects($this->never())
            ->method('warning');
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->assertEquals(false, $this->signUpRequest->call('IntegrationToken'));
    }
}
