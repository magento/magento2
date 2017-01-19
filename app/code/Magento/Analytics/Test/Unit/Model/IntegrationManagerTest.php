<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;
use Magento\Integration\Model\Integration;
use Magento\Analytics\Model\IntegrationManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class IntegrationManagerTest
 */
class IntegrationManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationServiceMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var IntegrationManager
     */
    private $integrationManager;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->integrationServiceMock = $this->getMockBuilder(IntegrationServiceInterface::class)
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationManager = $objectManagerHelper->getObject(
            IntegrationManager::class,
            [
                'integrationService' => $this->integrationServiceMock,
                'config' => $this->configMock
            ]
        );
    }

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

    public function testCreateIntegration()
    {
        $this->configMock->expects($this->once())
            ->method('getConfigDataValue')
            ->with('analytics/integration_name', null, null)
            ->willReturn('ma-integration-user');
        $this->integrationServiceMock->expects($this->once())
            ->method('create')
            ->with($this->getIntegrationUserData(Integration::STATUS_INACTIVE));
        $this->assertTrue($this->integrationManager->createIntegration());
    }

    public function testActivateIntegration()
    {
        $this->configMock->expects($this->once())
            ->method('getConfigDataValue')
            ->with('analytics/integration_name', null, null)
            ->willReturn('ma-integration-user');
        $this->integrationServiceMock->expects($this->once())
            ->method('update')
            ->with($this->getIntegrationUserData(Integration::STATUS_ACTIVE));
        $this->assertTrue($this->integrationManager->activateIntegration());
    }
}
