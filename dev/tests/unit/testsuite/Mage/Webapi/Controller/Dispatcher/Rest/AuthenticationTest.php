<?php
/**
 * REST web API authentication test.
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Dispatcher_Rest_AuthenticationTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_roleLocatorMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_oauthServerMock;

    /** @var Mage_Webapi_Controller_Dispatcher_Rest_Authentication */
    protected $_restAuthentication;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_oauthServerMock = $this->getMockBuilder('Mage_Webapi_Model_Rest_Oauth_Server')
            ->setMethods(array('authenticateTwoLegged', 'reportProblem'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_roleLocatorMock = $this->getMockBuilder('Mage_Webapi_Model_Authorization_RoleLocator')
            ->setMethods(array('setRoleId'))
            ->disableOriginalConstructor()
            ->getMock();
        /** Initialize SUT. */
        $this->_restAuthentication = new Mage_Webapi_Controller_Dispatcher_Rest_Authentication(
            $this->_oauthServerMock,
            $this->_roleLocatorMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_oauthServerMock);
        unset($this->_restAuthentication);
        unset($this->_roleLocatorMock);
        parent::tearDown();
    }

    public function testAuthenticate()
    {
        /** Prepare mocks for SUT constructor. */
        $consumerMock = $this->getMockBuilder('Mage_Webapi_Model_Acl_User')
            ->disableOriginalConstructor()
            ->setMethods(array('getRoleId'))
            ->getMock();
        $roleId = 1;
        $consumerMock->expects($this->once())->method('getRoleId')->will($this->returnValue($roleId));
        $this->_roleLocatorMock->expects($this->once())->method('setRoleId')->with($roleId);
        $this->_oauthServerMock
            ->expects($this->once())
            ->method('authenticateTwoLegged')
            ->will($this->returnValue($consumerMock));
        /** Execute SUT. */
        $this->_restAuthentication->authenticate();
    }

    public function testAuthenticateMageWebapiException()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_oauthServerMock
            ->expects($this->once())
            ->method('authenticateTwoLegged')
            ->will($this->throwException(
                Mage::exception('Mage_Oauth', 'Exception message.', Mage_Oauth_Model_Server::HTTP_BAD_REQUEST)
            ));
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Exception message.',
            Mage_Webapi_Exception::HTTP_UNAUTHORIZED
        );
        $this->_oauthServerMock
            ->expects($this->once())
            ->method('reportProblem')
            ->will($this->returnValue('Exception message.'));
        /** Execute SUT. */
        $this->_restAuthentication->authenticate();
    }
}
