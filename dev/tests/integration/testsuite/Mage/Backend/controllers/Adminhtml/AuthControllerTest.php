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
 * @category    Mage
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Adminhtml_AuthController
 * @magentoAppArea adminhtml
 */
class Mage_Backend_Adminhtml_AuthControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_session;

    /**
     * @var Mage_Backend_Model_Auth
     */
    protected $_auth;

    protected function tearDown()
    {
        $this->_session = null;
        $this->_auth = null;
        parent::tearDown();
    }

    /**
     * Performs user login
     */
    protected  function _login()
    {
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOffSecretKey();

        $this->_auth = Mage::getSingleton('Mage_Backend_Model_Auth');
        $this->_auth->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
        $this->_session = $this->_auth->getAuthStorage();
    }

    /**
     * Performs user logout
     */
    protected function _logout()
    {
        $this->_auth->logout();
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOnSecretKey();
    }

    /**
     * Check not logged state
     * @covers Mage_Backend_Adminhtml_AuthController::loginAction
     */
    public function testNotLoggedLoginAction()
    {
        $this->dispatch('backend/admin/auth/login');
        $this->assertFalse($this->getResponse()->isRedirect());

        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('form#login-form input#username[type=text]', true, $body);
        $this->assertSelectCount('form#login-form input#login[type=password]', true, $body);
    }

    /**
     * Check logged state
     * @covers Mage_Backend_Adminhtml_AuthController::loginAction
     * @magentoDbIsolation enabled
     */
    public function testLoggedLoginAction()
    {
        $this->_login();

        $this->dispatch('backend/admin/auth/login');
        /** @var $backendUrlModel Mage_Backend_Model_Url */
        $backendUrlModel = Mage::getObjectManager()->get('Mage_Backend_Model_Url');
        $url = $backendUrlModel->getStartupPageUrl();
        $expected = $backendUrlModel->getUrl($url);
        $this->assertRedirect($this->stringStartsWith($expected));

        $this->_logout();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testNotLoggedLoginActionWithRedirect()
    {
        $this->getRequest()->setPost(array(
            'login' => array(
                'username' => Magento_Test_Bootstrap::ADMIN_NAME,
                'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
            )
        ));

        $this->dispatch('backend/admin/index/index');

        $response = Mage::app()->getResponse();
        $code = $response->getHttpResponseCode();
        $this->assertTrue($code >= 300 && $code < 400, 'Incorrect response code');

        $this->assertTrue(Mage::getSingleton('Mage_Backend_Model_Auth')->isLoggedIn());
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::logoutAction
     * @magentoDbIsolation enabled
     */
    public function testLogoutAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/auth/logout');
        $this->assertRedirect($this->equalTo(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl()));
        $this->assertFalse($this->_session->isLoggedIn(), 'User is not logged out.');
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::deniedJsonAction
     * @covers Mage_Backend_Adminhtml_AuthController::_getDeniedJson
     * @magentoDbIsolation enabled
     */
    public function testDeniedJsonAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/auth/deniedJson');
        $data = array(
            'ajaxExpired' => 1,
            'ajaxRedirect' => Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl(),
        );
        $expected = json_encode($data);
        $this->assertEquals($expected, $this->getResponse()->getBody());
        $this->_logout();
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::deniedIframeAction
     * @covers Mage_Backend_Adminhtml_AuthController::_getDeniedIframe
     * @magentoDbIsolation enabled
     */
    public function testDeniedIframeAction()
    {
        $this->_login();
        $homeUrl = Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl();
        $this->dispatch('backend/admin/auth/deniedIframe');
        $expected = '<script type="text/javascript">parent.window.location =';
        $this->assertStringStartsWith($expected, $this->getResponse()->getBody());
        $this->assertContains($homeUrl, $this->getResponse()->getBody());
        $this->_logout();
    }

    /**
     * Test user logging process when user not assigned to any role
     * @dataProvider incorrectLoginDataProvider
     * @magentoDbIsolation enabled
     *
     * @param $params
     */
    public function testIncorrectLogin($params)
    {
        $this->getRequest()->setPost($params);
        $this->dispatch('backend/admin/auth/login');
        $this->assertContains('Please correct the user name or password.', $this->getResponse()->getBody());
    }

    public function incorrectLoginDataProvider()
    {
        return array(
            'login dummy user' => array (
                array(
                    'login' => array(
                        'username' => 'test1',
                        'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
                    )
                ),
            ),
            'login without role' => array (
                array(
                    'login' => array(
                        'username' => 'test2',
                        'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
                    )
                ),
            ),
            'login not active user' => array (
                array(
                    'login' => array(
                        'username' => 'test3',
                        'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
                    )
                ),
            ),
        );
    }
}
