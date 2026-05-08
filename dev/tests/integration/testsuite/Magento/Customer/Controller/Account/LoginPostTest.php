<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\ResourceModel\Visitor as VisitorResource;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Session\Generic;
use Magento\Framework\Url\EncoderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class checks customer login action
 *
 * @see \Magento\Customer\Controller\Account\LoginPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPostTest extends AbstractController
{
    /** @var Session */
    private $session;

    /** @var EncoderInterface */
    private $urlEncoder;

    /**
     * @var Url
     */
    private $customerUrl;

    /**
     * @var Generic
     */
    private $generic;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var VisitorResource
     */
    private $visitorResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->session = $this->_objectManager->get(Session::class);
        $this->urlEncoder = $this->_objectManager->get(EncoderInterface::class);
        $this->customerUrl = $this->_objectManager->get(Url::class);
        $this->generic = $this->_objectManager->get(Generic::class);
        $this->customerResource = $this->_objectManager->get(CustomerResource::class);
        $this->visitorResource = $this->_objectManager->get(VisitorResource::class);
    }

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @param string|null $email
     * @param string|null $password
     * @param string $expectedErrorMessage
     * @return void
     */
    #[DataProvider('missingParametersDataProvider')]
    public function testLoginIncorrectParameters(?string $email, ?string $password, string $expectedErrorMessage): void
    {
        $this->prepareRequest($email, $password);
        $this->dispatch('customer/account/loginPost');
        $this->assertSessionMessages(
            $this->equalTo([(string)__($expectedErrorMessage)]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @return array
     */
    public static function missingParametersDataProvider(): array
    {
        return [
            'missing_email' => [
                'email' => null,
                'password' => 'password',
                'expectedErrorMessage' => 'A login and a password are required.',
            ],
            'missing_password' => [
                'email' => 'customer@example.com',
                'password' => null,
                'expectedErrorMessage' => 'A login and a password are required.',
            ],
            'missing_both_parameters' => [
                'email' => null,
                'password' => null,
                'expectedErrorMessage' => 'A login and a password are required.',
            ],
            'wrong_email' => [
                'email' => 'wrongemail@example.com',
                'password' => 'password',
                'expectedErrorMessage' => 'The account sign-in was incorrect or your account is disabled temporarily.'
                    . ' Please wait and try again later.',
            ],
            'wrong_password' => [
                'email' => 'customer@example.com',
                'password' => 'wrongpassword',
                'expectedErrorMessage' => 'The account sign-in was incorrect or your account is disabled temporarily.'
                    . ' Please wait and try again later.',
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     * @magentoDataFixture Magento/Customer/_files/unconfirmed_customer.php
     *
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @return void
     */
    public function testLoginWithUnconfirmedPassword(): void
    {
        $email = 'unconfirmedcustomer@example.com';
        $this->prepareRequest($email, 'Qwert12345');
        $this->dispatch('customer/account/loginPost');
        $this->assertEquals($email, $this->session->getUsername());
        $message = __(
            'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
            $this->customerUrl->getEmailConfirmationUrl($this->session->getUsername())
        );
        $this->assertSessionMessages(
            $this->equalTo([(string)$message]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoConfigFixture current_store customer/startup/redirect_dashboard 0
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testLoginWithRedirectToDashboardDisabled(): void
    {
        $this->prepareRequest('customer@example.com', 'password');
        $this->getRequest()->setParam(Url::REFERER_QUERY_PARAM_NAME, $this->urlEncoder->encode('test_redirect'));
        $this->dispatch('customer/account/loginPost');
        $this->assertTrue($this->session->isLoggedIn());
        $this->assertRedirect($this->stringContains('test_redirect'));
    }

    /**
     * @magentoConfigFixture current_store customer/startup/redirect_dashboard 0
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testLoginFailureWithRedirectToDashboardDisabled(): void
    {
        $this->prepareRequest('customer@example.com', 'incorrect');
        $this->dispatch('customer/account/loginPost');
        $this->assertFalse($this->session->isLoggedIn());
        $this->assertRedirect($this->logicalAnd(
            $this->stringContains('customer/account/login'),
            $this->logicalnot($this->stringContains('referer'))
        ));
    }

    /**
     * @magentoConfigFixture current_store customer/startup/redirect_dashboard 0
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testLoginToDashboardWithIncorrectReferrer(): void
    {
        $redirectUrl = 'https:support.magento.com';
        $this->prepareRequest('customer@example.com', 'password');
        $this->getRequest()->setParam(Url::REFERER_QUERY_PARAM_NAME, $this->urlEncoder->encode($redirectUrl));
        $this->dispatch('customer/account/loginPost');
        $this->assertTrue($this->session->isLoggedIn());
        $this->assertRedirect($this->stringContains('customer/account/'));
    }

    /**
     * @magentoConfigFixture current_store customer/startup/redirect_dashboard 1
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testLoginWithRedirectToDashboard(): void
    {
        $this->prepareRequest('customer@example.com', 'password');
        $this->getRequest()->setParam(Url::REFERER_QUERY_PARAM_NAME, $this->urlEncoder->encode('test_redirect'));
        $this->dispatch('customer/account/loginPost');
        $this->assertTrue($this->session->isLoggedIn());
        $this->assertRedirect($this->stringContains('customer/account/'));
    }

    /**
     * @magentoConfigFixture current_store customer/startup/redirect_dashboard 1
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testNoFormKeyLoginPostAction(): void
    {
        $this->prepareRequest('customer@example.com', 'password');
        $this->getRequest()->setPostValue('form_key', null);
        $this->getRequest()->setParam(Url::REFERER_QUERY_PARAM_NAME, $this->urlEncoder->encode('test_redirect'));
        $this->dispatch('customer/account/loginPost');
        $this->assertFalse($this->session->isLoggedIn());
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo([new Phrase('Invalid Form Key. Please refresh the page.')]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoConfigFixture current_store customer/startup/redirect_dashboard 1
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testVisitorForCustomerLoginPostAction(): void
    {
        $this->assertEmpty($this->generic->getVisitorData());
        $this->prepareRequest('customer@example.com', 'password');
        $this->dispatch('customer/account/loginPost');
        $this->assertTrue($this->session->isLoggedIn());
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertNotEmpty($this->generic->getVisitorData()['visitor_id']);
        $this->assertNotEmpty($this->generic->getVisitorData()['customer_id']);
    }

    /**
     * Login succeeds on first attempt when session_cutoff is set (password reset on other device).
     *
     * @return void
     */
    #[
        Config('customer/startup/redirect_dashboard', 0, ScopeInterface::SCOPE_STORE),
        Config('customer/captcha/enable', 0, ScopeInterface::SCOPE_STORE),
        DataFixture(Customer::class, as: 'customer')
    ]
    public function testLoginSucceedsWhenSessionCutoffSetAfterPasswordResetElsewhere(): void
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->prepareRequest($customer->getEmail(), 'password');
        $this->dispatch('customer/account/loginPost');
        $this->assertTrue($this->session->isLoggedIn());

        $visitorData = $this->generic->getVisitorData();
        $this->assertNotEmpty($visitorData['visitor_id']);
        $visitorId = (int) $visitorData['visitor_id'];
        $customerId = (int) $this->session->getCustomerId();

        $this->session->logout();

        $cutoffTime = time();
        $oldSessionCreationTime = $cutoffTime - 3600;
        $this->customerResource->updateSessionCutOff($customerId, $cutoffTime);
        $this->visitorResource->updateCreatedAt($visitorId, $oldSessionCreationTime);

        $this->prepareRequest($customer->getEmail(), 'password');
        $this->dispatch('customer/account/loginPost');

        $this->assertTrue($this->session->isLoggedIn());
        $this->assertRedirect($this->stringContains('customer/account/'));
    }

    /**
     * Logged-in session is invalidated after password reset on another device.
     *
     * @return void
     */
    #[
        Config('customer/startup/redirect_dashboard', 0, ScopeInterface::SCOPE_STORE),
        Config('customer/captcha/enable', 0, ScopeInterface::SCOPE_STORE),
        DataFixture(Customer::class, as: 'customer')
    ]
    public function testLoggedInSessionIsInvalidatedWhenSessionCutoffIsUpdated(): void
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->prepareRequest($customer->getEmail(), 'password');
        $this->dispatch('customer/account/loginPost');
        $this->assertTrue($this->session->isLoggedIn());

        $customerId = (int)$this->session->getCustomerId();
        $this->customerResource->updateSessionCutOff($customerId, time() + 1);

        $sessionExceptionThrown = false;
        try {
            $this->session->start();
        } catch (SessionException) {
            $sessionExceptionThrown = true;
        }
        $this->assertTrue($sessionExceptionThrown);
    }

    /**
     * Prepare request
     *
     * @param string|null $email
     * @param string|null $password
     * @return void
     */
    private function prepareRequest(?string $email, ?string $password): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([
            'login' => [
                'username' => $email,
                'password' => $password,
            ],
        ]);
    }
}
