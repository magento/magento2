<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;


/**
 * Class to test Integration Manager
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Integration service
     *
     * @var \Magento\Integration\Service\V1\IntegrationInterface
     */
    protected $_integrationServiceMock;

    /**
     * Integration config
     *
     * @var \Magento\Integration\Model\Config
     */
    protected $_integrationConfigMock;

    /**
     * Integration config
     *
     * @var \Magento\Integration\Model\Manager
     */
    protected $_integrationManager;

    public function setUp()
    {
        $this->_integrationConfigMock = $this->getMockBuilder(
            '\Magento\Integration\Model\Config'
        )->disableOriginalConstructor()->setMethods(
            ['getIntegrations']
        )->getMock();

        $this->_integrationServiceMock = $this->getMockBuilder(
            '\Magento\Integration\Service\V1\Integration'
        )->disableOriginalConstructor()->setMethods(
            ['findByName', 'update', 'create']
        )->getMock();

        $this->_integrationManager = new \Magento\Integration\Model\Manager(
            $this->_integrationConfigMock,
            $this->_integrationServiceMock
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

    public function testProcessIntegrationConfigSuccess()
    {
        $this->_integrationConfigMock->expects(
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
        $intLookupData1 = new \Magento\Framework\Object(
            ['id' => 1, Integration::NAME => 'TestIntegration1', Integration::SETUP_TYPE => 1]
        );

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

        $this->_integrationServiceMock->expects(
            $this->at(0)
        )->method(
            'findByName'
        )->with(
            'TestIntegration1'
        )->will(
            $this->returnValue($intLookupData1)
        );
        $this->_integrationServiceMock->expects($this->once())->method('create')->with($integrationsData2);

        $this->_integrationServiceMock->expects(
            $this->at(2)
        )->method(
            'findByName'
        )->with(
            'TestIntegration2'
        )->will(
            $this->returnValue(new \Magento\Framework\Object([]))
        );
        $this->_integrationServiceMock->expects($this->once())->method('update')->with($intUpdateData1);

        $this->_integrationManager->processIntegrationConfig(['TestIntegration1', 'TestIntegration2']);
    }
}
