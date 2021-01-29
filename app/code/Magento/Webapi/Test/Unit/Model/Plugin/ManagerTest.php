<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Plugin;

use Magento\Integration\Model\Integration;

class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Integration service mock
     *
     * @var \Magento\Integration\Api\IntegrationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $integrationServiceMock;

    /**
     * Authorization service mock
     *
     * @var \Magento\Integration\Api\AuthorizationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $integrationAuthorizationServiceMock;

    /**
     * API setup plugin
     *
     * @var \Magento\Webapi\Model\Plugin\Manager
     */
    protected $apiSetupPlugin;

    /**
     * @var \Magento\Integration\Model\ConfigBasedIntegrationManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Integration\Model\IntegrationConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $integrationConfigMock;

    protected function setUp(): void
    {
        $this->integrationServiceMock = $this->getMockBuilder(
            \Magento\Integration\Api\IntegrationServiceInterface::class
        )->disableOriginalConstructor()->setMethods(
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

        $this->integrationAuthorizationServiceMock = $this->getMockBuilder(
            \Magento\Integration\Api\AuthorizationServiceInterface::class
        )->disableOriginalConstructor()->setMethods(
            [
                'grantPermissions',
                'grantAllPermissions',
                'removePermissions'
            ]
        )->getMock();

        $this->subjectMock = $this->createMock(\Magento\Integration\Model\ConfigBasedIntegrationManager::class);

        $this->integrationConfigMock = $this->getMockBuilder(\Magento\Integration\Model\IntegrationConfig::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->apiSetupPlugin = new \Magento\Webapi\Model\Plugin\Manager(
            $this->integrationAuthorizationServiceMock,
            $this->integrationServiceMock,
            $this->integrationConfigMock
        );
    }

    public function testAfterProcessIntegrationConfigNoIntegrations()
    {
        $this->integrationConfigMock->expects($this->never())->method('getIntegrations');
        $this->integrationServiceMock->expects($this->never())->method('findByName');
        $this->apiSetupPlugin->afterProcessIntegrationConfig($this->subjectMock, []);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterProcessIntegrationConfigSuccess()
    {
        $testIntegration1Resource = [
            'Magento_Customer::manage',
            'Magento_Customer::online',
            'Magento_Sales::create',
            'Magento_SalesRule::quote',
        ];
        $testIntegration2Resource = ['Magento_Catalog::product_read'];
        $this->integrationConfigMock->expects(
            $this->once()
        )->method(
            'getIntegrations'
        )->willReturn(
            
                [
                    'TestIntegration1' => ['resource' => $testIntegration1Resource],
                    'TestIntegration2' => ['resource' => $testIntegration2Resource],
                ]
            
        );
        $firstIntegrationId = 1;
        $integrationsData1 = new \Magento\Framework\DataObject(
            [
                'id' => $firstIntegrationId,
                Integration::NAME => 'TestIntegration1',
                Integration::EMAIL => 'test-integration1@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::SETUP_TYPE => 1,
            ]
        );
        $secondIntegrationId = 2;
        $integrationsData2 = new \Magento\Framework\DataObject(
            [
                'id' => $secondIntegrationId,
                Integration::NAME => 'TestIntegration2',
                Integration::EMAIL => 'test-integration2@magento.com',
                Integration::SETUP_TYPE => 1,
            ]
        );
        $this->integrationServiceMock->expects(
            $this->at(0)
        )->method(
            'findByName'
        )->with(
            'TestIntegration1'
        )->willReturn(
            $integrationsData1
        );
        $this->integrationServiceMock->expects(
            $this->at(1)
        )->method(
            'findByName'
        )->with(
            'TestIntegration2'
        )->willReturn(
            $integrationsData2
        );
        $this->apiSetupPlugin->afterProcessIntegrationConfig(
            $this->subjectMock,
            ['TestIntegration1', 'TestIntegration2']
        );
    }

    public function testAfterProcessConfigBasedIntegrationsNoIntegrations()
    {
        $this->integrationServiceMock->expects($this->never())->method('findByName');
        $this->apiSetupPlugin->afterProcessConfigBasedIntegrations($this->subjectMock, []);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterProcessConfigBasedIntegrationsSuccess()
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
                'Magento_SalesRule::quote',
            ]
        ];
        $integrationsData1Object = new \Magento\Framework\DataObject($integrationsData1);

        $secondIntegrationId = 2;
        $integrationsData2 = [
            'id' => $secondIntegrationId,
            Integration::NAME => 'TestIntegration2',
            Integration::EMAIL => 'test-integration2@magento.com',
            Integration::SETUP_TYPE => 1,
            'resource' => ['Magento_Catalog::product_read']
        ];
        $integrationsData2Object = new \Magento\Framework\DataObject($integrationsData2);

        $this->integrationServiceMock->expects(
            $this->at(0)
        )->method(
            'findByName'
        )->with(
            'TestIntegration1'
        )->willReturn(
            $integrationsData1Object
        );

        $this->integrationServiceMock->expects(
            $this->at(1)
        )->method(
            'findByName'
        )->with(
            'TestIntegration2'
        )->willReturn(
            $integrationsData2Object
        );

        $this->apiSetupPlugin->afterProcessConfigBasedIntegrations(
            $this->subjectMock,
            ['TestIntegration1' => $integrationsData1, 'TestIntegration2' => $integrationsData2]
        );
    }
}
