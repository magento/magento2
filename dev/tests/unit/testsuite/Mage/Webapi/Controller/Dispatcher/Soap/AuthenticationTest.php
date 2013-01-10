<?php
/**
 * SOAP web API authentication model.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Dispatcher_Soap_AuthenticationTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_helperMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_tokenFactoryMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_roleLocatorMock;

    /** @var Mage_Webapi_Controller_Dispatcher_Soap_Authentication */
    protected $_soapAuthentication;

    /** @var stdClass */
    protected $_usernameToken;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_tokenMock;

    protected function setUp()
    {
        $this->_usernameToken = new stdClass();
        /** Prepare mocks for SUT constructor. */
        $this->_usernameToken->Username = 'userName';
        $this->_usernameToken->Password = 'password';
        $this->_usernameToken->Created = '2012-12-12';
        $this->_usernameToken->Nonce = 'Nonce';

        $this->_helperMock = $this->getMockBuilder('Mage_Webapi_Helper_Data')
            ->setMethods(array('__'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));
        $this->_tokenFactoryMock = $this->getMockBuilder('Mage_Webapi_Model_Soap_Security_UsernameToken_Factory')
            ->setMethods(array('createFromArray'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenMock = $this->getMockBuilder('Mage_Webapi_Model_Soap_Security_UsernameToken')
            ->disableOriginalConstructor()
            ->setMethods(array('authenticate'))
            ->getMock();
        $this->_tokenFactoryMock
            ->expects($this->once())
            ->method('createFromArray')
            ->will($this->returnValue($this->_tokenMock));
        $this->_roleLocatorMock = $this->getMockBuilder('Mage_Webapi_Model_Authorization_RoleLocator')
            ->setMethods(array('setRoleId'))
            ->disableOriginalConstructor()
            ->getMock();
        /** Initialize SUT. */
        $this->_soapAuthentication = new Mage_Webapi_Controller_Dispatcher_Soap_Authentication(
            $this->_helperMock,
            $this->_tokenFactoryMock,
            $this->_roleLocatorMock
        );
        parent::setUp();
    }

    public function testAuthenticate()
    {
        /** Prepare mocks for SUT constructor. */
        $user = $this->getMockBuilder('Mage_Webapi_Model_Acl_User')
            ->disableOriginalConstructor()
            ->setMethods(array('getRoleId'))
            ->getMock();
        $roleId = 1;
        $user->expects($this->once())->method('getRoleId')->will($this->returnValue($roleId));
        $this->_tokenMock->expects($this->once())
            ->method('authenticate')
            ->with(
                $this->_usernameToken->Username,
                $this->_usernameToken->Password,
                $this->_usernameToken->Created,
                $this->_usernameToken->Nonce
            )->will($this->returnValue($user));
        $this->_tokenFactoryMock
            ->expects($this->once())
            ->method('createFromArray')
            ->will($this->returnValue($this->_tokenMock));
        $this->_roleLocatorMock->expects($this->once())->method('setRoleId')->with($roleId);
        /** Execute SUT. */
        $this->_soapAuthentication->authenticate($this->_usernameToken);
    }

    /**
     * @dataProvider authenticateExceptionProvider
     */
    public function testAuthenticateWithException($exception, $exceptionMessage)
    {
        /** Prepare mocks for SUT constructor. */
        $this->_tokenMock
            ->expects($this->once())
            ->method('authenticate')
            ->with(
                $this->_usernameToken->Username,
                $this->_usernameToken->Password,
                $this->_usernameToken->Created,
                $this->_usernameToken->Nonce
            )->will($this->throwException($exception));
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            $exceptionMessage,
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        /** Execute SUT. */
        $this->_soapAuthentication->authenticate($this->_usernameToken);
    }

    /**
     * Exception data provider for authenticate() method
     *
     * @return array
     */
    public function authenticateExceptionProvider()
    {
        return array(
            'testAuthenticateUsernameTokenInvalidCredentialException.' => array(
                new Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidCredentialException(),
                'Invalid Username or Password.',
            ),
            'testAuthenticateUsernameTokenNonceUsedException.' => array(
                new Mage_Webapi_Model_Soap_Security_UsernameToken_NonceUsedException(),
                'WS-Security UsernameToken Nonce is already used.',
            ),
            'testAuthenticateUsernameTokenTimestampRefusedException.' => array(
                new Mage_Webapi_Model_Soap_Security_UsernameToken_TimestampRefusedException(),
                'WS-Security UsernameToken Created timestamp is refused.',
            ),
            'testAuthenticateUsernameTokenInvalidDateException.' => array(
                new Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidDateException(),
                'Invalid UsernameToken Created date.',
            ),
        );
    }
}
