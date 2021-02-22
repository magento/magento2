<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Analytics\Model\Connector\SignUpCommand;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\IntegrationManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Model\Oauth\Token as IntegrationToken;
use Psr\Log\LoggerInterface;

class SignUpCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SignUpCommand
     */
    private $signUpCommand;

    /**
     * @var AnalyticsToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var IntegrationManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $integrationManagerMock;

    /**
     * @var IntegrationToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $integrationToken;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $httpClientMock;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var ResponseResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $responseResolverMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->analyticsTokenMock =  $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationManagerMock = $this->getMockBuilder(IntegrationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationToken = $this->getMockBuilder(IntegrationToken::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->httpClientMock = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->responseResolverMock = $this->getMockBuilder(ResponseResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($data['url']);
        $this->integrationToken->expects($this->any())
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
        $this->responseResolverMock->expects($this->any())
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
        $this->responseResolverMock->expects($this->any())
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
            'method' => \Magento\Framework\HTTP\ZendClient::POST,
            'body'=> ['token' => 'thisisintegrationtoken','url' => 'http://www.mystore.com'],
        ];
    }
}
