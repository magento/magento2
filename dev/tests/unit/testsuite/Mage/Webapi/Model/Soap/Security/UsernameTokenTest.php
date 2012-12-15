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
 * @category    Magento
 * @package     Mage_Webapi
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test SOAP WS-Security UsernameToken implementation.
 */
class Mage_Webapi_Model_Soap_Security_UsernameTokenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_nonceStorageMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_userFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_userMock;

    /**
     * Set up nonce storage mock to be used in further tests.
     */
    protected function setUp()
    {
        $this->_nonceStorageMock = $this->getMockBuilder('Mage_Webapi_Model_Soap_Security_UsernameToken_NonceStorage')
            ->disableOriginalConstructor()
            ->setConstructorArgs(array('validateNonce'))
            ->getMock();
        $this->_userMock = $this->getMockBuilder('Mage_Webapi_Model_Acl_User')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getId', 'getSecret'))
            ->getMock();
        $this->_userFactoryMock = $this->getMockBuilder('Mage_Webapi_Model_Acl_User_Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
    }

    /**
     * Clean up.
     */
    protected function tearDown()
    {
        unset($this->_nonceStorageMock);
        unset($this->_userMock);
        unset($this->_userFactoryMock);
    }

    /**
     * Test construction of object with valid datetime input.
     *
     * @dataProvider validDateTimeProvider()
     * @param string $validDateTime
     */
    public function testAuthenticateUsernameToken($validDateTime)
    {
        $username = 'test_user';
        $password = 'test_password';
        $nonce = mt_rand();
        $tokenPassword = base64_encode(hash('sha1', $nonce . $validDateTime . $password, true));
        $tokenNonce = base64_encode($nonce);
        $this->_nonceStorageMock
            ->expects($this->once())
            ->method('validateNonce')
            ->with($tokenNonce, strtotime($validDateTime));
        $this->_userFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->will($this->returnValue($this->_userMock));
        $this->_userMock->expects($this->once())
            ->method('load')
            ->with($username, 'api_key')
            ->will($this->returnSelf());
        $this->_userMock->expects($this->once())
            ->method('getId')
            ->with()
            ->will($this->returnValue(1));
        $this->_userMock->expects($this->once())
            ->method('getSecret')
            ->with()
            ->will($this->returnValue($password));

        $usernameToken = new Mage_Webapi_Model_Soap_Security_UsernameToken(
            $this->_nonceStorageMock,
            $this->_userFactoryMock
        );
        $this->assertEquals(
            $this->_userMock,
            $usernameToken->authenticate($username, $tokenPassword, $validDateTime, $tokenNonce)
        );
    }

    /**
     * Data provider for testConstructNewUsernameToken
     *
     * @return array
     */
    public static function validDateTimeProvider()
    {
        return array(
            'Valid ISO8601 date' => array(date('c')),
            'Date in UTC timezone "Z"' => array(date('Y-m-d\TH:i:s\Z')),
            'Date in +2 hours timezone' => array(date('Y-m-d\TH:i:s+02:00')),
            'Date in -2.5 hours timezone' => array(date('Y-m-d\TH:i:s-02:30')),
        );
    }

    /**
     * Test construction of object with invalid datetime input.
     *
     * @dataProvider invalidDateTimeProvider()
     * @param string $invalidDateTime
     * @expectedException Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidDateException
     */
    public function testAuthenticateUsernameTokenWithInvalidCreatedDate($invalidDateTime)
    {
        $username = 'test_user';
        $password = 'test_password';
        $nonce = mt_rand();

        $usernameToken = new Mage_Webapi_Model_Soap_Security_UsernameToken(
            $this->_nonceStorageMock,
            $this->_userFactoryMock
        );
        $usernameToken->authenticate($username, $password, $invalidDateTime, $nonce);
    }

    /**
     * Data provider for testConstructNewUsernameTokenWithInvalidCreatedDate
     *
     * @return array
     */
    public static function invalidDateTimeProvider()
    {
        return array(
            'No time specified' => array(date('Y-m-d')),
            'No seconds specified' => array(date('Y-m-dTH:i')),
            'Hours value is out of range' => array(date('Y-m-dT25:00:52+02:00')),
        );
    }

    /**
     * Test construction of object with invalid password type.
     *
     * @expectedException Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidPasswordTypeException
     */
    public function testConstructNewUsernameTokenWithInvalidPasswordType()
    {
        new Mage_Webapi_Model_Soap_Security_UsernameToken(
            $this->_nonceStorageMock,
            $this->_userFactoryMock,
            'INVALID_TYPE'
        );
    }

    /**
     * Test negative token authentication - username is invalid.
     *
     * @expectedException Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidCredentialException
     */
    public function testAuthenticateWithInvalidUsername()
    {
        $username = 'test_user';
        $password = 'test_password';
        list($created, $tokenPassword, $tokenNonce) = $this->_getTokenData($password);

        $this->_nonceStorageMock
            ->expects($this->once())
            ->method('validateNonce')
            ->with($tokenNonce, strtotime($created));
        $this->_userFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->will($this->returnValue($this->_userMock));
        $this->_userMock->expects($this->once())
            ->method('load')
            ->with($username, 'api_key')
            ->will($this->returnSelf());
        $this->_userMock->expects($this->once())
            ->method('getId')
            ->with()
            ->will($this->returnValue(false));

        $usernameToken = new Mage_Webapi_Model_Soap_Security_UsernameToken(
            $this->_nonceStorageMock,
            $this->_userFactoryMock
        );
        $usernameToken->authenticate($username, $tokenPassword, $created, $tokenNonce);
    }

    /**
     * Test negative token authentication - password is invalid.
     *
     * @expectedException Mage_Webapi_Model_Soap_Security_UsernameToken_InvalidCredentialException
     */
    public function testAuthenticateWithInvalidPassword()
    {
        $username = 'test_user';
        $password = 'test_password';
        $invalidPassword = 'invalid_password';
        list($created, $tokenPassword, $tokenNonce) = $this->_getTokenData($password);

        $this->_nonceStorageMock
            ->expects($this->once())
            ->method('validateNonce')
            ->with($tokenNonce, strtotime($created));
        $this->_userFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->will($this->returnValue($this->_userMock));
        $this->_userMock->expects($this->once())
            ->method('load')
            ->with($username, 'api_key')
            ->will($this->returnSelf());
        $this->_userMock->expects($this->once())
            ->method('getId')
            ->with()
            ->will($this->returnValue(1));
        $this->_userMock->expects($this->once())
            ->method('getSecret')
            ->with()
            ->will($this->returnValue($invalidPassword));

        $usernameToken = new Mage_Webapi_Model_Soap_Security_UsernameToken(
            $this->_nonceStorageMock,
            $this->_userFactoryMock
        );
        $usernameToken->authenticate($username, $tokenPassword, $created, $tokenNonce);
    }

    protected function _getTokenData($password)
    {
        $nonce = mt_rand();
        $created = date('c');
        $tokenPassword = base64_encode(hash('sha1', $nonce . $created . $password, true));
        $tokenNonce = base64_encode($nonce);
        return array($created, $tokenPassword, $tokenNonce);
    }
}
