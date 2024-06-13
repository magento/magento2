<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Set of tests to verify e-mail templates delivered to Customers
 *
 * @magentoAppArea frontend
 */
class EmailTemplateTest extends AbstractController
{
    private const FIXTURE_CUSTOMER_EMAIL = 'customer@example.com';
    private const FIXTURE_CUSTOMER_FIRSTNAME = 'John';
    private const FIXTURE_CUSTOMER_LASTNAME = 'Smith';
    private const FIXTURE_CUSTOMER_ID = 1;
    private const FIXTURE_CUSTOMER_PASSWORD = 'password';
    private const EXPECTED_GREETING = self::FIXTURE_CUSTOMER_FIRSTNAME . ' ' . self::FIXTURE_CUSTOMER_LASTNAME . ',';

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilderMock;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var FormKey
     */
    private $formKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transportBuilderMock = $this->_objectManager->get(TransportBuilderMock::class);
        $this->session = $this->_objectManager->get(Session::class);
        $this->formKey = $this->_objectManager->get(FormKey::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/captcha/enable 0
     */
    public function testForgotPasswordEmailTemplateGreeting()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST)
            ->setPostValue(['email' => self::FIXTURE_CUSTOMER_EMAIL]);
        $this->dispatch('customer/account/forgotPasswordPost');

        $this->assertSameGreeting(self::EXPECTED_GREETING, $this->transportBuilderMock->getSentMessage());
    }

    /**
     * Covers Magento_Customer::view/frontend/email/change_email.html
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/captcha/enable 0
     */
    public function testCustomerEmailChangeNotificationTemplateGreeting()
    {
        $this->loginByCustomerId(self::FIXTURE_CUSTOMER_ID);

        $this->sendAccountEditRequest([
            'email' => 'new.email@example.com',
            'change_email' => 1,
        ]);

        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['You saved the account information.']),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertSameGreeting(self::EXPECTED_GREETING, $this->transportBuilderMock->getSentMessage());
    }

    /**
     * Covers Magento_Customer::view/frontend/email/change_email_and_password.html
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/captcha/enable 0
     */
    public function testCustomerEmailAndPasswordChangeNotificationTemplateGreeting()
    {
        $this->loginByCustomerId(self::FIXTURE_CUSTOMER_ID);

        $this->sendAccountEditRequest([
            'email' => 'new.email@example.com',
            'change_email' => 1,
            'change_password' => 1,
            'password' => 'new-Password1',
            'password_confirmation' => 'new-Password1',
        ]);

        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['You saved the account information.']),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertSameGreeting(self::EXPECTED_GREETING, $this->transportBuilderMock->getSentMessage());
    }

    /**
     * Covers Magento_Customer::view/frontend/email/change_password.html
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/captcha/enable 0
     */
    public function testCustomerPasswordChangeNotificationTemplateGreeting()
    {
        $this->loginByCustomerId(self::FIXTURE_CUSTOMER_ID);

        $this->sendAccountEditRequest([
            'change_password' => 1,
            'password' => 'new-Password1',
            'password_confirmation' => 'new-Password1',
        ]);

        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages(
            $this->equalTo(['You saved the account information.']),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertSameGreeting(self::EXPECTED_GREETING, $this->transportBuilderMock->getSentMessage());
    }

    /**
     * Wraps Customer Edit POST request
     *
     * @param array $customData
     */
    private function sendAccountEditRequest(array $customData): void
    {
        $basicData = [
            'form_key' => $this->formKey->getFormKey(),
            'firstname' => self::FIXTURE_CUSTOMER_FIRSTNAME,
            'lastname' => self::FIXTURE_CUSTOMER_LASTNAME,
            'current_password' => self::FIXTURE_CUSTOMER_PASSWORD
        ];

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST)
            ->setPostValue(array_merge($basicData, $customData));

        $this->dispatch('customer/account/editPost');
    }

    /**
     * Verifies if `<p class="greeting"/>` text contents equals the expected one.
     *
     * @param string $expectedGreeting
     * @param EmailMessage $message
     */
    private function assertSameGreeting(string $expectedGreeting, EmailMessage $message)
    {
        $messageContent = $this->getMessageRawContent($message);
        $emailDom = new \DOMDocument();
        $emailDom->loadHTML($messageContent);

        $emailXpath = new \DOMXPath($emailDom);
        $greeting = $emailXpath->query('//p[@class="greeting"]');

        $this->assertSame(1, $greeting->length);
        $this->assertSame($expectedGreeting, $greeting->item(0)->textContent);
    }

    /**
     * Returns raw content of provided message
     *
     * @param EmailMessage $message
     * @return string
     */
    private function getMessageRawContent(EmailMessage $message): string
    {
        return  quoted_printable_decode($message->getBody()->bodyToString());
    }

    /**
     * Performs Customer log in
     *
     * @param int $customerId
     */
    private function loginByCustomerId(int $customerId): void
    {
        $this->session->loginById($customerId);
    }
}
