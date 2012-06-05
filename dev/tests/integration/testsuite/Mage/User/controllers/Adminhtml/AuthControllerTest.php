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
 * @package     Magento_User
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_User_Adminhtml_AuthController.
 *
 * @group module:Mage_User
 */
class Mage_User_Adminhtml_AuthControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var Mage_Backend_Model_Auth
     */
    protected $_auth;

    protected  function _login()
    {
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOffSecretKey();

        $this->_auth = Mage::getSingleton('Mage_Backend_Model_Auth');
        $this->_auth->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
    }

    protected  function _logout()
    {
        $this->_auth->logout();
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOnSecretKey();
    }

    /**
     * Test form existence
     * @covers Mage_User_Adminhtml_AuthController::forgotpasswordAction
     */
    public function testFormForgotpasswordAction()
    {
        $this->dispatch('admin/auth/forgotpassword');
        $expected = 'Forgot your user name or password?';
        $this->assertContains($expected, $this->getResponse()->getBody());
    }

    /**
     * Test redirection to startup page after success password recovering posting
     *
     * @covers Mage_User_Adminhtml_AuthController::forgotpasswordAction
     */
    public function testForgotpasswordAction()
    {
        $this->getRequest()->setPost('email', 'test@test.com');
        $this->dispatch('admin/auth/forgotpassword');
        $this->assertRedirect(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl());
    }

    /**
     * Test reset password action
     *
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     * @magentoDataFixture Mage/User/_files/dummy_user.php
     */
    public function testResetPasswordAction()
    {
        $user = Mage::getModel('Mage_User_Model_User')->loadByUsername('dummy_username');
        $resetPasswordToken = null;
        if ($user->getId()) {
            $resetPasswordToken = Mage::helper('Mage_User_Helper_Data')
                ->generateResetPasswordLinkToken();
            $user->changeResetPasswordLinkToken($resetPasswordToken);
            $user->save();
        }

        $this->getRequest()
            ->setQuery('token', $resetPasswordToken)
            ->setQuery('id', $user->getId());
        $this->dispatch('admin/auth/resetpassword');

        $this->assertEquals('adminhtml', $this->getRequest()->getRouteName());
        $this->assertEquals('auth', $this->getRequest()->getControllerName());
        $this->assertEquals('resetpassword', $this->getRequest()->getActionName());

        $this->assertContains($resetPasswordToken, $this->getResponse()->getBody());
    }

    /**
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     */
    public function testResetPasswordActionWithDummyToken()
    {
        $this->getRequest()->setQuery('token', 'dummy')->setQuery('id', 1);
        $this->dispatch('admin/auth/resetpassword');
        $this->assertRedirect();
    }

    /**
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordPostAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     * @magentoDataFixture Mage/User/_files/dummy_user.php
     */
    public function testResetPasswordPostAction()
    {
        $user = Mage::getModel('Mage_User_Model_User')->loadByUsername('dummy_username');
        $resetPasswordToken = null;
        if ($user->getId()) {
            $resetPasswordToken = Mage::helper('Mage_User_Helper_Data')
                ->generateResetPasswordLinkToken();
            $user->changeResetPasswordLinkToken($resetPasswordToken);
            $user->save();
        }

        $newDummyPassword = 'new_dummy_password2';

        $this->getRequest()
            ->setQuery('token', $resetPasswordToken)
            ->setQuery('id', $user->getId())
            ->setPost('password', $newDummyPassword)
            ->setPost('confirmation', $newDummyPassword);

        $this->dispatch('admin/auth/resetpasswordpost');

        $this->assertRedirect(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl());

        $user = Mage::getModel('Mage_User_Model_User')
            ->loadByUsername('dummy_username');

        $this->assertTrue(Mage::helper('Mage_Core_Helper_Data')->validateHash($newDummyPassword, $user->getPassword()));
    }

    /**
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordPostAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     * @magentoDataFixture Mage/User/_files/dummy_user.php
     */
    public function testResetPaswordPostActionWithDummyToken()
    {
        $this->getRequest()->setQuery('token', 'dummy')->setQuery('id', 1);
        $this->dispatch('admin/auth/resetpasswordpost');

        $this->assertRedirect(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl());
    }

    /**
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordPostAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     * @magentoDataFixture Mage/User/_files/dummy_user.php
     */
    public function testResetPaswordPostActionWithInvalidPassword()
    {
        $user = Mage::getModel('Mage_User_Model_User')->loadByUsername('dummy_username');
        $resetPasswordToken = null;
        if ($user->getId()) {
            $resetPasswordToken = Mage::helper('Mage_User_Helper_Data')
                ->generateResetPasswordLinkToken();
            $user->changeResetPasswordLinkToken($resetPasswordToken);
            $user->save();
        }

        $newDummyPassword = 'new_dummy_password2';

        $this->getRequest()
            ->setQuery('token', $resetPasswordToken)
            ->setQuery('id', $user->getId())
            ->setPost('password', $newDummyPassword)
            ->setPost('confirmation', 'invalid');

        $this->dispatch('admin/auth/resetpasswordpost');

        $this->assertRedirect();
    }

    /**
     * Empty data fixture to provide support of transaction
     * @static
     *
     */
    public static function emptyDataFixture()
    {

    }
}
