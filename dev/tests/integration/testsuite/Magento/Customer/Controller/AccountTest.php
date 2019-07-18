<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Account\Redirect;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\Request;
use Magento\TestFramework\Response;
use Magento\Theme\Controller\Result\MessagePlugin;
use Zend\Stdlib\Parameters;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var TransportBuilderMock
     */
    private $transportBuilderMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->transportBuilderMock = $this->_objectManager->get(TransportBuilderMock::class);
    }

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

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_password.php
     */
    public function testLoginWithIncorrectPassword()
    {
        $expectedMessage = 'The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.';
        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue(
                [
                    'login' => [
                        'username' => 'customer@example.com',
                        'password' => '123123q'
                    ]
                ]
            );

        $this->dispatch('customer/account/loginPost');
        $this->assertRedirect($this->stringContains('customer/account/login'));
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    $expectedMessage
                ]
            )
        );
    }

    /**
     * Test sign up form displaying.
     */
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
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store general/store_information/name Test special' characters
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @codingStandardsIgnoreEnd
     */
    public function testForgotPasswordEmailMessageWithSpecialCharacters()
    {
        $email = 'customer@example.com';

        $this->getRequest()->setPostValue(['email' => $email]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));

        $subject = $this->transportBuilderMock->getSentMessage()->getSubject();
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
        $customer->setData('confirmation', 'confirmation');
        $customer->save();

        $this->getRequest()->setParam('token', $token);

        $this->dispatch('customer/account/createPassword');

        $response = $this->getResponse();
        $this->assertEquals(302, $response->getHttpResponseCode());
        $text = $response->getBody();
        $this->assertFalse((bool)preg_match('/' . $token . '/m', $text));
        $this->assertRedirect(
            $this->stringContains('customer/account/createpassword')
        );

        /** @var Session $customer */
        $session = Bootstrap::getObjectManager()->get(Session::class);
        $this->assertEquals($token, $session->getRpToken());
        $this->assertNotContains($token, $response->getHeader('Location')->getFieldValue());
        $this->assertCustomerConfirmationEquals(1, null);
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
        $customer->setData('confirmation', 'confirmation');
        $customer->save();

        /** @var \Magento\Customer\Model\Session $customer */
        $session = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
        $session->setRpToken($token);
        $session->setRpCustomerId($customer->getId());

        $this->dispatch('customer/account/createPassword');

        $response = $this->getResponse();
        $text = $response->getBody();
        $this->assertTrue((bool)preg_match('/' . $token . '/m', $text));
        $this->assertCustomerConfirmationEquals(1, null);
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
        $customer->setData('confirmation', 'confirmation');
        $customer->save();

        $this->getRequest()->setParam('token', 'INVALIDTOKEN');
        $this->getRequest()->setParam('id', $customer->getId());

        $this->dispatch('customer/account/createPassword');

        // should be redirected to forgotpassword page
        $response = $this->getResponse();
        $this->assertEquals(302, $response->getHttpResponseCode());
        $this->assertContains('customer/account/forgotpassword', $response->getHeader('Location')->getFieldValue());
        $this->assertCustomerConfirmationEquals(1, 'confirmation');
    }

    /**
     * @param int         $customerId
     * @param string|null $confirmation
     */
    private function assertCustomerConfirmationEquals(int $customerId, string $confirmation = null)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()
                             ->create(\Magento\Customer\Model\Customer::class)->load($customerId);
        $this->assertEquals($confirmation, $customer->getConfirmation());
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
        $this->fillRequestWithAccountData('test1@email.com');
        $this->getRequest()->setPostValue('form_key', null);
        $this->dispatch('customer/account/createPost');

        $this->assertNull($this->getCustomerByEmail('test1@email.com'));
        $this->assertRedirect($this->stringEndsWith('customer/account/create/'));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_disable.php
     */
    public function testNoConfirmCreatePostAction()
    {
        $this->fillRequestWithAccountDataAndFormKey('test1@email.com');
        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringEndsWith('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['Thank you for registering with Main Website Store.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     */
    public function testWithConfirmCreatePostAction()
    {
        $this->fillRequestWithAccountDataAndFormKey('test2@email.com');
        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/index/'));
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'You must confirm your account. Please check your email for the confirmation link or '
                    . '<a href="http://localhost/index.php/customer/account/confirmation/'
                    . '?email=test2%40email.com">click here</a> for a new link.'
                ]
            ),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExistingEmailCreatePostAction()
    {
        $this->fillRequestWithAccountDataAndFormKey('customer@example.com');
        $this->dispatch('customer/account/createPost');
        $this->assertRedirect($this->stringContains('customer/account/create/'));
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'There is already an account with this email address. ' .
                    'If you are sure that it is your email address, ' .
                    '<a href="http://localhost/index.php/customer/account/forgotpassword/">click here</a>' .
                    ' to get your password and access your account.',
                ]
            ),
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
            ->setPostValue(
                [
                    'email' => 'customer@needAconfirmation.com',
                ]
            );

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
            ->setPostValue(
                [
                    'email' => 'customer@example.com',
                ]
            );

        $this->dispatch('customer/account/confirmation');
        $this->assertRedirect($this->stringContains('customer/account/index'));
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'This email does not require confirmation.',
                ]
            ),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @codingStandardsIgnoreEnd
     */
    public function testForgotPasswordPostAction()
    {
        $email = 'customer@example.com';

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['email' => $email]);

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
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()
            ->setPostValue(
                [
                    'email' => 'bad@email',
                ]
            );

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['The email address is incorrect. Verify the email address and try again.']),
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
            ->setMethod('POST')
            ->setPostValue(
                [
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                ]
            );

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
            ->setMethod('POST')
            ->setPostValue(
                [
                    'password' => 'new-Password1',
                    'password_confirmation' => 'new-Password1',
                ]
            );

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
        $this->assertContains(
            '<input type="checkbox" name="change_password" id="change-password" '
            . 'data-role="change-password" value="1" title="Change&#x20;Password" class="checkbox" />',
            $body
        );
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
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/account_information/change_email_template customer_account_information_change_email_and_password_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @codingStandardsIgnoreEnd
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
            ->setPostValue(
                [
                    'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'johndoe@email.com',
                    'change_email' => 1,
                    'current_password' => 'password'
                ]
            );

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringContains('customer/account/'));
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
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/account_information/change_email_and_password_template customer_account_information_change_email_and_password_template
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @codingStandardsIgnoreEnd
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
            ->setPostValue(
                [
                    'form_key'         => $this->_objectManager->get(FormKey::class)->getFormKey(),
                    'firstname'        => 'John',
                    'lastname'         => 'Doe',
                    'email'            => 'johndoe@email.com',
                    'change_password'  => 1,
                    'change_email'     => 1,
                    'current_password' => 'password',
                    'password'         => 'new-Password1',
                    'password_confirmation' => 'new-Password1',
                ]
            );

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringContains('customer/account/'));
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
            ->setPostValue(
                [
                    'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'change_email' => 1,
                    'current_password' => 'password',
                    'email' => 'bad-email',
                ]
            );

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringContains('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(['&quot;Email&quot; is not a valid email address.']),
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
            ->setPostValue(
                [
                    'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'johndoe@email.com',
                    'change_password' => 1,
                    'current_password' => 'wrong-password',
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                ]
            );

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringContains('customer/account/edit/'));
        // Not sure if its the most secure message. Not changing the behavior for now in the new AccountManagement APIs.
        $this->assertSessionMessages(
            $this->equalTo(["The password doesn&#039;t match this account. Verify the password and try again."]),
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
            ->setPostValue(
                [
                    'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'johndoe@email.com',
                    'change_password' => 1,
                    'current_password' => 'password',
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password-no-match',
                ]
            );

        $this->dispatch('customer/account/editPost');

        $this->assertRedirect($this->stringContains('customer/account/edit/'));
        $this->assertSessionMessages(
            $this->equalTo(['Password confirmation doesn&#039;t match entered password.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test redirect customer to account dashboard after logging in.
     *
     * @param bool|null $redirectDashboard
     * @param string $redirectUrl
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider loginPostRedirectDataProvider
     */
    public function testLoginPostRedirect($redirectDashboard, string $redirectUrl)
    {
        if (isset($redirectDashboard)) {
            $this->_objectManager->get(ScopeConfigInterface::class)->setValue(
                'customer/startup/redirect_dashboard',
                $redirectDashboard
            );
        }
        $this->_objectManager->get(Redirect::class)->setRedirectCookie('test');
        $configValue = $this->_objectManager->create(Value::class);
        $configValue->load('web/unsecure/base_url', 'path');
        $baseUrl = $configValue->getValue() ?: 'http://localhost/';
        $request = $this->prepareRequest();
        $app = $this->_objectManager->create(Http::class, ['_request' => $request]);
        $response = $app->launch();
        $this->assertResponseRedirect($response, $baseUrl . $redirectUrl);
        $this->assertTrue($this->_objectManager->get(Session::class)->isLoggedIn());
    }

    /**
     * Test that confirmation email address displays special characters correctly.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_email_address_with_special_chars.php
     *
     * @return void
     */
    public function testConfirmationEmailWithSpecialCharacters(): void
    {
        $email = 'customer+confirmation@example.com';
        $this->dispatch('customer/account/confirmation/email/customer%2Bconfirmation%40email.com');
        $this->getRequest()->setPostValue('email', $email);
        $this->dispatch('customer/account/confirmation/email/customer%2Bconfirmation%40email.com');

        $this->assertRedirect($this->stringContains('customer/account/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Please check your email for confirmation key.']),
            MessageInterface::TYPE_SUCCESS
        );

        /** @var $message \Magento\Framework\Mail\Message */
        $message = $this->transportBuilderMock->getSentMessage();
        $rawMessage = $message->getRawMessage();

        $this->assertContains('To: ' . $email, $rawMessage);

        $content = $message->getBody()->getPartContent(0);
        $confirmationUrl = $this->getConfirmationUrlFromMessageContent($content);
        $this->setRequestInfo($confirmationUrl, 'confirm');
        $this->clearCookieMessagesList();
        $this->dispatch($confirmationUrl);

        $this->assertRedirect($this->stringContains('customer/account/index'));
        $this->assertSessionMessages(
            $this->equalTo(['Thank you for registering with Main Website Store.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Data provider for testLoginPostRedirect.
     *
     * @return array
     */
    public function loginPostRedirectDataProvider()
    {
        return [
            [null, 'index.php/'],
            [0, 'index.php/'],
            [1, 'index.php/customer/account/'],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoAppArea frontend
     */
    public function testCheckVisitorModel()
    {
        /** @var \Magento\Customer\Model\Visitor $visitor */
        $visitor = $this->_objectManager->get(\Magento\Customer\Model\Visitor::class);
        $this->login(1);
        $this->assertNull($visitor->getId());
        $this->dispatch('customer/account/index');
        $this->assertNotNull($visitor->getId());
    }

    /**
     * @param string $email
     * @return void
     */
    private function fillRequestWithAccountData($email)
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setParam('firstname', 'firstname1')
            ->setParam('lastname', 'lastname1')
            ->setParam('company', '')
            ->setParam('email', $email)
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
     * @param string $email
     * @return void
     */
    private function fillRequestWithAccountDataAndFormKey($email)
    {
        $this->fillRequestWithAccountData($email);
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

    /**
     * Prepare request for customer login.
     *
     * @return Request
     */
    private function prepareRequest()
    {
        $post = new Parameters(
            [
                'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
                'login' => [
                    'username' => 'customer@example.com',
                    'password' => 'password'
                ]
            ]
        );
        $request = $this->getRequest();
        $formKey = $this->_objectManager->get(FormKey::class);
        $request->setParam('form_key', $formKey->getFormKey());
        $request->setMethod(Request::METHOD_POST);
        $request->setRequestUri('customer/account/loginPost/');
        $request->setPost($post);
        return $request;
    }

    /**
     * Assert response is redirect.
     *
     * @param Response $response
     * @param string $redirectUrl
     * @return void
     */
    private function assertResponseRedirect(Response $response, string $redirectUrl)
    {
        $this->assertTrue($response->isRedirect());
        $this->assertSame($redirectUrl, $response->getHeader('Location')->getUri());
    }

    /**
     * Add new request info (request uri, path info, action name).
     *
     * @param string $uri
     * @param string $actionName
     * @return void
     */
    private function setRequestInfo(string $uri, string $actionName): void
    {
        $this->getRequest()
            ->setRequestUri($uri)
            ->setPathInfo()
            ->setActionName($actionName);
    }

    /**
     * Clear cookie messages list.
     *
     * @return void
     */
    private function clearCookieMessagesList(): void
    {
        $cookieManager = $this->_objectManager->get(CookieManagerInterface::class);
        $jsonSerializer = $this->_objectManager->get(Json::class);
        $cookieManager->setPublicCookie(
            MessagePlugin::MESSAGES_COOKIES_NAME,
            $jsonSerializer->serialize([])
        );
    }

    /**
     * Get confirmation URL from message content.
     *
     * @param string $content
     * @return string
     */
    private function getConfirmationUrlFromMessageContent(string $content): string
    {
        $confirmationUrl = '';

        if (preg_match('<a\s*href="(?<url>.*?)".*>', $content, $matches)) {
            $confirmationUrl = $matches['url'];
            $confirmationUrl = str_replace('http://localhost/index.php/', '', $confirmationUrl);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $confirmationUrl = html_entity_decode($confirmationUrl);
        }

        return $confirmationUrl;
    }
}
