<?php
/**
 * Two-legged OAuth server test.
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
class Mage_Webapi_Model_Rest_Oauth_ServerTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Model_Rest_Oauth_Server */
    protected $_server;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_consumerFactoryMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_nonceFactory;

    /**
     * Set up mocks.
     */
    protected function setUp()
    {
        $this->_requestMock = $this->getMockBuilder('Mage_Webapi_Controller_Request_Rest')
            ->setMethods(array('getHeader', 'getScheme', 'getHttpHost', 'getRequestUri'))
            ->disableOriginalConstructor()
            ->getMock();
        $tokenFactory = $this->getMockBuilder('Mage_Oauth_Model_Token_Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_consumerFactoryMock = $this->getMockBuilder('Mage_Webapi_Model_Acl_User_Factory')
            ->setMethods(array('create'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_nonceFactory = $this->getMockBuilder('Mage_Oauth_Model_Nonce_Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_server = new Mage_Webapi_Model_Rest_Oauth_Server(
            $this->_requestMock,
            $tokenFactory,
            $this->_consumerFactoryMock,
            $this->_nonceFactory
        );
    }

    /**
     * Test two-legged authentication
     */
    public function testAuthenticateTwoLegged()
    {
        $testUserKey = 'foo_user';
        $testUserSecret = 'bar_secret';
        $testUrl = 'http://foo.bar/api/rest/v1/baz';
        // Prepare signature and OAuth parameters.
        $utility = new Zend_Oauth_Http_Utility();
        $params = array(
            'oauth_consumer_key' => $testUserKey,
            'oauth_nonce' => $utility->generateNonce(),
            'oauth_timestamp' => $utility->generateTimestamp(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => Mage_Oauth_Model_Server::SIGNATURE_PLAIN,
        );
        $params['oauth_signature'] = $utility->sign(
            $params,
            Mage_Oauth_Model_Server::SIGNATURE_PLAIN,
            $testUserSecret,
            '',
            'GET',
            $testUrl
        );
        $authHeader = $utility->toAuthorizationHeader($params);
        $this->_requestMock->expects($this->at(0))
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue($authHeader));
        $this->_requestMock->expects($this->at(1))
            ->method('getHeader')
            ->with(Zend_Http_Client::CONTENT_TYPE)
            ->will($this->returnValue('application/json'));
        $this->_requestMock->expects($this->any())
            ->method('getScheme')
            ->with()
            ->will($this->returnValue(Zend_Controller_Request_Http::SCHEME_HTTP));
        $this->_requestMock->expects($this->any())
            ->method('getHttpHost')
            ->with()
            ->will($this->returnValue('foo.bar'));
        $this->_requestMock->expects($this->any())
            ->method('getRequestUri')
            ->with()
            ->will($this->returnValue('/api/rest/v1/baz'));

        $userMock = $this->getMockBuilder('Mage_Webapi_Model_Acl_User')
            ->setMethods(array('loadByKey', 'getId', 'getSecret'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_consumerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($userMock));
        $userMock->expects($this->once())
            ->method('loadByKey')
            ->with($testUserKey)
            ->will($this->returnSelf());
        $userMock->expects($this->once())
            ->method('getId')
            ->with()
            ->will($this->returnValue(1));
        $userMock->expects($this->once())
            ->method('getSecret')
            ->with()
            ->will($this->returnValue($testUserSecret));

        $this->assertEquals($userMock, $this->_server->authenticateTwoLegged());
    }
}
