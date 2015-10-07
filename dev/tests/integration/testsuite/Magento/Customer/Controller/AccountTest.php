<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Controller;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;

class AccountTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\Session');
        $session->loginById($customerId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testIndexAction()
    {
        $this->login(1);
        $this->dispatch('customer/account/index');

        $body = $this->getResponse()->getBody();
        $this->assertContains('Green str, 67', $body);
    }

    public function testCreateAction()
    {
        $this->dispatch('customer/account/create');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<input type="text" id="firstname"', $body);
        $this->assertContains('<input type="text" id="lastname"', $body);
        $this->assertContains('<input type="email" name="email" id="email_address"', $body);
        $this->assertContains('<input type="checkbox" name="is_subscribed"', $body);
        $this->assertContains('<input type="password" name="password" id="password"', $body);
        $this->assertContains('<input type="password" name="password_confirmation" title="Confirm Password"', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testLogoutAction()
    {
        $this->login(1);
        $this->dispatch('customer/account/logout');
        $this->assertRedirect($this->stringContains('customer/account/logoutSuccess'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreatepasswordActionWithDirectLink()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $token = Bootstrap::getObjectManager()->get('Magento\Framework\Math\Random')
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);
        $customer->save();

        $this->getRequest()->setParam('token', $token);
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/createPassword');

        $response = $this->getResponse();
        $this->assertEquals(302, $response->getHttpResponseCode());
        $text = $response->getBody();
        $this->assertFalse((bool)preg_match('/' . $token . '/m', $text));
        $this->assertRedirect($this->stringContains('customer/account/createpassword'));

        /** @var \Magento\Customer\Model\Session $customer */
        $session = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $this->assertEquals($token, $session->getRpToken());
        $this->assertEquals($customer->getId(), $session->getRpCustomerId());
        $this->assertNotContains($token, $response->getHeader('Location')->getFieldValue());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreatepasswordActionWithSession()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $token = Bootstrap::getObjectManager()->get('Magento\Framework\Math\Random')
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);
        $customer->save();

        /** @var \Magento\Customer\Model\Session $customer */
        $session = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $session->setRpToken($token);
        $session->setRpCustomerId($customer->getId());

        $this->dispatch('customer/account/createPassword');

        $response = $this->getResponse();
        $text = $response->getBody();
        $this->assertTrue((bool)preg_match('/' . $token . '/m', $text));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreatepasswordActionInvalidToken()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $token = Bootstrap::getObjectManager()->get('Magento\Framework\Math\Random')
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);
        $customer->save();

        $this->getRequest()->setParam('token', 'INVALIDTOKEN');
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/createPassword');

        // should be redirected to forgotpassword page
        $response = $this->getResponse();
        $this->assertEquals(302, $response->getHttpResponseCode());
        $this->assertContains('customer/account/forgotpassword', $response->getHeader('Location')->getFieldValue());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testConfirmActionAlreadyActive()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer')->load(1);

        $this->getRequest()->setParam('key', 'abc');
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/confirm');
        $this->getResponse()->getBody();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store customer/create_account/confirm 0
     */
    public function testNoConfirmCreatePostAction()
    {
        // Setting data for request
        $this->getRequest()
            ->setMethod('POST')
            ->setParam('firstname', 'firstname1')
            ->setParam('lastname', 'lastname1')
            ->setParam('company', '')
            ->setParam('email', 'test1@email.com')
            ->setParam('password', 'password')
            ->setParam('password_confirmation', 'password')
            ->setParam('telephone', '5123334444')
            ->setParam('street', ['1234 fake street', ''])
            ->setParam('city', 'Austin')
            ->setParam('region_id', 57)
            ->setParam('region', '')
            ->setParam('postcode', '78701')
            ->setParam('country_id', 'US')
            ->setParam('default_billing', '1')
            ->setParam('default_shipping', '1')
            ->setParam('is_subscribed', '0')
            ->setPostValue('create_address', true);

        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['Thank you for registering with Main Website Store.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store customer/create_account/confirm 1
     */
    public function testWithConfirmCreatePostAction()
    {
        // Setting data for request
        $email = 'test2@email.com';
        $this->getRequest()
            ->setMethod('POST')
            ->setParam('firstname', 'firstname2')
            ->setParam('lastname', 'lastname2')
            ->setParam('company', '')
            ->setParam('email', $email)
            ->setParam('password', 'password')
            ->setParam('password_confirmation', 'password')
            ->setParam('telephone', '5123334444')
            ->setParam('street', ['1234 fake street', ''])
            ->setParam('city', 'Austin')
            ->setParam('region_id', 57)
            ->setParam('region', '')
            ->setParam('postcode', '78701')
            ->setParam('country_id', 'US')
            ->setParam('default_billing', '1')
            ->setParam('default_shipping', '1')
            ->setParam('is_subscribed', '1')
            ->setPostValue('create_address', true);

        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/index/'));
        $this->assertSessionMessages(
            $this->equalTo([
                'You must confirm your account. Please check your email for the confirmation link or '
                . '<a href="http://localhost/index.php/customer/account/confirmation/email/'
                . $email . '/">click here</a> for a new link.'
            ]),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExistingEmailCreatePostAction()
    {
        // Setting data for request
        $this->getRequest()
            ->setMethod('POST')
            ->setParam('firstname', 'firstname')
            ->setParam('lastname', 'lastname')
            ->setParam('company', '')
            ->setParam('email', 'customer@example.com')
            ->setParam('password', 'password')
            ->setParam('password_confirmation', 'password')
            ->setParam('telephone', '5123334444')
            ->setParam('street', ['1234 fake street', ''])
            ->setParam('city', 'Austin')
            ->setParam('region_id', 57)
            ->setParam('region', '')
            ->setParam('postcode', '78701')
            ->setParam('country_id', 'US')
            ->setParam('default_billing', '1')
            ->setParam('default_shipping', '1')
            ->setParam('is_subscribed', '1')
            ->setPostValue('create_address', true);

        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/create/'));
        $this->assertSessionMessages(
            $this->equalTo(['There is already an account with this email address. ' .
                'If you are sure that it is your email address, ' .
                '<a href="http://localhost/index.php/customer/account/forgotpassword/">click here</a>' .
                ' to get your password and access your account.', ]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testInactiveUserConfirmationAction()
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue(['email' => 'customer@needAconfirmation.com']);

        $this->dispatch('customer/account/confirmation');
        $this->assertRedirect($this->stringContains('customer/account/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Please check your email for confirmation key.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testActiveUserConfirmationAction()
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'email' => 'customer@example.com',
            ]);

        $this->dispatch('customer/account/confirmation');
        $this->assertRedirect($this->stringContains('customer/account/index'));
        $this->assertSessionMessages(
            $this->equalTo(['This email does not require confirmation.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    public function testForgotPasswordPostAction()
    {
        $email = 'customer@example.com';

        $this->getRequest()
            ->setPostValue([
                'email' => $email,
            ]);

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));

        $message = __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $email
        );
        $this->assertSessionMessages(
            $this->equalTo([$message]),
            MessageInterface::TYPE_SUCCESS
        );
    }

    public function testForgotPasswordPostWithBadEmailAction()
    {
        $this->getRequest()
            ->setPostValue([
                'email' => 'bad@email',
            ]);

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['Please correct the email address.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordPostNoTokenAction()
    {
        $this->getRequest()
            ->setParam('id', 1)
            ->setParam('token', '8ed8677e6c79e68b94e61658bd756ea5')
            ->setPostValue([
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $this->dispatch('customer/account/resetPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['Something went wrong while saving the new password.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_rp_token.php
     * @magentoConfigFixture customer/password/reset_link_expiration_period 10
     */
    public function testResetPasswordPostAction()
    {
        $this->getRequest()
            ->setQueryValue('id', 1)
            ->setQueryValue('token', '8ed8677e6c79e68b94e61658bd756ea5')
            ->setPostValue([
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $this->dispatch('customer/account/resetPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/login'));
        $this->assertSessionMessages(
            $this->equalTo(['You updated your password.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testEditAction()
    {
        $this->login(1);

        $this->dispatch('customer/account/edit');

        $body = $this->getResponse()->getBody();
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode(), $body);
        $this->assertContains('<div class="field field-name-firstname required">', $body);
        // Verify the password check box is not checked
        $this->assertContains('<input type="checkbox" name="change_password" id="change-password" value="1" ' .
            'title="Change Password" class="checkbox"/>', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePasswordEditAction()
    {
        $this->login(1);

        $this->dispatch('customer/account/edit/changepass/1');

        $body = $this->getResponse()->getBody();
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode(), $body);
        $this->assertContains('<div class="field field-name-firstname required">', $body);
        // Verify the password check box is checked
        $this->assertContains('<input type="checkbox" name="change_password" id="change-password" value="1" ' .
            'title="Change Password" checked="checked" class="checkbox"/>', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testEditPostAction()
    {
        /** @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface */
        $customerRepository = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Smith', $customer->getLastname());
        $this->assertEquals('customer@example.com', $customer->getEmail());

        $this->login(1);
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'form_key'  => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'johndoe@email.com',
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['You saved the account information.']),
            MessageInterface::TYPE_SUCCESS
        );

        $customer = $customerRepository->getById(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Doe', $customer->getLastname());
        $this->assertEquals('johndoe@email.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePasswordEditPostAction()
    {
        /** @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface */
        $customerRepository = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Smith', $customer->getLastname());
        $this->assertEquals('customer@example.com', $customer->getEmail());

        $this->login(1);
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'form_key'         => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname'        => 'John',
                'lastname'         => 'Doe',
                'email'            => 'johndoe@email.com',
                'change_password'  => 1,
                'current_password' => 'password',
                'password'         => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['You saved the account information.']),
            MessageInterface::TYPE_SUCCESS
        );

        $customer = $customerRepository->getById(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Doe', $customer->getLastname());
        $this->assertEquals('johndoe@email.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testMissingDataEditPostAction()
    {
        $this->login(1);
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'form_key'  => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'bad-email',
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(['Invalid input']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testWrongPasswordEditPostAction()
    {
        $this->login(1);
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'form_key'         => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname'        => 'John',
                'lastname'         => 'Doe',
                'email'            => 'johndoe@email.com',
                'change_password'  => 1,
                'current_password' => 'wrong-password',
                'password'         => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/edit/'));
        // Not sure if its the most secure message. Not changing the behavior for now in the new AccountManagement APIs.
        $this->assertSessionMessages(
            $this->equalTo(['The password doesn\'t match this account.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testWrongConfirmationEditPostAction()
    {
        $this->login(1);
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'form_key'         => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname'        => 'John',
                'lastname'         => 'Doe',
                'email'            => 'johndoe@email.com',
                'change_password'  => 1,
                'current_password' => 'password',
                'password'         => 'new-password',
                'password_confirmation' => 'new-password-no-match',
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(['Confirm your new password.']),
            MessageInterface::TYPE_ERROR
        );
    }
}
