<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Analytics\Model\Connector\UpdateCommand;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Config\Model\Config;
use Magento\Framework\FlagManager;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;

/**
 * Class SignUpCommandTest
 */
class UpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateCommand
     */
    private $updateCommand;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    public $configMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    protected function setUp()
    {
        $this->analyticsTokenMock =  $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientMock =  $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock =  $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock =  $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->flagManagerMock =  $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock =  $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateCommand = new UpdateCommand(
            $this->analyticsTokenMock,
            $this->httpClientMock,
            $this->configMock,
            $this->loggerMock,
            $this->flagManagerMock
        );
    }

    public function testExecuteSuccess()
    {
        $url = "old.localhost.com";
        $configVal = "Config val";
        $token = "Secret token!";
        $requestJson = sprintf('{"url":"%s","new-url":"%s","access-token":"%s"}', $url, $configVal, $token);
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);

        $this->configMock->expects($this->any())
            ->method('getConfigDataValue')
            ->willReturn($configVal);

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(BaseUrlConfigPlugin::OLD_BASE_URL_FLAG_CODE)
            ->willReturn($url);

        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                ZendClient::PUT,
                $configVal,
                $requestJson,
                ['Content-Type: application/json']
            )->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(201);

        $this->assertTrue($this->updateCommand->execute());
    }

    public function testExecuteWithoutToken()
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);

        $this->assertFalse($this->updateCommand->execute());
    }
}
