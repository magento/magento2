<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model;

use Magento\Integration\Model\Integration;

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
            array('getIntegrations')
        )->getMock();

        $this->_integrationServiceMock = $this->getMockBuilder(
            '\Magento\Integration\Service\V1\Integration'
        )->disableOriginalConstructor()->setMethods(
            array('findByName', 'update', 'create')
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
        $this->_integrationManager->processIntegrationConfig(array());
    }

    public function testProcessIntegrationConfigSuccess()
    {
        $this->_integrationConfigMock->expects(
            $this->once()
        )->method(
            'getIntegrations'
        )->will(
            $this->returnValue(
                array(
                    'TestIntegration1' => array(
                        'email' => 'test-integration1@magento.com',
                        'endpoint_url' => 'http://endpoint.com',
                        'identity_link_url' => 'http://www.example.com/identity'
                    ),
                    'TestIntegration2' => array('email' => 'test-integration2@magento.com')
                )
            )
        );
        $intLookupData1 = new \Magento\Framework\Object(
            array('id' => 1, Integration::NAME => 'TestIntegration1', Integration::SETUP_TYPE => 1)
        );

        $intUpdateData1 = array(
            Integration::ID => 1,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::IDENTITY_LINK_URL => 'http://www.example.com/identity',
            Integration::SETUP_TYPE => 1
        );

        $integrationsData2 = array(
            Integration::NAME => 'TestIntegration2',
            Integration::EMAIL => 'test-integration2@magento.com',
            Integration::SETUP_TYPE => 1
        );

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
            $this->returnValue(new \Magento\Framework\Object(array()))
        );
        $this->_integrationServiceMock->expects($this->once())->method('update')->with($intUpdateData1);

        $this->_integrationManager->processIntegrationConfig(array('TestIntegration1', 'TestIntegration2'));
    }
}
