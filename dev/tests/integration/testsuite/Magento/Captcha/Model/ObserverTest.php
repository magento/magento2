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
namespace Magento\Captcha\Model;

/**
 * Test captcha observer behavior
 *
 * @magentoAppArea adminhtml
 */
class ObserverTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoAdminConfigFixture admin/captcha/forms backend_login
     * @magentoAdminConfigFixture admin/captcha/enable 1
     * @magentoAdminConfigFixture admin/captcha/mode always
     */
    public function testBackendLoginActionWithInvalidCaptchaReturnsError()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        )->turnOffSecretKey();

        $post = array(
            'login' => array(
                'username' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
            ),
            'captcha' => array('backend_login' => 'some_unrealistic_captcha_value')
        );
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/admin');
        $this->assertContains(__('Incorrect CAPTCHA'), $this->getResponse()->getBody());
    }

    /**
     * @magentoAdminConfigFixture admin/captcha/enable 1
     * @magentoAdminConfigFixture admin/captcha/forms backend_login
     * @magentoAdminConfigFixture admin/captcha/mode after_fail
     * @magentoAdminConfigFixture admin/captcha/failed_attempts_login 1
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCaptchaIsRequiredAfterFailedLoginAttempts()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->setCurrentStore(
            0
        );
        $captchaModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Captcha\Helper\Data'
        )->getCaptcha(
            'backend_login'
        );

        try {
            $authModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Backend\Model\Auth'
            );
            $authModel->login(\Magento\TestFramework\Bootstrap::ADMIN_NAME, 'wrong_password');
        } catch (\Exception $e) {
        }

        $this->assertTrue($captchaModel->isRequired());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Captcha/_files/dummy_user.php
     * @magentoAdminConfigFixture admin/captcha/enable 1
     * @magentoAdminConfigFixture admin/captcha/forms backend_forgotpassword
     * @magentoAdminConfigFixture admin/captcha/mode always
     */
    public function testCheckUserForgotPasswordBackendWhenCaptchaFailed()
    {
        $this->getRequest()->setPost(
            array('email' => 'dummy@dummy.com', 'captcha' => array('backend_forgotpassword' => 'dummy'))
        );
        $this->dispatch('backend/admin/auth/forgotpassword');
        $this->assertRedirect($this->stringContains('backend/admin/auth/forgotpassword'));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAdminConfigFixture admin/captcha/enable 1
     * @magentoAdminConfigFixture admin/captcha/forms backend_forgotpassword
     * @magentoAdminConfigFixture admin/captcha/mode always
     */
    public function testCheckUnsuccessfulMessageWhenCaptchaFailed()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        )->turnOffSecretKey();
        $this->getRequest()->setPost(array('email' => 'dummy@dummy.com', 'captcha' => '1234'));
        $this->dispatch('backend/admin/auth/forgotpassword');
        $this->assertSessionMessages(
            $this->equalTo(array('Incorrect CAPTCHA')),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
