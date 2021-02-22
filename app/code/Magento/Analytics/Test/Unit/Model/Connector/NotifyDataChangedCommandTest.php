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
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Analytics\Model\Connector\NotifyDataChangedCommand;
use Magento\Analytics\Model\Connector\Http\ClientInterface;

class NotifyDataChangedCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NotifyDataChangedCommand
     */
    private $notifyDataChangedCommand;

    /**
     * @var AnalyticsToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $httpClientMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public $configMock;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->analyticsTokenMock =  $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientMock =  $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock =  $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->loggerMock =  $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $successHandler = $this->getMockBuilder(\Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $successHandler->method('handleResponse')
            ->willReturn(true);
        $serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturn(['unserialized data']);
        $objectManager = new ObjectManager($this);
        $this->notifyDataChangedCommand = $objectManager->getObject(
            NotifyDataChangedCommand::class,
            [
                'analyticsToken' => $this->analyticsTokenMock,
                'httpClient' => $this->httpClientMock,
                'config' => $this->configMock,
                'responseResolver' => $objectManager->getObject(
                    ResponseResolver::class,
                    [
                        'converter' => $objectManager->getObject(
                            JsonConverter::class,
                            ['serializer' => $serializerMock]
                        ),
                        'responseHandlers' => [201 => $successHandler]
                    ]
                ),
                'logger' => $this->loggerMock
            ]
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
