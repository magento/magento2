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
namespace Magento\Webapi\Model\Plugin;

use Magento\Authz\Model\UserIdentifier;
use Magento\Integration\Model\Integration;

class SetupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * API Integration config
     *
     * @var \Magento\Webapi\Model\IntegrationConfig
     */
    protected $integrationConfigMock;

    /**
     * Integration service mock
     *
     * @var \Magento\Integration\Service\V1\IntegrationInterface
     */
    protected $integrationServiceMock;

    /**
     * Authorization service mock
     *
     * @var \Magento\Authz\Service\AuthorizationV1
     */
    protected $authzServiceMock;

    /**
     * Mock for UserIdentifier Factory
     *
     * @var \Magento\Authz\Model\UserIdentifier\Factory
     */
    protected $userIdentifierFactoryMock;

    /**
     * API setup plugin
     *
     * @var \Magento\Webapi\Model\Plugin\Setup
     */
    protected $apiSetupPlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    public function setUp()
    {
        $this->integrationConfigMock = $this->getMockBuilder(
            '\Magento\Webapi\Model\IntegrationConfig'
        )->disableOriginalConstructor()->setMethods(
            array('getIntegrations')
        )->getMock();

        $this->integrationServiceMock = $this->getMockBuilder(
            '\Magento\Integration\Service\V1\Integration'
        )->disableOriginalConstructor()->setMethods(
            array('findByName')
        )->getMock();

        $this->authzServiceMock = $this->getMockBuilder(
            '\Magento\Authz\Service\AuthorizationV1'
        )->disableOriginalConstructor()->setMethods(
            array('grantPermissions')
        )->getMock();

        $this->userIdentifierFactoryMock = $this->getMockBuilder(
            '\Magento\Authz\Model\UserIdentifier\Factory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $this->subjectMock = $this->getMock('Magento\Integration\Model\Resource\Setup', array(), array(), '', false);
        $this->apiSetupPlugin = new \Magento\Webapi\Model\Plugin\Setup(
            $this->integrationConfigMock,
            $this->authzServiceMock,
            $this->integrationServiceMock,
            $this->userIdentifierFactoryMock
        );
    }

    public function testAfterInitIntegrationProcessingNoIntegrations()
    {
        $this->integrationConfigMock->expects($this->never())->method('getIntegrations');
        $this->integrationServiceMock->expects($this->never())->method('findByName');
        $this->authzServiceMock->expects($this->never())->method('grantPermissions');
        $this->userIdentifierFactoryMock->expects($this->never())->method('create');
        $this->apiSetupPlugin->afterInitIntegrationProcessing($this->subjectMock, array());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterInitIntegrationProcessingSuccess()
    {
        $testIntegration1Resource = array(
            'Magento_Customer::manage',
            'Magento_Customer::online',
            'Magento_Sales::create',
            'Magento_SalesRule::quote'
        );
        $testIntegration2Resource = array('Magento_Catalog::product_read');
        $this->integrationConfigMock->expects(
            $this->once()
        )->method(
            'getIntegrations'
        )->will(
            $this->returnValue(
                array(
                    'TestIntegration1' => array('resources' => $testIntegration1Resource),
                    'TestIntegration2' => array('resources' => $testIntegration2Resource)
                )
            )
        );

        $integrationsData1 = new \Magento\Framework\Object(
            array(
                'id' => 1,
                Integration::NAME => 'TestIntegration1',
                Integration::EMAIL => 'test-integration1@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::SETUP_TYPE => 1
            )
        );

        $integrationsData2 = new \Magento\Framework\Object(
            array(
                'id' => 2,
                Integration::NAME => 'TestIntegration2',
                Integration::EMAIL => 'test-integration2@magento.com',
                Integration::SETUP_TYPE => 1
            )
        );

        $this->integrationServiceMock->expects(
            $this->at(0)
        )->method(
            'findByName'
        )->with(
            'TestIntegration1'
        )->will(
            $this->returnValue($integrationsData1)
        );

        $this->integrationServiceMock->expects(
            $this->at(1)
        )->method(
            'findByName'
        )->with(
            'TestIntegration2'
        )->will(
            $this->returnValue($integrationsData2)
        );

        $userIdentifierMock1 = $this->getMockBuilder(
            '\Magento\Authz\Model\UserIdentifier'
        )->disableOriginalConstructor()->getMock();
        $this->userIdentifierFactoryMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            UserIdentifier::USER_TYPE_INTEGRATION,
            1
        )->will(
            $this->returnValue($userIdentifierMock1)
        );

        $userIdentifierMock2 = $this->getMockBuilder(
            '\Magento\Authz\Model\UserIdentifier'
        )->disableOriginalConstructor()->getMock();
        $this->userIdentifierFactoryMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            UserIdentifier::USER_TYPE_INTEGRATION,
            2
        )->will(
            $this->returnValue($userIdentifierMock2)
        );

        $this->authzServiceMock->expects(
            $this->at(0)
        )->method(
            'grantPermissions'
        )->with(
            $userIdentifierMock1,
            $testIntegration1Resource
        );
        $this->authzServiceMock->expects(
            $this->at(1)
        )->method(
            'grantPermissions'
        )->with(
            $userIdentifierMock2,
            $testIntegration2Resource
        );

        $this->apiSetupPlugin->afterInitIntegrationProcessing(
            $this->subjectMock,
            array('TestIntegration1', 'TestIntegration2')
        );
    }
}
