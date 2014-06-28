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

class IntegrationServiceV1Test extends \PHPUnit_Framework_TestCase
{
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
     * @var \Magento\Webapi\Model\Plugin\IntegrationServiceV1
     */
    protected $integrationV1Plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    public function setUp()
    {
        $this->authzServiceMock = $this->getMockBuilder(
            '\Magento\Authz\Service\AuthorizationV1'
        )->disableOriginalConstructor()->setMethods(
            array('removePermissions')
        )->getMock();
        $this->userIdentifierFactoryMock = $this->getMockBuilder(
            '\Magento\Authz\Model\UserIdentifier\Factory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();
        $this->subjectMock = $this->getMock('Magento\Integration\Service\V1\Integration', array(), array(), '', false);
        $this->integrationV1Plugin = new \Magento\Webapi\Model\Plugin\IntegrationServiceV1(
            $this->authzServiceMock,
            $this->userIdentifierFactoryMock
        );
    }

    public function testAfterDelete()
    {
        $integrationsData = array(
            Integration::ID => 1,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::SETUP_TYPE => 1
        );
        $userIdentifierMock = $this->getMockBuilder(
            '\Magento\Authz\Model\UserIdentifier'
        )->disableOriginalConstructor()->getMock();
        $this->authzServiceMock->expects($this->once())->method('removePermissions')->with($userIdentifierMock);
        $this->userIdentifierFactoryMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            UserIdentifier::USER_TYPE_INTEGRATION,
            1
        )->will(
            $this->returnValue($userIdentifierMock)
        );
        $this->authzServiceMock->expects($this->once())->method('removePermissions')->with($userIdentifierMock);
        $this->integrationV1Plugin->afterDelete($this->subjectMock, $integrationsData);
    }
}
