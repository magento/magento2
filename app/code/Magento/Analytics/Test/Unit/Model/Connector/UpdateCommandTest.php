<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Analytics\Model\Connector\UpdateCommand;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\HTTP\ZendClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UpdateCommandTest extends TestCase
{
    /**
     * @var UpdateCommand
     */
    private $updateCommand;

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

    /**
     * @var FlagManager|MockObject
     */
    private $flagManagerMock;

    /**
     * @var ResponseResolver|MockObject
     */
    private $responseResolverMock;

    protected function setUp(): void
    {
        $this->analyticsTokenMock =  $this->createMock(AnalyticsToken::class);

        $this->httpClientMock =  $this->getMockForAbstractClass(ClientInterface::class);

        $this->configMock =  $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->loggerMock =  $this->getMockForAbstractClass(LoggerInterface::class);

        $this->flagManagerMock =  $this->createMock(FlagManager::class);

        $this->responseResolverMock = $this->createMock(ResponseResolver::class);

        $this->updateCommand = new UpdateCommand(
            $this->analyticsTokenMock,
            $this->httpClientMock,
            $this->configMock,
            $this->loggerMock,
            $this->flagManagerMock,
            $this->responseResolverMock
        );
    }

    public function testExecuteSuccess()
    {
        $url = "old.localhost.com";
        $configVal = "Config val";
        $token = "Secret token!";
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);

        $this->configMock
            ->method('getValue')
            ->willReturn($configVal);

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE)
            ->willReturn($url);

        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                ZendClient::PUT,
                $configVal,
                [
                    'url' => $url,
                    'new-url' => $configVal,
                    'access-token' => $token
                ]
            )->willReturn(new \Zend_Http_Response(200, []));

        $this->responseResolverMock->expects($this->once())
            ->method('getResult')
            ->willReturn(true);

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
