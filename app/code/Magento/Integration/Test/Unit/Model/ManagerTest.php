<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

use \Magento\Integration\Model\Integration;

/**
 * Class to test Integration Manager
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Integration service
     *
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $integrationServiceMock;

    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $aclRetriever;

    /**
     * @var \Magento\Integration\Model\Config
     */
    protected $configMock;

    /**
     * Integration config
     *
     * @var \Magento\Integration\Model\ConfigBasedIntegrationManager
     */
    protected $integrationManager;

    protected function setUp()
    {
        $this->integrationServiceMock = $this->getMockBuilder(
            '\Magento\Integration\Api\IntegrationServiceInterface'
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

        $this->aclRetriever = $this->getMockBuilder('Magento\Authorization\Model\Acl\AclRetriever')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->configMock = $this->getMockBuilder('Magento\Integration\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->integrationManager = $objectManagerHelper->getObject(
            'Magento\Integration\Model\ConfigBasedIntegrationManager',
            [
                'integrationService' => $this->integrationServiceMock,
                'aclRetriever' => $this->aclRetriever,
                'integrationConfig' => $this->configMock
            ]
        );
    }

    public function tearDown()
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
        )->will(
            $this->returnValue(
                [
                    'TestIntegration1' => [
                        'email' => 'test-integration1@magento.com',
                        'endpoint_url' => 'http://endpoint.com',
                        'identity_link_url' => 'http://www.example.com/identity',
                    ],
                    'TestIntegration2' => ['email' => 'test-integration2@magento.com'],
                ]
            )
        );
        $intLookupData1 = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $intLookupData1->expects($this->any())->method('getId')->willReturn(1);
        $intLookupData2 = $this->getMockBuilder('Magento\Integration\Model\Integration')
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
        )->will(
            $this->returnValue($intLookupData1)
        );
        $this->integrationServiceMock->expects($this->once())->method('create')->with($integrationsData2);
        $this->integrationServiceMock->expects(
            $this->at(2)
        )->method(
            'findByName'
        )->with(
            'TestIntegration2'
        )->will(
            $this->returnValue($intLookupData2)
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

        $integrationObject = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        // Integration already exists, so update with new data and recreate
        $this->integrationServiceMock->expects($this->at(0))->method('findByName')->with('TestIntegration1')->will(
            $this->returnValue($integrationObject)
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

        $integrationObject = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        // Integration1 does not exist, so create it
        $this->integrationServiceMock->expects($this->at(0))->method('findByName')->with('TestIntegration1')->will(
            $this->returnValue($integrationObject)
        );
        $integrationObject->expects($this->any())->method('getId')->willReturn(false);
        $this->integrationServiceMock->expects($this->any())->method('create');

        // Integration2 does not exist, so create it
        $this->integrationServiceMock->expects($this->at(2))->method('findByName')->with('TestIntegration2')->will(
            $this->returnValue($integrationObject)
        );

        $this->integrationManager->processConfigBasedIntegrations($integrations);
    }
}
