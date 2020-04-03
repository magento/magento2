<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Test for customer email notification model.
 *
 * @magentoDbIsolation enabled
 */
class EmailNotificationTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var EmailNotification */
    private $emailNotification;

    /** @var TransportBuilderMock */
    private $transportBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->emailNotification = $this->objectManager->get(EmailNotification::class);
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/set_custom_customer_reset_password_template.php
     *
     * @return void
     */
    public function testResetPasswordCustomTemplate(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->emailNotification->credentialsChanged($customer, $customer->getEmail(), true);
        $expectedSender = ['name' => 'CustomerSupport', 'email' => 'support@example.com'];
        $this->assertMessage($expectedSender);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/set_custom_customer_forgot_email_template.php
     * @magentoConfigFixture current_store customer/password/forgot_email_identity custom1
     *
     * @return void
     */
    public function testForgotPasswordCustomTemplate(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->emailNotification->passwordResetConfirmation($customer);
        $expectedSender = ['name' => 'Custom 1', 'email' => 'custom1@example.com'];
        $this->assertMessage($expectedSender);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/set_custom_customer_remind_email_template.php
     * @magentoConfigFixture current_store customer/password/forgot_email_identity custom2
     *
     * @return void
     */
    public function testRemindPasswordCustomTemplate(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->emailNotification->passwordReminder($customer);
        $expectedSender = ['name' => 'Custom 2', 'email' => 'custom2@example.com'];
        $this->assertMessage($expectedSender);
    }

    /**
     * Assert message.
     *
     * @param array $expectedSender
     * @return void
     */
    private function assertMessage(array $expectedSender): void
    {
        $message = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($message);
        $this->assertMessageSender($message, $expectedSender);
        $this->assertContains(
            'Text specially for check in test.',
            $message->getBody()->getParts()[0]->getRawContent(),
            'Expected text wasn\'t found in message.'
        );
    }

    /**
     * Assert message sender.
     *
     * @param MessageInterface $message
     * @param array $expectedSender
     * @return void
     */
    private function assertMessageSender(MessageInterface $message, array $expectedSender): void
    {
        $messageFrom = $message->getFrom();
        $this->assertNotNull($messageFrom);
        $messageFrom = current($messageFrom);
        $this->assertEquals($expectedSender['name'], $messageFrom->getName());
        $this->assertEquals($expectedSender['email'], $messageFrom->getEmail());
    }
}
