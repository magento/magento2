<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class checks password forgot scenarios
 *
 * @see \Magento\Customer\Controller\Account\ForgotPasswordPost
 * @magentoDbIsolation enabled
 */
class ForgotPasswordPostTest extends AbstractController
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var TransportBuilderMock */
    private $transportBuilderMock;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @return void
     */
    public function testWithoutEmail(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['email' => '']);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('Please enter your email.')]),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
    }

    /**
     * Test that forgot password email message displays special characters correctly.
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @codingStandardsIgnoreEnd
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store general/store_information/name Test special' characters
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testForgotPasswordEmailMessageWithSpecialCharacters(): void
    {
        $email = 'customer@example.com';
        $this->getRequest()->setPostValue(['email' => $email]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSuccessSessionMessage($email);
        $subject = $this->transportBuilderMock->getSentMessage()->getSubject();
        $this->assertStringContainsString('Test special\' characters', $subject);
    }

    /**
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @codingStandardsIgnoreEnd
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testForgotPasswordPostAction(): void
    {
        $email = 'customer@example.com';
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['email' => $email]);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSuccessSessionMessage($email);
    }

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @return void
     */
    public function testForgotPasswordPostWithBadEmailAction(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['email' => 'bad@email']);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['The email address is incorrect. Verify the email address and try again.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Assert success session message
     *
     * @param string $email
     * @return void
     */
    private function assertSuccessSessionMessage(string $email): void
    {
        $message = __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $email
        );
        $this->assertSessionMessages($this->equalTo([$message]), MessageInterface::TYPE_SUCCESS);
    }

    /**
     * @magentoConfigFixture current_store customer/password/password_reset_protection_type 0
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDisableLimitOfResetRequests(): void
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->customerRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $customer) {
            $customAttributes = $customer->getCustomAttributes();
            $numberOfRequests = $customAttributes['max_number_password_reset_requests'] ?? null;

            $this->assertNull($numberOfRequests);
        }

        $email = 'customer@example.com';
        $this->getRequest()->setPostValue(['email' => $email]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        for ($i = 0; $i < 10; $i++) {
            $this->dispatch('customer/account/forgotPasswordPost');
            $this->assertRedirect($this->stringContains('customer/account/'));

            $sendMessage = $this->transportBuilderMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();

            $this->assertStringContainsString(
                'There was recently a request to change the password for your account',
                $sendMessage
            );

            $this->assertSessionMessages(
                $this->equalTo([]),
                MessageInterface::TYPE_ERROR
            );
        }
    }
}
