<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;
use Magento\Analytics\Model\Connector\NotifyDataChangedCommand;
use Magento\Analytics\Model\Connector\Http\ClientInterface;

class NotifyDataChangedCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NotifyDataChangedCommand
     */
    private $notifyDataChangedCommand;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public $configMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->analyticsTokenMock =  $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientMock =  $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock =  $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock =  $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $successHandler = $this->getMockBuilder(\Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $successHandler->method('handleResponse')
            ->willReturn(true);

        $this->notifyDataChangedCommand = new NotifyDataChangedCommand(
            $this->analyticsTokenMock,
            $this->httpClientMock,
            $this->configMock,
            new ResponseResolver(new JsonConverter(), [201 => $successHandler]),
            $this->loggerMock
        );
    }

    public function testExecuteSuccess()
    {
        $configVal = "Config val";
        $token = "Secret token!";
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($configVal);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                ZendClient::POST,
                $configVal,
                ['access-token' => $token, 'url' => $configVal]
            )->willReturn(new \Zend_Http_Response(201, []));
        $this->assertTrue($this->notifyDataChangedCommand->execute());
    }

    public function testExecuteWithoutToken()
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);
        $this->httpClientMock->expects($this->never())
            ->method('request');
        $this->assertFalse($this->notifyDataChangedCommand->execute());
    }
}
