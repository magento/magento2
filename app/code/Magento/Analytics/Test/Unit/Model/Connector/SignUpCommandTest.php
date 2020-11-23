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
use Magento\Analytics\Model\Connector\SignUpCommand;
use Magento\Analytics\Model\IntegrationManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Integration\Model\Oauth\Token as IntegrationToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SignUpCommandTest extends TestCase
{
    /**
     * @var SignUpCommand
     */
    private $signUpCommand;

    /**
     * @var AnalyticsToken|MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var IntegrationManager|MockObject
     */
    private $integrationManagerMock;

    /**
     * @var IntegrationToken|MockObject
     */
    private $integrationToken;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var ClientInterface|MockObject
     */
    private $httpClientMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ResponseResolver|MockObject
     */
    private $responseResolverMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->analyticsTokenMock =  $this->createMock(AnalyticsToken::class);
        $this->integrationManagerMock = $this->createMock(IntegrationManager::class);
        $this->integrationToken = $this->createMock(IntegrationToken::class);
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->httpClientMock = $this->getMockForAbstractClass(ClientInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->responseResolverMock = $this->createMock(ResponseResolver::class);

        $this->signUpCommand = new SignUpCommand(
            $this->analyticsTokenMock,
            $this->integrationManagerMock,
            $this->configMock,
            $this->httpClientMock,
            $this->loggerMock,
            $this->responseResolverMock
        );
    }

    /**
     * @throws \Zend_Http_Exception
     * @return void
     */
    public function testExecuteSuccess()
    {
        $this->integrationManagerMock->expects($this->once())
            ->method('generateToken')
            ->willReturn($this->integrationToken);
        $this->integrationManagerMock->expects($this->once())
            ->method('activateIntegration')
            ->willReturn(true);
        $data = $this->getTestData();

        $this->configMock
            ->method('getValue')
            ->willReturn($data['url']);
        $this->integrationToken
            ->method('getData')
            ->with('token')
            ->willReturn($data['integration-token']);
        $httpResponse = new \Zend_Http_Response(201, [], '{"access-token": "' . $data['access-token'] . '"}');
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                $data['method'],
                $data['url'],
                $data['body']
            )
            ->willReturn($httpResponse);
        $this->responseResolverMock
            ->method('getResult')
            ->with($httpResponse)
            ->willReturn(true);
        $this->assertTrue($this->signUpCommand->execute());
    }

    /**
     * @return void
     */
    public function testExecuteFailureCannotGenerateToken()
    {
        $this->integrationManagerMock->expects($this->once())
            ->method('generateToken')
            ->willReturn(false);
        $this->integrationManagerMock->expects($this->never())
            ->method('activateIntegration');
        $this->assertFalse($this->signUpCommand->execute());
    }

    /**
     * @throws \Zend_Http_Exception
     * @return void
     */
    public function testExecuteFailureResponseIsEmpty()
    {
        $this->integrationManagerMock->expects($this->once())
            ->method('generateToken')
            ->willReturn($this->integrationToken);
        $this->integrationManagerMock->expects($this->once())
            ->method('activateIntegration')
            ->willReturn(true);
        $httpResponse = new \Zend_Http_Response(0, []);
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);
        $this->responseResolverMock
            ->method('getResult')
            ->willReturn(false);
        $this->assertFalse($this->signUpCommand->execute());
    }

    /**
     * Returns test parameters for request.
     *
     * @return array
     */
    private function getTestData()
    {
        return [
            'url' => 'http://www.mystore.com',
            'access-token' => 'thisisaccesstoken',
            'integration-token' => 'thisisintegrationtoken',
            'method' => ZendClient::POST,
            'body'=> ['token' => 'thisisintegrationtoken','url' => 'http://www.mystore.com'],
        ];
    }
}
