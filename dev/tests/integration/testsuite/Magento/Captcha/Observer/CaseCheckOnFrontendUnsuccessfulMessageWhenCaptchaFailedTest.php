<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Data\Form\FormKey;
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
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'login' => [
                'username' => 'dummy@dummy.com',
                'password' => 'dummy_password1',
            ],
            'captcha' => ['user_login' => 'wrong_captcha'],
            'form_key' => $formKey->getFormKey(),
        ];

        $this->prepareRequestData($post);

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
        $post = ['email' => 'dummy@dummy.com'];
        $this->prepareRequestData($post);

        $this->dispatch('customer/account/forgotPasswordPost');

        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['Incorrect CAPTCHA']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test incorrect captcha on customer create account page
     *
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms user_create
     * @magentoConfigFixture default_store customer/captcha/mode always
     */
    public function testCreateAccountCheckUnsuccessfulMessageWhenCaptchaFailed()
    {
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'email' => 'dummy@dummy.com',
            'password' => 'TestPassword123',
            'password_confirmation' => 'TestPassword123',
            'captcha' => ['user_create' => 'wrong_captcha'],
            'form_key' => $formKey->getFormKey(),
        ];
        $this->prepareRequestData($post);

        $this->dispatch('customer/account/createPost');

        $this->assertRedirect($this->stringContains('customer/account/create'));
        $this->assertSessionMessages(
            $this->equalTo(['Incorrect CAPTCHA']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @param array $postData
     * @return void
     */
    private function prepareRequestData($postData)
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
    }
}
