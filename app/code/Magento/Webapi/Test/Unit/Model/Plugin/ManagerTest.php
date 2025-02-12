<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Plugin;

use Magento\Framework\DataObject;
use Magento\Integration\Api\AuthorizationServiceInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\IntegrationConfig;
use Magento\Webapi\Model\Plugin\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /**
     * @var IntegrationServiceInterface|MockObject
     */
    protected $integrationServiceMock;

    /**
     * @var AuthorizationServiceInterface|MockObject
     */
    protected $integrationAuthorizationServiceMock;

    /**
     * @var Manager
     */
    protected $apiSetupPlugin;

    /**
     * @var ConfigBasedIntegrationManager|MockObject
     */
    protected $subjectMock;

    /**
     * @var IntegrationConfig|MockObject
     */
    protected $integrationConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->integrationServiceMock = $this->getMockBuilder(IntegrationServiceInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'findByName',
                    'update',
                    'create',
                    'get',
                    'findByConsumerId',
                    'findActiveIntegrationByConsumerId',
                    'delete',
                    'getSelectedResources'
                ]
            )->getMock();

        $this->integrationAuthorizationServiceMock = $this->getMockBuilder(AuthorizationServiceInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['grantPermissions', 'grantAllPermissions', 'removePermissions'])
            ->getMock();

        $this->subjectMock = $this->createMock(ConfigBasedIntegrationManager::class);

        $this->integrationConfigMock = $this->getMockBuilder(IntegrationConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIntegrations'])
            ->getMock();

        $this->apiSetupPlugin = new Manager(
            $this->integrationAuthorizationServiceMock,
            $this->integrationServiceMock,
            $this->integrationConfigMock
        );
    }

    /**
     * @return void
     */
    public function testAfterProcessIntegrationConfigNoIntegrations(): void
    {
        $this->integrationConfigMock->expects($this->never())->method('getIntegrations');
        $this->integrationServiceMock->expects($this->never())->method('findByName');
        $this->apiSetupPlugin->afterProcessIntegrationConfig($this->subjectMock, []);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterProcessIntegrationConfigSuccess(): void
    {
        $testIntegration1Resource = [
            'Magento_Customer::manage',
            'Magento_Customer::online',
            'Magento_Sales::create',
            'Magento_SalesRule::quote'
        ];
        $testIntegration2Resource = ['Magento_Catalog::product_read'];
        $this->integrationConfigMock->expects(
            $this->once()
        )->method(
            'getIntegrations'
        )->willReturn(
            [
                'TestIntegration1' => ['resource' => $testIntegration1Resource],
                'TestIntegration2' => ['resource' => $testIntegration2Resource]
            ]
        );
        $firstIntegrationId = 1;
        $integrationsData1 = new DataObject(
            [
                'id' => $firstIntegrationId,
                Integration::NAME => 'TestIntegration1',
                Integration::EMAIL => 'test-integration1@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::SETUP_TYPE => 1
            ]
        );
        $secondIntegrationId = 2;
        $integrationsData2 = new DataObject(
            [
                'id' => $secondIntegrationId,
                Integration::NAME => 'TestIntegration2',
                Integration::EMAIL => 'test-integration2@magento.com',
                Integration::SETUP_TYPE => 1
            ]
        );
        $this->integrationServiceMock
            ->method('findByName')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['TestIntegration1'] => $integrationsData1,
                ['TestIntegration2'] => $integrationsData2
            });
        $this->apiSetupPlugin->afterProcessIntegrationConfig(
            $this->subjectMock,
            ['TestIntegration1', 'TestIntegration2']
        );
    }

    /**
     * @return void
     */
    public function testAfterProcessConfigBasedIntegrationsNoIntegrations(): void
    {
        $this->integrationServiceMock->expects($this->never())->method('findByName');
        $this->apiSetupPlugin->afterProcessConfigBasedIntegrations($this->subjectMock, []);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterProcessConfigBasedIntegrationsSuccess(): void
    {
        $firstIntegrationId = 1;
        $integrationsData1 = [
            'id' => $firstIntegrationId,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::SETUP_TYPE => 1,
            'resource' => [
                'Magento_Customer::manage',
                'Magento_Customer::online',
                'Magento_Sales::create',
                'Magento_SalesRule::quote'
            ]
        ];
        $integrationsData1Object = new DataObject($integrationsData1);

        $secondIntegrationId = 2;
        $integrationsData2 = [
            'id' => $secondIntegrationId,
            Integration::NAME => 'TestIntegration2',
            Integration::EMAIL => 'test-integration2@magento.com',
            Integration::SETUP_TYPE => 1,
            'resource' => ['Magento_Catalog::product_read']
        ];
        $integrationsData2Object = new DataObject($integrationsData2);

        $this->integrationServiceMock
            ->method('findByName')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['TestIntegration1'] => $integrationsData1Object,
                ['TestIntegration2'] => $integrationsData2Object
            });

        $this->apiSetupPlugin->afterProcessConfigBasedIntegrations(
            $this->subjectMock,
            ['TestIntegration1' => $integrationsData1, 'TestIntegration2' => $integrationsData2]
        );
    }
}
