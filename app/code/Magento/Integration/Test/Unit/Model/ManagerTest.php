<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Config;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Model\Integration;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /**
     * Integration service
     *
     * @var IntegrationServiceInterface
     */
    protected $integrationServiceMock;

    /**
     * @var AclRetriever
     */
    protected $aclRetriever;

    /**
     * @var Config
     */
    protected $configMock;

    /**
     * Integration config
     *
     * @var ConfigBasedIntegrationManager
     */
    protected $integrationManager;

    protected function setUp(): void
    {
        $this->integrationServiceMock = $this->getMockBuilder(
            IntegrationServiceInterface::class
        )->disableOriginalConstructor()
            ->setMethods(
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

        $this->aclRetriever = $this->getMockBuilder(AclRetriever::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);

        $this->integrationManager = $objectManagerHelper->getObject(
            ConfigBasedIntegrationManager::class,
            [
                'integrationService' => $this->integrationServiceMock,
                'aclRetriever' => $this->aclRetriever,
                'integrationConfig' => $this->configMock
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($this->integrationServiceMock);
        unset($this->integrationManager);
    }

    public function testProcessIntegrationConfigNoIntegrations()
    {
        $this->configMock->expects($this->never())->method('getIntegrations');
        $this->integrationManager->processIntegrationConfig([]);
    }

    public function testProcessIntegrationConfigSuccess()
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getIntegrations'
        )->willReturn(
            
                [
                    'TestIntegration1' => [
                        'email' => 'test-integration1@magento.com',
                        'endpoint_url' => 'http://endpoint.com',
                        'identity_link_url' => 'http://www.example.com/identity',
                    ],
                    'TestIntegration2' => ['email' => 'test-integration2@magento.com'],
                ]
            
        );
        $intLookupData1 = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $intLookupData1->expects($this->any())->method('getId')->willReturn(1);
        $intLookupData2 = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $intLookupData1->expects($this->any())->method('getId')->willReturn(false);

        $intUpdateData1 = [
            Integration::ID => 1,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
            Integration::SETUP_TYPE => 1,
        ];
        $integrationsData2 = [
            Integration::NAME => 'TestIntegration2',
            Integration::EMAIL => 'test-integration2@magento.com',
            Integration::SETUP_TYPE => 1,
        ];
        $this->integrationServiceMock->expects(
            $this->at(0)
        )->method(
            'findByName'
        )->with(
            'TestIntegration1'
        )->willReturn(
            $intLookupData1
        );
        $this->integrationServiceMock->expects($this->once())->method('create')->with($integrationsData2);
        $this->integrationServiceMock->expects(
            $this->at(2)
        )->method(
            'findByName'
        )->with(
            'TestIntegration2'
        )->willReturn(
            $intLookupData2
        );
        $this->integrationServiceMock->expects($this->at(1))->method('update')->with($intUpdateData1);
        $this->integrationManager->processIntegrationConfig(['TestIntegration1', 'TestIntegration2']);
    }

    public function testProcessConfigBasedIntegrationsRecreateUpdatedConfigAfterResourceChange()
    {
        $originalData = [
            Integration::ID => 1,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
            Integration::SETUP_TYPE => 1
        ];
        $integrations = [
            'TestIntegration1' => [
                Integration::EMAIL => 'test-integration1@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
                'resources' => [
                    'Magento_Customer::manage',
                    'Magento_Customer::customer'
                ]
            ]
        ];
        $originalResources = [
            'Magento_Customer::manage'
        ];
        $newResources = [
            'Magento_Customer::manage',
            'Magento_Customer::customer'
        ];

        $integrationObject = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        // Integration already exists, so update with new data and recreate
        $this->integrationServiceMock->expects($this->at(0))->method('findByName')->with('TestIntegration1')->willReturn(
            $integrationObject
        );
        $this->aclRetriever->expects($this->once())->method('getAllowedResourcesByUser')
            ->willReturn($originalResources);
        $integrationObject->expects($this->any())->method('getId')->willReturn($originalData[Integration::ID]);
        $this->integrationServiceMock->expects($this->once())->method('update')->willReturn($integrationObject);

        $integrationObject->expects($this->once())->method('getOrigData')->willReturn($originalData);
        $integrationObject->expects($this->once())->method('getData')->willReturn($newResources);

        $this->integrationServiceMock->expects($this->once())->method('create');

        $this->integrationManager->processConfigBasedIntegrations($integrations);
    }

    public function testProcessConfigBasedIntegrationsCreateNewIntegrations()
    {
        $integrations = [
            'TestIntegration1' => [
                Integration::EMAIL => 'test-integration1@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
                'resources' => [
                    'Magento_Customer::manage',
                    'Magento_Customer::customer'
                ]
            ],
            'TestIntegration2' => [
                Integration::EMAIL => 'test-integration2@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
            ]
        ];

        $integrationObject = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        // Integration1 does not exist, so create it
        $this->integrationServiceMock->expects($this->at(0))->method('findByName')->with('TestIntegration1')->willReturn(
            $integrationObject
        );
        $integrationObject->expects($this->any())->method('getId')->willReturn(false);
        $this->integrationServiceMock->expects($this->any())->method('create');

        // Integration2 does not exist, so create it
        $this->integrationServiceMock->expects($this->at(2))->method('findByName')->with('TestIntegration2')->willReturn(
            $integrationObject
        );

        $this->integrationManager->processConfigBasedIntegrations($integrations);
    }
}
