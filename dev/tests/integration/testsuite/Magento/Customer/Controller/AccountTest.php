<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Controller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Theme\Controller\Result\MessagePlugin;
use PHPUnit\Framework\Constraint\StringContains;
use Symfony\Component\Mime\Message;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountTest extends AbstractController
{
    /**
     * @var TransportBuilderMock
     */
    private $transportBuilderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
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
        /** @var Session $session */
        $session = Bootstrap::getObjectManager()->get(Session::class);
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
        $this->assertStringContainsString('Green str, 67', $body);
    }

    /**
     * Test sign up form displaying.
     */
    public function testCreateAction()
    {
        $this->dispatch('customer/account/create');
        $body = $this->getResponse()->getBody();

        $this->assertMatchesRegularExpression('~<input type="text"[^>]*id="firstname"~', $body);
        $this->assertMatchesRegularExpression('~<input type="text"[^>]*id="lastname"~', $body);
        $this->assertMatchesRegularExpression('~<input type="checkbox"[^>]*id="is_subscribed"~', $body);
        $this->assertMatchesRegularExpression('~<input type="email"[^>]*id="email_address"~', $body);
        $this->assertMatchesRegularExpression('~<input type="password"[^>]*id="password"~', $body);
        $this->assertMatchesRegularExpression('~<input type="password"[^>]*id="password-confirmation"~', $body);
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
            ->create(\Magento\Customer\Model\Customer::class)->load(1);

        $token = Bootstrap::getObjectManager()->get(\Magento\Framework\Math\Random::class)
            ->getUniqueHash();
        $customer->changeResetPasswordLinkToken($token);
        $customer->setData('confirmation', 'confirmation');
        $customer->save();

        $this->getRequest()->setParam('token', $token);
        $this->getRequest()->setParam('id', 1);

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
        $this->assertStringNotContainsString($token, $response->getHeader('Location')->getFieldValue());
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

        /** @var Session $customer */
        $session = Bootstrap::getObjectManager()->get(Session::class);
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
        $this->assertStringContainsString(
            'customer/account/forgotpassword',
            $response->getHeader('Location')->getFieldValue()
        );
        $this->assertCustomerConfirmationEquals(1, 'confirmation');
    }

    /**
     * @param int $customerId
     * @param string|null $confirmation
     */
    private function assertCustomerConfirmationEquals(int $customerId, ?string $confirmation = null)
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
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordPostNoEmail()
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
            $this->equalTo(['&quot;email&quot; is required. Enter and try again.']),
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
        $this->assertStringContainsString('<div class="field field-name-firstname required">', $body);
        // Verify the password check box is not checked
        $checkboxXpath = '//input[@type="checkbox"][@name="change_password"][@id="change-password"][not (@checked)]' .
            '[@data-role="change-password"][@value="1"][@title="Change Password"][@class="checkbox"]';

        $this->assertEquals(1, Xpath::getElementsCountForXpath($checkboxXpath, $body));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePasswordEditAction(): void
    {
        $this->login(1);

        $this->dispatch('customer/account/edit/changepass/1');

        $body = $this->getResponse()->getBody();
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode(), $body);
        $this->assertStringContainsString('<div class="field field-name-firstname required">', $body);
        // Verify the password check box is checked
        $checkboxXpath = '//input[@type="checkbox"][@name="change_password"][@id="change-password"]' .
            '[@data-role="change-password"][@value="1"][@title="Change Password"][@checked="checked"]' .
            '[@class="checkbox"]';

        $this->assertEquals(1, Xpath::getElementsCountForXpath($checkboxXpath, $body));
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
                    'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'johndoe@email.com',
                    'change_password' => 1,
                    'change_email' => 1,
                    'current_password' => 'password',
                    'password' => 'new-Password1',
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

        /** @var Message $messageBodyPart */
        $messageBodyPart = $message->getBody();
        $name = 'John Smith';

        $nameEmail = sprintf('%s <%s>', $name, $email);

        $this->assertStringContainsString('To: ' . $nameEmail, $rawMessage);

        $content = $messageBodyPart->getBody();
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
     * Check that Customer which change email can't log in with old email.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function testResetPasswordWhenEmailChanged(): void
    {
        $email = 'customer@example.com';
        $newEmail = 'new_customer@example.com';

        /* Reset password and check mail with token */
        $this->getRequest()->setPostValue(['email' => $email]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    "If there is an account associated with {$email} you will receive an email with a link "
                    . "to reset your password."
                ]
            ),
            MessageInterface::TYPE_SUCCESS
        );

        /** @var CustomerRegistry $customerRegistry */
        $customerRegistry = $this->_objectManager->get(CustomerRegistry::class);
        $customerData = $customerRegistry->retrieveByEmail($email);
        $token = $customerData->getRpToken();
        $customerId = $customerData->getId();
        $this->assertForgotPasswordEmailContent($token, $customerId, $email);

        /* Set new email */
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $customerRepository->getById($customerData->getId());
        $customer->setEmail($newEmail);
        $customerRepository->save($customer);

        $this->resetRequest();

        /* Goes through the link in a mail */
        $this->getRequest()
            ->setParam('token', $token)
            ->setParam('id', $customerData->getId());

        $this->dispatch('customer/account/createPassword');

        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['Your password reset link has expired.']),
            MessageInterface::TYPE_ERROR
        );
        /* Trying to log in with old email */
        $this->resetRequest();
        $this->clearCookieMessagesList();
        $customerRegistry->removeByEmail($email);

        $this->dispatchLoginPostAction($email, 'password');
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                ]
            ),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('customer/account/login'));
        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $this->assertFalse($session->isLoggedIn());

        /* Trying to log in with correct(new) email */
        $this->resetRequest();
        $this->dispatchLoginPostAction($newEmail, 'password');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertTrue($session->isLoggedIn());
        $session->logout();
    }

    /**
     * Set needed parameters and dispatch Customer loginPost action.
     *
     * @param string $email
     * @param string $password
     * @return void
     */
    private function dispatchLoginPostAction(string $email, string $password): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'login' => [
                    'username' => $email,
                    'password' => $password,
                ],
            ]
        );
        $this->dispatch('customer/account/loginPost');
    }

    /**
     * Check that 'Forgot password' email contains correct data.
     *
     * @param string $token
     * @param int $customerId
     * @param string $email
     * @return void
     */
    private function assertForgotPasswordEmailContent(string $token, int $customerId, string $email): void
    {
        $message = $this->transportBuilderMock->getSentMessage();
        $email = urlencode($email);
        //phpcs:ignore
        $pattern = "/<a.+customer\/account\/createPassword\/\?email={$email}&amp;id={$customerId}&amp;token={$token}.+Set\s+a\s+New\s+Password<\/a\>/";
        $rawMessage = quoted_printable_decode($message->getBody()->bodyToString());
        $messageConstraint = $this->logicalAnd(
            new StringContains('There was recently a request to change the password for your account.'),
            $this->matchesRegularExpression($pattern)
        );
        $this->assertThat($rawMessage, $messageConstraint);
    }

    /**
     * @inheritDoc
     */
    protected function resetRequest(): void
    {
        $this->_objectManager->removeSharedInstance(Http::class);
        $this->_objectManager->removeSharedInstance(Request::class);
        parent::resetRequest();
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
