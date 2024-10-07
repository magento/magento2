<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Analytics\Model\Connector\NotifyDataChangedCommand;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NotifyDataChangedCommandTest extends TestCase
{
    /**
     * @var NotifyDataChangedCommand
     */
    private $notifyDataChangedCommand;

    /**
     * @var AnalyticsToken|MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ClientInterface|MockObject
     */
    private $httpClientMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    public $configMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->analyticsTokenMock =  $this->createMock(AnalyticsToken::class);

        $this->httpClientMock =  $this->getMockForAbstractClass(ClientInterface::class);

        $this->configMock =  $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->loggerMock =  $this->getMockForAbstractClass(LoggerInterface::class);
        $successHandler = $this->getMockBuilder(ResponseHandlerInterface::class)
            ->getMockForAbstractClass();
        $successHandler->method('handleResponse')
            ->willReturn(true);
        $serializerMock = $this->createMock(Json::class);
        $serializerMock
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
        $this->configMock
            ->method('getValue')
            ->willReturn($configVal);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_201);
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                Request::METHOD_POST,
                $configVal,
                ['access-token' => $token, 'url' => $configVal]
            )->willReturn($response);
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
