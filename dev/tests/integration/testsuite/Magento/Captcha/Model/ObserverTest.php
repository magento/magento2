<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

        $post = [
            'login' => [
                'username' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
            ],
            'captcha' => ['backend_login' => 'some_unrealistic_captcha_value'],
        ];
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
            'Magento\Store\Model\StoreManagerInterface'
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
            ['email' => 'dummy@dummy.com', 'captcha' => ['backend_forgotpassword' => 'dummy']]
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
        $this->getRequest()->setPost(['email' => 'dummy@dummy.com', 'captcha' => '1234']);
        $this->dispatch('backend/admin/auth/forgotpassword');
        $this->assertSessionMessages(
            $this->equalTo(['Incorrect CAPTCHA']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
