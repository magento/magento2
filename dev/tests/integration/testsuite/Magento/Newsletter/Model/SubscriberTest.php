<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

use Laminas\Mail\Headers;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Class checks subscription behavior.
 *
 * @see \Magento\Newsletter\Model\Subscriber
 */
class SubscriberTest extends TestCase
{
    /** @var ObjectManagerInterface  */
    private $objectManager;

    /** @var SubscriberFactory */
    private $subscriberFactory;

    /** @var TransportBuilderMock  */
    private $transportBuilder;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->subscriberFactory = $this->objectManager->get(SubscriberFactory::class);
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * Tests that confirmation code does NOT change after creating Customer account with subscription.
     *
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @return void
     */
    public function testConfirmationCodeDoesNotChangeWhenCustomerEmailHasSubscription(): void
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create()
            ->loadByEmail('customer_confirm@example.com');
        $confirmCode = $subscriber->getCode();

        /** @var CustomerInterfaceFactory $customerFactory */
        $customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $customerDataObject = $customerFactory->create()
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setEmail('customer_confirm@example.com');

        /** @var AccountManagementInterface $accountManagement */
        $accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $createdCustomer = $this->customerRepository->save(
            $customerDataObject,
            $accountManagement->getPasswordHash('password')
        );

        $subscriber->loadByCustomerId((int)$createdCustomer->getId());
        $this->assertEquals($confirmCode, $subscriber->getCode());
    }

    /**
     * @magentoConfigFixture current_store newsletter/subscription/confirm 1
     *
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     *
     * @return void
     */
    public function testEmailConfirmation(): void
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->subscribe('customer_confirm@example.com');
        // confirmationCode 'ysayquyajua23iq29gxwu2eax2qb6gvy' is taken from fixture
        $this->assertStringContainsString(
            '/newsletter/subscriber/confirm/id/' . $subscriber->getSubscriberId()
            . '/code/ysayquyajua23iq29gxwu2eax2qb6gvy',
            $this->transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
        $this->assertEquals(Subscriber::STATUS_NOT_ACTIVE, $subscriber->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     *
     * @return void
     */
    public function testLoadByCustomerId(): void
    {
        $subscriber = $this->subscriberFactory->create();
        $this->assertSame($subscriber, $subscriber->loadByCustomerId(1));
        $this->assertEquals('customer@example.com', $subscriber->getSubscriberEmail());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     *
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testUnsubscribeSubscribe(): void
    {
        $subscriber = $this->subscriberFactory->create();
        $this->assertSame($subscriber, $subscriber->loadByCustomerId(1));
        $this->assertEquals($subscriber, $subscriber->unsubscribe());
        $this->assertStringContainsString(
            'You have been unsubscribed from the newsletter.',
            $this->getFilteredRawMessage($this->transportBuilder)
        );
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $subscriber->getSubscriberStatus());
        // Subscribe and verify
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $subscriber->subscribe('customer@example.com'));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $subscriber->getSubscriberStatus());
        $this->assertStringContainsString(
            'You have been successfully subscribed to our newsletter.',
            $this->getFilteredRawMessage($this->transportBuilder)
        );
    }

    /**
     * @param TransportBuilderMock $transportBuilderMock
     * @return string
     */
    private function getFilteredRawMessage(TransportBuilderMock $transportBuilderMock): string
    {
        return str_replace('=' . Headers::EOL, '', $transportBuilderMock->getSentMessage()->getRawMessage());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     *
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testUnsubscribeSubscribeByCustomerId(): void
    {
        $subscriber = $this->subscriberFactory->create();
        // Unsubscribe and verify
        $this->assertSame($subscriber, $subscriber->unsubscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $subscriber->getSubscriberStatus());
        $this->assertStringContainsString(
            'You have been unsubscribed from the newsletter.',
            $this->getFilteredRawMessage($this->transportBuilder)
        );
        // Subscribe and verify
        $this->assertSame($subscriber, $subscriber->subscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $subscriber->getSubscriberStatus());
        $this->assertStringContainsString(
            'You have been successfully subscribed to our newsletter.',
            $this->getFilteredRawMessage($this->transportBuilder)
        );
    }

    /**
     * @magentoConfigFixture current_store newsletter/subscription/confirm 1
     *
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     *
     * @return void
     */
    public function testConfirm(): void
    {
        $subscriber = $this->subscriberFactory->create();
        $customerEmail = 'customer_confirm@example.com';
        $subscriber->subscribe($customerEmail);
        $subscriber->loadByEmail($customerEmail);
        $subscriber->confirm($subscriber->getSubscriberConfirmCode());
        $this->assertStringContainsString(
            'You have been successfully subscribed to our newsletter.',
            $this->getFilteredRawMessage($this->transportBuilder)
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     * @magentoDataFixture Magento/Newsletter/_files/newsletter_unconfirmed_customer.php
     *
     * @return void
     */
    public function testSubscribeUnconfirmedCustomerWithSubscription(): void
    {
        $customer = $this->customerRepository->get('unconfirmedcustomer@example.com');
        $subscriber = $this->subscriberFactory->create();
        $subscriber->subscribeCustomerById($customer->getId());
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $subscriber->getStatus());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     * @magentoDataFixture Magento/Customer/_files/unconfirmed_customer.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSubscribeUnconfirmedCustomerWithoutSubscription(): void
    {
        $customer = $this->customerRepository->get('unconfirmedcustomer@example.com');
        $subscriber = $this->subscriberFactory->create();
        $subscriber->subscribeCustomerById($customer->getId());
        $this->assertEquals(Subscriber::STATUS_UNCONFIRMED, $subscriber->getStatus());
    }
}
