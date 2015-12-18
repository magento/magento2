<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    protected $_integrationServiceMock;

    /**
     * Integration config
     *
     * @var \Magento\Integration\Model\IntegrationConfig
     */
    protected $_integrationConfigMock;

    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $aclRetriever;

    /**
     * Integration config
     *
     * @var \Magento\Integration\Model\ConfigBasedIntegrationManager
     */
    protected $_integrationManager;

    public function setUp()
    {
        $this->_integrationConfigMock = $this->getMockBuilder(
            '\Magento\Integration\Model\IntegrationConfig'
        )->disableOriginalConstructor()->setMethods(
            ['getIntegrations']
        )->getMock();

        $this->_integrationServiceMock = $this->getMockBuilder(
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

        $this->_integrationManager = new \Magento\Integration\Model\ConfigBasedIntegrationManager(
            $this->_integrationConfigMock,
            $this->_integrationServiceMock,
            $this->aclRetriever
        );
    }

    public function tearDown()
    {
        unset($this->_integrationConfigMock);
        unset($this->_integrationServiceMock);
        unset($this->_integrationManager);
    }

    public function testProcessIntegrationConfigNoIntegrations()
    {
        $this->_integrationConfigMock->expects($this->never())->method('getIntegrations');
        $this->_integrationManager->processIntegrationConfig([]);
    }

    public function testProcessIntegrationConfigRecreateUpdatedConfigAfterResourceChange()
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
            ]
        ];
        $originalResources = [
            'Magento_Customer::manage'
        ];
        $newResources = [
            'Magento_Customer::manage',
            'Magento_Customer::customer'
        ];

        $this->_integrationConfigMock->expects($this->once())->method('getIntegrations')->willReturn($newResources);
        $integrationObject = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        // Integration already exists, so update with new data and recreate
        $this->_integrationServiceMock->expects($this->at(0))->method('findByName')->with('TestIntegration1')->will(
            $this->returnValue($integrationObject)
        );
        $this->aclRetriever->expects($this->once())->method('getAllowedResourcesByUser')
            ->willReturn($originalResources);
        $integrationObject->expects($this->any())->method('getId')->willReturn($originalData[Integration::ID]);
        $this->_integrationServiceMock->expects($this->once())->method('update')->willReturn($integrationObject);

        $integrationObject->expects($this->once())->method('getOrigData')->willReturn($originalData);
        $integrationObject->expects($this->once())->method('getData')->willReturn($newResources);

        $this->_integrationServiceMock->expects($this->once())->method('create');

        $this->_integrationManager->processIntegrationConfig($integrations);
    }

    public function testProcessIntegrationConfigCreateNewIntegrations()
    {
        $integrations = [
            'TestIntegration1' => [
                Integration::EMAIL => 'test-integration1@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
            ],
            'TestIntegration2' => [
                Integration::EMAIL => 'test-integration2@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
            ]
        ];
        $newResources = [
            'Magento_Customer::manage',
            'Magento_Customer::customer'
        ];

        $this->_integrationConfigMock->expects($this->once())->method('getIntegrations')->willReturn($newResources);
        $integrationObject = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        // Integration1 does not exist, so create it
        $this->_integrationServiceMock->expects($this->at(0))->method('findByName')->with('TestIntegration1')->will(
            $this->returnValue($integrationObject)
        );
        $integrationObject->expects($this->any())->method('getId')->willReturn(false);
        $this->_integrationServiceMock->expects($this->any())->method('create');

        // Integration2 does not exist, so create it
        $this->_integrationServiceMock->expects($this->at(2))->method('findByName')->with('TestIntegration2')->will(
            $this->returnValue($integrationObject)
        );

        $this->_integrationManager->processIntegrationConfig($integrations);
    }
}
