<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Test for customer email notification model.
 *
 * @see \Magento\Customer\Model\EmailNotification
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotificationTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Manager */
    private $moduleManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var EmailNotificationInterface */
    private $emailNotification;

    /** @var TransportBuilderMock */
    private $transportBuilder;

    /** @var TemplateResource */
    private $templateResource;

    /** @var TemplateFactory */
    private $templateFactory;

    /** @var MutableScopeConfigInterface */
    private $mutableScopeConfig;

    /** @var CollectionFactory */
    private $templateCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $this->objectManager->get(Manager::class);
        //This check is needed because Magento_Customer independent of Magento_Email
        if (!$this->moduleManager->isEnabled('Magento_Email')) {
            $this->markTestSkipped('Magento_Email module disabled.');
        }
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->emailNotification = $this->objectManager->get(EmailNotificationInterface::class);
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
        $this->templateResource = $this->objectManager->get(TemplateResource::class);
        $this->templateFactory = $this->objectManager->get(TemplateFactory::class);
        $this->mutableScopeConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
        $this->templateCollectionFactory = $this->objectManager->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->moduleManager->isEnabled('Magento_Email')) {
            $this->mutableScopeConfig->clean();
            $collection = $this->templateCollectionFactory->create();
            $template = $collection->addFieldToFilter('template_code', 'customer_password_email_template')
                ->getFirstItem();
            if ($template->getId()) {
                $this->templateResource->delete($template);
            }
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testResetPasswordCustomTemplate(): void
    {
        $this->setEmailTemplateConfig(EmailNotification::XML_PATH_RESET_PASSWORD_TEMPLATE);
        $customer = $this->customerRepository->get('customer@example.com');
        $this->emailNotification->credentialsChanged($customer, $customer->getEmail(), true);
        $expectedSender = ['name' => 'CustomerSupport', 'email' => 'support@example.com'];
        $this->assertMessage($expectedSender);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/password/forgot_email_identity custom1
     *
     * @return void
     */
    public function testForgotPasswordCustomTemplate(): void
    {
        $this->setEmailTemplateConfig(EmailNotification::XML_PATH_FORGOT_EMAIL_TEMPLATE);
        $customer = $this->customerRepository->get('customer@example.com');
        $this->emailNotification->passwordResetConfirmation($customer);
        $expectedSender = ['name' => 'Custom 1', 'email' => 'custom1@example.com'];
        $this->assertMessage($expectedSender);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/password/forgot_email_identity custom2
     *
     * @return void
     */
    public function testRemindPasswordCustomTemplate(): void
    {
        $this->setEmailTemplateConfig(EmailNotification::XML_PATH_REMIND_EMAIL_TEMPLATE);
        $customer = $this->customerRepository->get('customer@example.com');
        $this->emailNotification->passwordReminder($customer);
        $expectedSender = ['name' => 'Custom 2', 'email' => 'custom2@example.com'];
        $this->assertMessage($expectedSender);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testChangeEmailCustomTemplate(): void
    {
        $this->setEmailTemplateConfig(EmailNotification::XML_PATH_CHANGE_EMAIL_TEMPLATE);
        $customer = $this->customerRepository->get('customer@example.com');
        $customer->setEmail('customer_update@example.com');
        $this->emailNotification->credentialsChanged($customer, 'customer@example.com');
        $expectedSender = ['name' => 'CustomerSupport', 'email' => 'support@example.com'];
        $this->assertMessage($expectedSender);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testChangeEmailAndPasswordCustomTemplate(): void
    {
        $this->setEmailTemplateConfig(EmailNotification::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE);
        $customer = $this->customerRepository->get('customer@example.com');
        $customer->setEmail('customer_update@example.com');
        $this->emailNotification->credentialsChanged($customer, 'customer@example.com', true);
        $expectedSender = ['name' => 'CustomerSupport', 'email' => 'support@example.com'];
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
        $this->assertStringContainsString(
            'Text specially for check in test.',
            quoted_printable_decode($message->getBody()->bodyToString()),
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

    /**
     * Set email template config.
     *
     * @param string $configPath
     * @return void
     */
    private function setEmailTemplateConfig(string $configPath): void
    {
        $template = $this->templateFactory->create();
        $template->setTemplateCode('customer_password_email_template')
            ->setTemplateText(file_get_contents(__DIR__ . '/../_files/customer_password_email_template.html'))
            ->setTemplateType(Template::TYPE_HTML);
        $this->templateResource->save($template);
        $this->mutableScopeConfig->setValue($configPath, $template->getId(), ScopeInterface::SCOPE_STORE, 'default');
    }
}
