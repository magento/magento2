<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Controller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
            ->get(\Magento\Customer\Model\Session::class);
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

        $this->assertRegExp('~<input type="text"[^>]*id="firstname"~', $body);
        $this->assertRegExp('~<input type="text"[^>]*id="lastname"~', $body);
        $this->assertRegExp('~<input type="checkbox"[^>]*id="is_subscribed"~', $body);
        $this->assertRegExp('~<input type="email"[^>]*id="email_address"~', $body);
        $this->assertRegExp('~<input type="password"[^>]*id="password"~', $body);
        $this->assertRegExp('~<input type="password"[^>]*id="password-confirmation"~', $body);
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
     * Test that forgot password email message displays special characters correctly.
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store general/store_information/name Test special' characters
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testForgotPasswordEmailMessageWithSpecialCharacters()
    {
        $email = 'customer@example.com';

        $this->getRequest()
            ->setPostValue([
                'email' => $email,
            ]);

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = $this->_objectManager->get(
            \Magento\TestFramework\Mail\Template\TransportBuilderMock::class
        );
        $subject = $transportBuilder->getSentMessage()->getSubject();
        $this->assertContains(
            'Test special\' characters',
            $subject
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreatepasswordActionWithDirectLink()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Customer::class)->load(1);

        $token = Bootstrap::getObjectManager()->get(\Magento\Framework\Math\Random::class)
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
        $session = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
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
            ->create(\Magento\Customer\Model\Customer::class)->load(1);

        $token = Bootstrap::getObjectManager()->get(\Magento\Framework\Math\Random::class)
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);
        $customer->save();

        /** @var \Magento\Customer\Model\Session $customer */
        $session = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
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
            ->create(\Magento\Customer\Model\Customer::class)->load(1);

        $token = Bootstrap::getObjectManager()->get(\Magento\Framework\Math\Random::class)
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
            ->create(\Magento\Customer\Model\Customer::class)->load(1);

        $this->getRequest()->setParam('key', 'abc');
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/confirm');
        $this->getResponse()->getBody();
    }

    /**
     * Tests that without form key user account won't be created
     * and user will be redirected on account creation page again.
     */
    public function testNoFormKeyCreatePostAction()
    {
        $this->fillRequestWithAccountData();
        $this->dispatch('customer/account/createPost');

        $this->assertNull($this->getCustomerByEmail('test1@email.com'));
        $this->assertRedirect($this->stringEndsWith('customer/account/create/'));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testNoConfirmCreatePostAction()
    {
        /** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableScopeConfig */
        $mutableScopeConfig = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);

        $scopeValue = $mutableScopeConfig->getValue(
            'customer/create_account/confirm',
            ScopeInterface::SCOPE_WEBSITES,
            null
        );

        $mutableScopeConfig->setValue(
            'customer/create_account/confirm',
            0,
            ScopeInterface::SCOPE_WEBSITES,
            null
        );

        $this->fillRequestWithAccountDataAndFormKey();
        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringEndsWith('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['Thank you for registering with Main Website Store.']),
            MessageInterface::TYPE_SUCCESS
        );

        $mutableScopeConfig->setValue(
            'customer/create_account/confirm',
            $scopeValue,
            ScopeInterface::SCOPE_WEBSITES,
            null
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testWithConfirmCreatePostAction()
    {
        /** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableScopeConfig */
        $mutableScopeConfig = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);

        $scopeValue = $mutableScopeConfig->getValue(
            'customer/create_account/confirm',
            ScopeInterface::SCOPE_WEBSITES,
            null
        );

        $mutableScopeConfig->setValue(
            'customer/create_account/confirm',
            1,
            ScopeInterface::SCOPE_WEBSITES,
            null
        );

        $this->fillRequestWithAccountDataAndFormKey();
        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/index/'));
        $this->assertSessionMessages(
            $this->equalTo([
                'You must confirm your account. Please check your email for the confirmation link or '
                    . '<a href="http://localhost/index.php/customer/account/confirmation/email/'
                    . 'test1%40email.com/">click here</a> for a new link.'
            ]),
            MessageInterface::TYPE_SUCCESS
        );

        $mutableScopeConfig->setValue(
            'customer/create_account/confirm',
            $scopeValue,
            ScopeInterface::SCOPE_WEBSITES,
            null
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExistingEmailCreatePostAction()
    {
        $this->fillRequestWithAccountDataAndFormKey();
        $this->getRequest()->setParam('email', 'customer@example.com');
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

    /**
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
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

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     */
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
                'password' => 'new-Password1',
                'password_confirmation' => 'new-Password1',
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
        $this->assertContains('<input type="checkbox" name="change_password" id="change-password" '
            . 'data-role="change-password" value="1" title="Change&#x20;Password" class="checkbox" />', $body);
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
        $this->assertContains(
            '<input type="checkbox" name="change_password" id="change-password" '
            . 'data-role="change-password" value="1" title="Change&#x20;Password" checked="checked" '
            . 'class="checkbox" />',
            $body
        );
    }

    /**
     * @magentoConfigFixture current_store customer/account_information/change_email_template customer_account_information_change_email_and_password_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testEditPostAction()
    {
        /** @var $customerRepository CustomerRepositoryInterface */
        $customerRepository = Bootstrap::getObjectManager()
            ->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Smith', $customer->getLastname());
        $this->assertEquals('customer@example.com', $customer->getEmail());

        $this->login(1);
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'form_key'  => $this->_objectManager->get(FormKey::class)->getFormKey(),
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'johndoe@email.com',
                'change_email'     => 1,
                'current_password' => 'password'
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
     * @magentoConfigFixture current_store customer/account_information/change_email_and_password_template customer_account_information_change_email_and_password_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePasswordEditPostAction()
    {
        /** @var $customerRepository CustomerRepositoryInterface */
        $customerRepository = Bootstrap::getObjectManager()
            ->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        $this->assertEquals('John', $customer->getFirstname());
        $this->assertEquals('Smith', $customer->getLastname());
        $this->assertEquals('customer@example.com', $customer->getEmail());

        $this->login(1);
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue([
                'form_key'         => $this->_objectManager->get(
                    FormKey::class)->getFormKey(),
                'firstname'        => 'John',
                'lastname'         => 'Doe',
                'email'            => 'johndoe@email.com',
                'change_password'  => 1,
                'change_email'     => 1,
                'current_password' => 'password',
                'password'         => 'new-Password1',
                'password_confirmation' => 'new-Password1',
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
                'form_key'  => $this->_objectManager->get(FormKey::class)->getFormKey(),
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'change_email'  => 1,
                'current_password'  => 'password',
                'email'     => 'bad-email',
            ]);

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringEndsWith('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(['"Email" is not a valid email address.']),
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
                'form_key'         => $this->_objectManager->get(
                    FormKey::class)->getFormKey(),
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
                'form_key'         => $this->_objectManager->get(
                    FormKey::class)->getFormKey(),
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
            $this->equalTo(['Password confirmation doesn\'t match entered password.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @return void
     */
    private function fillRequestWithAccountData()
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setParam('firstname', 'firstname1')
            ->setParam('lastname', 'lastname1')
            ->setParam('company', '')
            ->setParam('email', 'test1@email.com')
            ->setParam('password', '_Password1')
            ->setParam('password_confirmation', '_Password1')
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
    }

    /**
     * @return void
     */
    private function fillRequestWithAccountDataAndFormKey()
    {
        $this->fillRequestWithAccountData();
        $formKey = $this->_objectManager->get(FormKey::class);
        $this->getRequest()->setParam('form_key', $formKey->getFormKey());
    }

    /**
     * Returns stored customer by email.
     *
     * @param string $email
     * @return CustomerInterface
     */
    private function getCustomerByEmail($email)
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->_objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(CustomerInterface::EMAIL)
                ->setValue($email)
                ->create()
        ];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $customers = $customerRepository->getList($searchCriteria)
            ->getItems();

        $customer = array_pop($customers);

        return $customer;
    }
}
