<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Integration\Model\Integration;
use Magento\Analytics\Model\TokenProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class TokenProviderTest
 */
class TokenProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OauthServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $oauthServiceMock;

    /**
     * @var IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationServiceMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Integration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationMock;

    /**
     * @var TokenProvider
     */
    private $tokenProvider;

    public function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->oauthServiceMock = $this->getMockBuilder(OauthServiceInterface::class)
            ->getMock();
        $this->integrationServiceMock = $this->getMockBuilder(IntegrationServiceInterface::class)
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->integrationMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConsumerId'])
            ->getMock();
        $this->tokenProvider = $objectManager->getObject(
            TokenProvider::class,
            [
                'config' => $this->configMock,
                'oauthService' => $this->oauthServiceMock,
                'integrationService' => $this->integrationServiceMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testGetTokenNewIntegration()
    {
        $this->configMock->expects($this->once())
            ->method('getConfigDataValue')
            ->with('analytics/integration_name', null, null)
            ->willReturn('ma-integration-user');
        $this->integrationServiceMock->expects($this->once())
            ->method('findByName')
            ->with('ma-integration-user')
            ->willReturn($this->integrationMock);
        $this->integrationMock->expects($this->once())
            ->method('getConsumerId')
            ->willReturn(100500);
        $this->oauthServiceMock->expects($this->at(0))
            ->method('getAccessToken')
            ->with(100500)
            ->willReturn(false);
        $this->oauthServiceMock->expects($this->at(2))
            ->method('getAccessToken')
            ->with(100500)
            ->willReturn('IntegrationToken');
        $this->oauthServiceMock->expects($this->once())
            ->method('createAccessToken')
            ->with(100500, true)
            ->willReturn(true);
        $this->assertEquals('IntegrationToken', $this->tokenProvider->getToken());
    }

    public function testGetTokenExistingIntegration()
    {
        $this->configMock->expects($this->once())
            ->method('getConfigDataValue')
            ->with('analytics/integration_name', null, null)
            ->willReturn('ma-integration-user');
        $this->integrationServiceMock->expects($this->once())
            ->method('findByName')
            ->with('ma-integration-user')
            ->willReturn($this->integrationMock);
        $this->integrationMock->expects($this->once())
            ->method('getConsumerId')
            ->willReturn(100500);
        $this->oauthServiceMock->expects($this->once())
            ->method('getAccessToken')
            ->with(100500)
            ->willReturn('IntegrationToken');
        $this->oauthServiceMock->expects($this->never())
            ->method('createAccessToken');
        $this->assertEquals('IntegrationToken', $this->tokenProvider->getToken());
    }
}
