<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test captcha observer behavior
 *
 * @magentoAppArea frontend
 */
class CaseCheckOnFrontendUnsuccessfulMessageWhenCaptchaFailedTest extends AbstractController
{
    /**
     * Test incorrect captcha on customer login page
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms user_login
     * @magentoConfigFixture default_store customer/captcha/mode always
     */
    public function testLoginCheckUnsuccessfulMessageWhenCaptchaFailed()
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
        $post = [
            'login' => [
                'username' => 'dummy@dummy.com',
                'password' => 'dummy_password1',
            ],
            'captcha' => ['user_login' => 'wrong_captcha'],
            'form_key' => $formKey->getFormKey(),
        ];

        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setPostValue($post);

        $this->dispatch('customer/account/loginPost');

        $this->assertRedirect($this->stringContains('customer/account/login'));
        $this->assertSessionMessages(
            $this->equalTo(['Incorrect CAPTCHA']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test incorrect captcha on customer forgot password page
     *
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms user_forgotpassword
     * @magentoConfigFixture default_store customer/captcha/mode always
     */
    public function testForgotPasswordCheckUnsuccessfulMessageWhenCaptchaFailed()
    {
        $email = 'dummy@dummy.com';

        $this->getRequest()->setPostValue(['email' => $email]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('customer/account/forgotPasswordPost');

        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['Incorrect CAPTCHA']),
            MessageInterface::TYPE_ERROR
        );
    }
}
