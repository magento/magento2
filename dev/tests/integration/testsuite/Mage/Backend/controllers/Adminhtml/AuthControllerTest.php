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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Adminhtml_AuthController.
 *
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

    /**
     * @var Mage_User_Model_User
     */
    protected static $_newUser;

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
        $this->dispatch('admin/auth/login');
        $this->assertFalse($this->getResponse()->isRedirect());
        $expected = 'Log in to Admin Panel';
        $this->assertContains($expected, $this->getResponse()->getBody(), 'There is no login form');
    }

    /**
     * Check logged state
     * @covers Mage_Backend_Adminhtml_AuthController::loginAction
     * @magentoDataFixture emptyDataFixture
     */
    public function testLoggedLoginAction()
    {
        $this->_login();

        $this->dispatch('admin/auth/login');
        $expected = Mage::getSingleton('Mage_Backend_Model_Url')->getUrl('adminhtml/dashboard');
        $this->assertRedirect($expected, self::MODE_START_WITH);

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

        $this->dispatch('admin/index/index');

        $response = Mage::app()->getResponse();
        $code = $response->getHttpResponseCode();
        $this->assertTrue($code >= 300 && $code < 400, 'Incorrect response code');

        $this->assertTrue(Mage::getSingleton('Mage_Backend_Model_Auth')->isLoggedIn());
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::logoutAction
     * @magentoDataFixture emptyDataFixture
     */
    public function testLogoutAction()
    {
        $this->_login();
        $this->dispatch('admin/auth/logout');
        $this->assertRedirect(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl(), self::MODE_EQUALS);
        $this->assertFalse($this->_session->isLoggedIn(), 'User is not logouted');
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::deniedJsonAction
     * @covers Mage_Backend_Adminhtml_AuthController::_getDeniedJson
     * @magentoDataFixture emptyDataFixture
     */
    public function testDeniedJsonAction()
    {
        $this->_login();
        $this->dispatch('admin/auth/deniedJson');
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
     * @magentoDataFixture emptyDataFixture
     */
    public function testDeniedIframeAction()
    {
        $this->_login();
        $homeUrl = Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl();
        $this->dispatch('admin/auth/deniedIframe');
        $expected = '<script type="text/javascript">parent.window.location =';
        $this->assertStringStartsWith($expected, $this->getResponse()->getBody());
        $this->assertContains($homeUrl, $this->getResponse()->getBody());
        $this->_logout();
    }

    /**
     * Test user logging process when user not assigned to any role
     * @dataProvider incorrectLoginDataProvider
     * @magentoDataFixture userDataFixture
     *
     * @param $params
     */
    public function testIncorrectLogin($params)
    {
        $this->getRequest()->setPost($params);
        $this->dispatch('admin/auth/login');
        $this->assertContains('Invalid User Name or Password', $this->getResponse()->getBody());
    }

    /**
     * Empty data fixture to provide support of transaction
     * @static
     *
     */
    public static function emptyDataFixture()
    {

    }

    public static function userDataFixture()
    {
        self::$_newUser = new Mage_User_Model_User;
        self::$_newUser->setFirstname('admin_role')
            ->setUsername('test2')
            ->setPassword(Magento_Test_Bootstrap::ADMIN_PASSWORD)
            ->setIsActive(1)
            ->save();

        self::$_newUser = new Mage_User_Model_User;
        self::$_newUser->setFirstname('admin_role')
            ->setUsername('test3')
            ->setPassword(Magento_Test_Bootstrap::ADMIN_PASSWORD)
            ->setIsActive(0)
            ->setRoleId(1)
            ->save();
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
