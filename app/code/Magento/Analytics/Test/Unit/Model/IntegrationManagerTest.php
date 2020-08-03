<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\IntegrationManager;
use Magento\Config\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IntegrationManagerTest extends TestCase
{
    /**
     * @var IntegrationServiceInterface|MockObject
     */
    private $integrationServiceMock;

    /**
     * @var OauthServiceInterface|MockObject
     */
    private $oauthServiceMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Integration|MockObject
     */
    private $integrationMock;

    /**
     * @var IntegrationManager
     */
    private $integrationManager;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->integrationServiceMock = $this->getMockForAbstractClass(IntegrationServiceInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->oauthServiceMock = $this->getMockForAbstractClass(OauthServiceInterface::class);
        $this->integrationMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
                'getConsumerId'
            ])
            ->getMock();
        $this->integrationManager = $objectManagerHelper->getObject(
            IntegrationManager::class,
            [
                'integrationService' => $this->integrationServiceMock,
                'oauthService' => $this->oauthServiceMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * @param string $status
     *
     * @return array
     */
    private function getIntegrationUserData($status)
    {
        return [
            'name' => 'ma-integration-user',
            'status' => $status,
            'all_resources' => false,
            'resource' => [
                'Magento_Analytics::analytics',
                'Magento_Analytics::analytics_api'
            ],
        ];
    }

    /**
     * @return void
     */
    public function testActivateIntegrationSuccess()
    {
        $this->integrationServiceMock->expects($this->once())
            ->method('findByName')
            ->with('ma-integration-user')
            ->willReturn($this->integrationMock);
        $this->integrationMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(100500);
        $integrationData = $this->getIntegrationUserData(Integration::STATUS_ACTIVE);
        $integrationData['integration_id'] = 100500;
        $this->configMock->expects($this->exactly(2))
            ->method('getConfigDataValue')
            ->with('analytics/integration_name', null, null)
            ->willReturn('ma-integration-user');
        $this->integrationServiceMock->expects($this->once())
            ->method('update')
            ->with($integrationData);
        $this->assertTrue($this->integrationManager->activateIntegration());
    }

    public function testActivateIntegrationFailureNoSuchEntity()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->integrationServiceMock->expects($this->once())
            ->method('findByName')
            ->with('ma-integration-user')
            ->willReturn($this->integrationMock);
        $this->integrationMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('getConfigDataValue')
            ->with('analytics/integration_name', null, null)
            ->willReturn('ma-integration-user');
        $this->integrationServiceMock->expects($this->never())
            ->method('update');
        $this->integrationManager->activateIntegration();
    }

    /**
     * @dataProvider integrationIdDataProvider
     *
     * @param int|null $integrationId If null integration is absent.
     * @return void
     */
    public function testGetTokenNewIntegration($integrationId)
    {
        $this->configMock->expects($this->atLeastOnce())
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
        $this->integrationMock->expects($this->once())
            ->method('getId')
            ->willReturn($integrationId);
        if (!$integrationId) {
            $this->integrationServiceMock
                ->expects($this->once())
                ->method('create')
                ->with($this->getIntegrationUserData(Integration::STATUS_INACTIVE))
                ->willReturn($this->integrationMock);
        }
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
        $this->assertEquals('IntegrationToken', $this->integrationManager->generateToken());
    }

    /**
     * @dataProvider integrationIdDataProvider
     *
     * @param int|null $integrationId If null integration is absent.
     * @return void
     */
    public function testGetTokenExistingIntegration($integrationId)
    {
        $this->configMock->expects($this->atLeastOnce())
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
        $this->integrationMock->expects($this->once())
            ->method('getId')
            ->willReturn($integrationId);
        if (!$integrationId) {
            $this->integrationServiceMock
                ->expects($this->once())
                ->method('create')
                ->with($this->getIntegrationUserData(Integration::STATUS_INACTIVE))
                ->willReturn($this->integrationMock);
        }
        $this->oauthServiceMock->expects($this->once())
            ->method('getAccessToken')
            ->with(100500)
            ->willReturn('IntegrationToken');
        $this->oauthServiceMock->expects($this->never())
            ->method('createAccessToken');
        $this->assertEquals('IntegrationToken', $this->integrationManager->generateToken());
    }

    /**
     * @return array
     */
    public function integrationIdDataProvider()
    {
        return [
            [1],
            [null],
        ];
    }
}
