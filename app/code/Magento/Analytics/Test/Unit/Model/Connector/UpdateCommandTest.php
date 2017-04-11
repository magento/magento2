<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\FlagManager;
use Magento\Framework\HTTP\ZendClient;
use Magento\Config\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Analytics\Model\Connector\UpdateCommand;
use Magento\Analytics\Model\Connector\Http\ClientInterface;

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
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var ResponseResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseResolverMock;

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

        $this->responseResolverMock = $this->getMockBuilder(ResponseResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

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
