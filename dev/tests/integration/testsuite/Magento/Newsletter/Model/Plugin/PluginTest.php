<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Newsletter\Model\Plugin;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:disable Magento2.Security.Superglobal
 * @magentoAppIsolation enabled
 */
class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Customer Account Service
     *
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var TransportBuilderMock
     */
    protected $transportBuilderMock;

    protected function setUp(): void
    {
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->transportBuilderMock = Bootstrap::getObjectManager()->get(
            TransportBuilderMock::class
        );
    }

    protected function tearDown(): void
    {
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Model\CustomerRegistry::class);
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerCreated()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer_two@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals(0, (int)$subscriber->getCustomerId());

        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = $objectManager->get(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class);
        $customerDataObject = $customerFactory->create()
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setEmail('customer_two@example.com');
        $createdCustomer = $this->customerRepository->save(
            $customerDataObject,
            $this->accountManagement->getPasswordHash('password')
        );

        $subscriber->loadByEmail('customer_two@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals((int)$createdCustomer->getId(), (int)$subscriber->getCustomerId());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testCustomerCreatedNotSubscribed()
    {
        $this->verifySubscriptionNotExist('customer@example.com');

        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = $objectManager->get(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class);
        $customerDataObject = $customerFactory->create()
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setEmail('customer@example.com');
        $this->accountManagement->createAccount($customerDataObject);

        $this->verifySubscriptionNotExist('customer@example.com');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerUpdatedEmail()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals(1, (int)$subscriber->getCustomerId());

        $customer = $this->customerRepository->getById(1);
        $customer->setEmail('new@example.com');
        $this->customerRepository->save($customer);

        $subscriber->loadByEmail('new@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->assertEquals(1, (int)$subscriber->getCustomerId());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerDeletedByIdAdminArea()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());

        $this->customerRepository->deleteById(1);

        $this->verifySubscriptionNotExist('customer@example.com');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerDeletedAdminArea()
    {
        $customer = $this->customerRepository->getById(1);
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->customerRepository->delete($customer);
        $this->verifySubscriptionNotExist('customer@example.com');
    }

    /**
     * Verify a subscription doesn't exist for a given email address
     *
     * @param string $email
     * @return \Magento\Newsletter\Model\Subscriber
     */
    private function verifySubscriptionNotExist($email)
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail($email);
        $this->assertFalse($subscriber->isSubscribed());
        $this->assertEquals(0, (int)$subscriber->getId());
        return $subscriber;
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testCustomerWithZeroStoreIdIsSubscribed()
    {
        $objectManager = Bootstrap::getObjectManager();

        $currentStore = $objectManager->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId();

        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber->setStoreId($currentStore)
            ->setCustomerId(0)
            ->setSubscriberEmail('customer@example.com')
            ->setSubscriberStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED)
            ->save();

        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = $objectManager->get(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class);
        $customerDataObject = $customerFactory->create()
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setStoreId(0)
            ->setEmail('customer@example.com');
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->accountManagement->createAccount($customerDataObject);

        $this->customerRepository->save($customer);

        $subscriber->loadByEmail('customer@example.com');

        $this->assertEquals($customer->getId(), (int)$subscriber->getCustomerId());
        $this->assertEquals($currentStore, (int)$subscriber->getStoreId());
    }

    /**
     * Test get list customer, which have more then 2 subscribes in newsletter_subscriber.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testCustomerWithTwoNewsLetterSubscriptions()
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $searchCriteria = $searchBuilder->addFilter('entity_id', 1)->create();
        $items = $this->customerRepository->getList($searchCriteria)->getItems();
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $items[0];
        $extensionAttributes = $customer->getExtensionAttributes();
        $this->assertTrue($extensionAttributes->getIsSubscribed());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store newsletter/general/active 1
     * @magentoDataFixture Magento/Customer/_files/customer_welcome_email_template.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateAccountWithNewsLetterSubscription(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = $objectManager->get(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class);
        $customerDataObject = $customerFactory->create()
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('customer@example.com');
        $extensionAttributes = $customerDataObject->getExtensionAttributes();
        $extensionAttributes->setIsSubscribed(true);
        $customerDataObject->setExtensionAttributes($extensionAttributes);
        $this->accountManagement->createAccount($customerDataObject, '123123qW');
        $message = $this->transportBuilderMock->getSentMessage();

        $this->assertNotNull($message);
        $this->assertEquals('Welcome to Main Website Store', $message->getSubject());
        $mailMessage = quoted_printable_decode($message->getBody()->bodyToString());
        $this->assertStringContainsString(
            'John',
            $mailMessage
        );
        $this->assertStringContainsString(
            'customer@example.com',
            $mailMessage
        );

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $objectManager->create(\Magento\Newsletter\Model\Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());

        $this->transportBuilderMock->setTemplateIdentifier(
            'newsletter_subscription_confirm_email_template'
        )->setTemplateVars([
            'subscriber_data' => [
                'confirmation_link' => $subscriber->getConfirmationLink(),
            ],
        ])->setTemplateOptions([
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ])
        ->addTo('customer@example.com')
        ->getTransport();

        $message = $this->transportBuilderMock->getSentMessage();
        $mailMessage = quoted_printable_decode($message->getBody()->bodyToString());
        $this->assertNotNull($message);
        $this->assertStringContainsString(
            $subscriber->getConfirmationLink(),
            $mailMessage
        );
        $this->assertEquals('Newsletter subscription confirmation', $message->getSubject());
    }
}
