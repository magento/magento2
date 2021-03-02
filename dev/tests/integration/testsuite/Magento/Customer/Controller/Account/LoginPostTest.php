<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class checks customer login action
 *
 * @see \Magento\Customer\Controller\Account\LoginPost
 */
class LoginPostTest extends AbstractController
{
    /** @var Session */
    private $session;

    /** @var EncoderInterface */
    private $urlEncoder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->session = $this->_objectManager->get(Session::class);
        $this->urlEncoder = $this->_objectManager->get(EncoderInterface::class);
    }

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @dataProvider missingParametersDataProvider
     *
     * @param string|null $email
     * @param string|null $password
     * @param string $expectedErrorMessage
     * @return void
     */
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
    public function missingParametersDataProvider(): array
    {
        return [
            'missing_email' => [
                'email' => null,
                'password' => 'password',
                'expected_error_message' => 'A login and a password are required.',
            ],
            'missing_password' => [
                'email' => 'customer@example.com',
                'password' => null,
                'expected_error_message' => 'A login and a password are required.',
            ],
            'missing_both_parameters' => [
                'email' => null,
                'password' => null,
                'expected_error_message' => 'A login and a password are required.',
            ],
            'wrong_email' => [
                'email' => 'wrongemail@example.com',
                'password' => 'password',
                'expected_error_message' => 'The account sign-in was incorrect or your account is disabled temporarily.'
                    . ' Please wait and try again later.',
            ],
            'wrong_password' => [
                'email' => 'customer@example.com',
                'password' => 'wrongpassword',
                'expected_error_message' => 'The account sign-in was incorrect or your account is disabled temporarily.'
                    . ' Please wait and try again later.',
            ],
        ];
    }

    /**
     * Tests correct message appears when Sign In with unconfirmed Customer.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     * @magentoDataFixture Magento/Customer/_files/unconfirmed_customer.php
     *
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @return void
     */
    public function testLoginWithUnconfirmedCustomer(): void
    {
        $email = 'unconfirmedcustomer@example.com';
        $urlBuilder = $this->_objectManager->get(UrlInterface::class);
        $this->prepareRequest($email, 'Qwert12345');
        $this->dispatch('customer/account/loginPost');
        $this->assertEquals($email, $this->session->getUsername());
        $expectedMessage = __(
            'This account is not confirmed. <a href="'
            . $urlBuilder->getUrl('customer/account/confirmation', ['_query' => ['email' => $email]])
            . '">Click here</a> to resend confirmation email.'
        );
        $this->assertSessionMessages($this->equalTo([(string)$expectedMessage]), MessageInterface::TYPE_ERROR);
        $errorMessage = current($this->getMessages(MessageInterface::TYPE_ERROR));
        $entities = ['&lt;', '&gt;', '&quot;', '&#039;', '&amp;'];
        foreach ($entities as $entity) {
            $this->assertStringNotContainsString($entity, $errorMessage);
        }
        $this->assertRedirect($this->stringContains('customer/account/login'));
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
