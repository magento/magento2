<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Newsletter\Model\Plugin;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
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

    /** @var DataFixtureStorage */
    private $fixtures;

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
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
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

        /** @var Subscriber $subscriber */
        $subscriber = $objectManager->create(Subscriber::class);
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

        /** @var Subscriber $subscriber */
        $subscriber = $objectManager->create(Subscriber::class);
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

        /** @var Subscriber $subscriber */
        $subscriber = $objectManager->create(Subscriber::class);
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
        /** @var Subscriber $subscriber */
        $subscriber = $objectManager->create(Subscriber::class);
        $subscriber->loadByEmail('customer@example.com');
        $this->assertTrue($subscriber->isSubscribed());
        $this->customerRepository->delete($customer);
        $this->verifySubscriptionNotExist('customer@example.com');
    }

    /**
     * Verify a subscription doesn't exist for a given email address
     *
     * @param string $email
     * @return Subscriber
     */
    private function verifySubscriptionNotExist($email)
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var Subscriber $subscriber */
        $subscriber = $objectManager->create(Subscriber::class);
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

        $subscriber = $objectManager->create(Subscriber::class);
        /** @var Subscriber $subscriber */
        $subscriber->setStoreId($currentStore)
            ->setCustomerId(0)
            ->setSubscriberEmail('customer@example.com')
            ->setSubscriberStatus(Subscriber::STATUS_SUBSCRIBED)
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
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
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

        /** @var Subscriber $subscriber */
        $subscriber = $objectManager->create(Subscriber::class);
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

    #[
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'w2'),
        DataFixture(GroupFixture::class, ['website_id' => '$w2.id$'], 'g2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$g2.id$'], as: 's2'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customerDefaultWebsite'),
        DataFixture(
            Customer::class,
            ['email' => 'customer@example.com', 'website_id' => '$w2.id$', 'store_id' => '$s2.id$'],
            as: 'customerCustomWebsite'
        )
    ]
    public function testMultipleWebsiteCustomerHasUniqueSubscriptionsPerWebsite(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $customerDefaultWebsite = $this->fixtures->get('customerDefaultWebsite');
        $customerCustomWebsite = $this->fixtures->get('customerCustomWebsite');
        // setting to customer for convenient and uniform retrieving later below
        $customerDefaultWebsite->setSubscriberStatus(Subscriber::STATUS_SUBSCRIBED);
        $customerCustomWebsite->setSubscriberStatus(Subscriber::STATUS_UNSUBSCRIBED);

        $this->assertEquals(
            $customerDefaultWebsite->getEmail(),
            $customerCustomWebsite->getEmail(),
            'Precondition emails for customers on both websites must be the same'
        );

        foreach ([$customerDefaultWebsite, $customerCustomWebsite] as $customer) {
            $subscriber = $objectManager->create(Subscriber::class);
            $subscriber->setEmail($customer->getEmail());
            $subscriber->setCustomerId($customer->getId());
            $subscriber->setStoreId($customer->getStoreId());
            $subscriber->setSubscriberStatus($customer->getSubscriberStatus());
            $subscriber->save();
        }

        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchBuilder->addFilter('email', $customerDefaultWebsite->getEmail())->create();
        $items = $this->customerRepository->getList($searchCriteria)->getItems();

        // Assertions
        $this->assertEquals(2, count($items), 'Customers from both websites should be retrieved');

        $expectedCustomerSubscriptionMap = [
            $customerDefaultWebsite->getId() => true,
            $customerCustomWebsite->getId() => false
        ];

        $actualCustomerSubscriptionMap = [];
        foreach ($items as $item) {
            $actualCustomerSubscriptionMap[$item->getId()] = $item->getExtensionAttributes()->getIsSubscribed();
        }

        $this->assertEquals(
            $expectedCustomerSubscriptionMap,
            $actualCustomerSubscriptionMap,
            'Customer with same email on each website should have has respective subscription'
        );
    }
}
