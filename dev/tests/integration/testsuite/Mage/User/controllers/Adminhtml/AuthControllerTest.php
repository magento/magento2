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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_User_Adminhtml_AuthController.
 */
class Mage_User_Adminhtml_AuthControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * Test form existence
     * @covers Mage_User_Adminhtml_AuthController::forgotpasswordAction
     */
    public function testFormForgotpasswordAction()
    {
        $this->dispatch('backend/admin/auth/forgotpassword');
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
        $this->dispatch('backend/admin/auth/forgotpassword');
        $this->assertRedirect($this->equalTo(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl()));
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
        /** @var $user Mage_User_Model_User */
        $user = Mage::getModel('Mage_User_Model_User')->loadByUsername('dummy_username');
        $this->assertNotEmpty($user->getId(), 'Broken fixture');
        $resetPasswordToken = Mage::helper('Mage_User_Helper_Data')->generateResetPasswordLinkToken();
        $user->changeResetPasswordLinkToken($resetPasswordToken);
        $user->save();

        $this->getRequest()
            ->setQuery('token', $resetPasswordToken)
            ->setQuery('id', $user->getId());
        $this->dispatch('backend/admin/auth/resetpassword');

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
        $this->dispatch('backend/admin/auth/resetpassword');
        $this->assertSessionMessages(
            $this->equalTo(array('Your password reset link has expired.')), Mage_Core_Model_Message::ERROR
        );
        $this->assertRedirect();
    }

    /**
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordPostAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     * @magentoDataFixture Mage/User/_files/dummy_user.php
     */
    public function testResetPasswordPostAction()
    {
        /** @var $user Mage_User_Model_User */
        $user = Mage::getModel('Mage_User_Model_User')->loadByUsername('dummy_username');
        $this->assertNotEmpty($user->getId(), 'Broken fixture');
        $resetPasswordToken = Mage::helper('Mage_User_Helper_Data')->generateResetPasswordLinkToken();
        $user->changeResetPasswordLinkToken($resetPasswordToken);
        $user->save();

        $newDummyPassword = 'new_dummy_password2';

        $this->getRequest()
            ->setQuery('token', $resetPasswordToken)
            ->setQuery('id', $user->getId())
            ->setPost('password', $newDummyPassword)
            ->setPost('confirmation', $newDummyPassword);

        $this->dispatch('backend/admin/auth/resetpasswordpost');

        $this->assertRedirect($this->equalTo(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl()));

        /** @var $user Mage_User_Model_User */
        $user = Mage::getModel('Mage_User_Model_User')->loadByUsername('dummy_username');
        $this->assertTrue(Mage::helper('Mage_Core_Helper_Data')->validateHash($newDummyPassword, $user->getPassword()));
    }

    /**
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordPostAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     * @magentoDataFixture Mage/User/_files/dummy_user.php
     */
    public function testResetPasswordPostActionWithDummyToken()
    {
        $this->getRequest()->setQuery('token', 'dummy')->setQuery('id', 1);
        $this->dispatch('backend/admin/auth/resetpasswordpost');
        $this->assertSessionMessages(
            $this->equalTo(array('Your password reset link has expired.')), Mage_Core_Model_Message::ERROR
        );
        $this->assertRedirect($this->equalTo(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl()));
    }

    /**
     * @covers Mage_User_Adminhtml_AuthController::resetPasswordPostAction
     * @covers Mage_User_Adminhtml_AuthController::_validateResetPasswordLinkToken
     * @magentoDataFixture Mage/User/_files/dummy_user.php
     */
    public function testResetPasswordPostActionWithInvalidPassword()
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

        $this->dispatch('backend/admin/auth/resetpasswordpost');

        $this->assertSessionMessages(
            $this->equalTo(array('Password confirmation must be same as password.')), Mage_Core_Model_Message::ERROR
        );
        $this->assertRedirect();
    }
}
