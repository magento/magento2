<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\AnalyticsConnector;

use Magento\Analytics\Model\AnalyticsConnector\SignUpCommand;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Config\Model\Config;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;
use Magento\Analytics\Model\AnalyticsApiUserProvider;
use Magento\Analytics\Model\TokenGenerator;
use Magento\Store\Model\Store;

/**
 * Class SignUpCommandTest
 */
class SignUpCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SignUpCommand
     */
    private $signUpCommand;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ZendClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientFactoryMock;

    /**
     * @var ZendClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var AnalyticsApiUserProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsApiUserProviderMock;

    /**
     * @var TokenGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenGeneratorMock;

    /**
     * @var \Zend_Http_Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    protected function setUp()
    {
        $this->analyticsTokenMock =  $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientFactoryMock = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientMock = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->analyticsApiUserProviderMock = $this->getMockBuilder(AnalyticsApiUserProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenGeneratorMock = $this->getMockBuilder(TokenGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signUpCommand = new SignUpCommand(
            $this->configMock,
            $this->httpClientFactoryMock,
            $this->analyticsTokenMock,
            $this->loggerMock,
            $this->analyticsApiUserProviderMock,
            $this->tokenGeneratorMock
        );
    }

    public function testExecuteWithoutToken()
    {
        $this->analyticsApiUserProviderMock->expects($this->any())
            ->method('getToken')
            ->willReturn(false);
        $this->tokenGeneratorMock->expects($this->once())
            ->method('execute');
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('The attempt of subscription was unsuccessful on step generate token.');
        $this->assertFalse($this->signUpCommand->execute());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExecute($url, $maTokent, $responseBody)
    {
        $this->getApiToken();
        $this->configMock->expects($this->at(0))
            ->method('getConfigDataValue')
            ->with(Store::XML_PATH_UNSECURE_BASE_URL)
            ->willReturn($url);
        $this->prepareRequest($url);
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($this->responseMock);
        $this->responseMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);
        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($responseBody);
        $this->analyticsTokenMock->expects($this->once())
            ->method('setToken')
            ->with($maTokent);
        $this->assertTrue($this->signUpCommand->execute());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExecuteWithHttpClientException($url)
    {
        $this->getApiToken();
        $this->configMock->expects($this->at(0))
            ->method('getConfigDataValue')
            ->with(Store::XML_PATH_UNSECURE_BASE_URL)
            ->willReturn($url);
        $this->prepareRequest($url);
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Zend_Http_Client_Exception("Connection Error!"));
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with("The attempt of subscription was unsuccessful on step sign-up.");
        $this->assertFalse($this->signUpCommand->execute());
    }

    private function getApiToken()
    {
        $token = "Secret Token!";
        $this->analyticsApiUserProviderMock->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
    }

    private function prepareRequest($url)
    {
        $maEndPoint = "http://ma-api.service";
        $requestData = json_encode(
            [
                "token" => "Secret Token!",
                "url" => $url
            ]
        );
        $this->configMock->expects($this->at(1))
            ->method('getConfigDataValue')
            ->with(SignUpCommand::MA_SIGNUP_URL_PATH)
            ->willReturn($maEndPoint);
        $this->httpClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClientMock);
        $this->httpClientMock->expects($this->once())
            ->method('setUri')
            ->with($maEndPoint)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setRawData')
            ->with($requestData)
            ->willReturnSelf();
        $this->httpClientMock->expects($this->once())
            ->method('setMethod')
            ->with(\Zend_Http_Client::POST)
            ->willReturnSelf();
    }

    public function dataProvider()
    {
        $url = "http://localhost";
        $maTokent = "MA Secret Token";
        $responseBody = '{"token":"' . $maTokent . '"}';
        return [
            [$url, $maTokent, $responseBody]
        ];
    }
}
