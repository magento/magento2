<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Model\CustomerSubscriberCache;
use Magento\Newsletter\Model\Plugin\CustomerPlugin;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class to test Newsletter Plugin for customer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin
 */
class CustomerPluginTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var SubscriberFactory|MockObject
     */
    private $subscriberFactory;

    /**
     * @var ExtensionAttributesFactory|MockObject
     */
    private $extensionFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var SubscriptionManagerInterface|MockObject
     */
    private $subscriptionManager;

    /**
     * @var Share|MockObject
     */
    private $shareConfig;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var CustomerSubscriberCache|MockObject
     */
    private $customerSubscriberCache;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->subscriberFactory = $this->createMock(SubscriberFactory::class);
        $this->extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->subscriptionManager = $this->createMock(SubscriptionManagerInterface::class);
        $this->shareConfig = $this->createMock(Share::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->customerSubscriberCache = $this->createMock(CustomerSubscriberCache::class);
        $this->objectManager = new ObjectManager($this);
        $this->plugin = $this->objectManager->getObject(
            CustomerPlugin::class,
            [
                'subscriberFactory' => $this->subscriberFactory,
                'extensionFactory' => $this->extensionFactory,
                'collectionFactory' => $this->collectionFactory,
                'subscriptionManager' => $this->subscriptionManager,
                'shareConfig' => $this->shareConfig,
                'storeManager' => $this->storeManager,
                'logger' => $this->logger,
                'customerSubscriberCache' => $this->customerSubscriberCache,
            ]
        );
    }

    /**
     * Test to update customer subscription after save customer
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterSave()
     * @param int|null $originalStatus
     * @param bool|null $newValue
     * @param bool|null $expectedSubscribe
     * @return void
     */
    #[DataProvider('afterSaveDataProvider')]
    public function testAfterSave(?int $originalStatus, ?bool $newValue, ?bool $expectedSubscribe): void
    {
        $storeId = 2;
        $websiteId = 1;
        $customerId = 3;
        $customerEmail = 'email@example.com';

        $this->customerSubscriberCache->method('getCustomerSubscriber')->with($customerId)->willReturn(null);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn($storeId);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->willReturn($store);

        $subscriber = $this->createMock(Subscriber::class);
        $subscriber->method('getStatus')->willReturn($originalStatus);
        $subscriber->method('getEmail')->willReturn($customerEmail);
        $subscriber->method('isSubscribed')->willReturn($originalStatus === Subscriber::STATUS_SUBSCRIBED);
        $subscriber->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerId, $websiteId)
            ->willReturnSelf();
        if ($originalStatus !== null && $originalStatus === Subscriber::STATUS_UNCONFIRMED) {
            $subscriber->method('getId')->willReturn(1);
        } else {
            $subscriber->expects($this->once())
                ->method('loadBySubscriberEmail')
                ->with($customerEmail, $websiteId)
                ->willReturnSelf();
        }
        $this->subscriberFactory->method('create')->willReturn($subscriber);

        $customerExtension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['getIsSubscribed', 'setIsSubscribed']
        );
        $customerExtension->method('getIsSubscribed')->willReturn($newValue);
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getExtensionAttributes')->willReturn($customerExtension);

        $resultIsSubscribed = $newValue ?? $originalStatus === Subscriber::STATUS_SUBSCRIBED;
        if ($expectedSubscribe !== null) {
            $resultSubscriber = $this->createMock(Subscriber::class);
            $resultSubscriber->method('isSubscribed')->willReturn($resultIsSubscribed);
            $this->subscriptionManager->expects($this->once())
                ->method($expectedSubscribe ? 'subscribeCustomer' : 'unsubscribeCustomer')
                ->with($customerId, $storeId)
                ->willReturn($resultSubscriber);
            $this->customerSubscriberCache->expects($this->exactly(2))
                ->method('setCustomerSubscriber')
                ->with($customerId, $this->isInstanceOf(Subscriber::class));
        } else {
            $this->subscriptionManager->expects($this->never())->method('subscribeCustomer');
            $this->subscriptionManager->expects($this->never())->method('unsubscribeCustomer');
            $this->customerSubscriberCache->expects($this->once())
                ->method('setCustomerSubscriber')
                ->with($customerId, $subscriber);
        }

        $resultExtension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['getIsSubscribed', 'setIsSubscribed']
        );
        $resultExtension->expects($this->once())->method('setIsSubscribed')->with($resultIsSubscribed);
        /** @var CustomerInterface|MockObject $result */
        $result = $this->createMock(CustomerInterface::class);
        $result->method('getId')->willReturn($customerId);
        $result->method('getEmail')->willReturn($customerEmail);
        $result->method('getExtensionAttributes')->willReturn($resultExtension);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertSame($result, $this->plugin->afterSave($subject, $result, $customer));
    }

    /**
     * Data provider for testAfterSave()
     *
     * @return array<string, array<int|null, bool|null, bool|null>>
     */
    public static function afterSaveDataProvider(): array
    {
        return [
            'missing_previous_and_new_status' => [null, null, null],
            'missing_previous_status_and_subscribe' => [null, true, true],
            'new_unsubscribed_value_and_missing_previous_status' => [null, false, null],
            'previous_subscribed_status_without_new_value' => [Subscriber::STATUS_SUBSCRIBED, null, null],
            'same_subscribed_previous_and_new_status' => [Subscriber::STATUS_SUBSCRIBED, true, null],
            'unsubscribe_previously_subscribed_customer' => [Subscriber::STATUS_SUBSCRIBED, false, false],
            'previously_unsubscribed_status_without_new_value' => [Subscriber::STATUS_UNSUBSCRIBED, null, null],
            'subscribe_previously_unsubscribed_customer' => [Subscriber::STATUS_UNSUBSCRIBED, true, true],
            'same_unsubscribed_previous_and_new_status' => [Subscriber::STATUS_UNSUBSCRIBED, false, null],
            'previous_unconfirmed_status_without_new_value' => [Subscriber::STATUS_UNCONFIRMED, null, true],
            'subscribe_previously_unconfirmed_status' => [Subscriber::STATUS_UNCONFIRMED, true, true],
            'unsubscribe_previously_unconfirmed_status' => [Subscriber::STATUS_UNCONFIRMED, false, true],
        ];
    }

    /**
     * Test to delete subscriptions after delete customer
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterDelete()
     * @return void
     */
    public function testAfterDelete(): void
    {
        $customerEmail = 'email@example.com';
        $websiteId = 1;
        $storeIds = [1, 2];

        $subscriber = $this->createMock(Subscriber::class);
        $subscriber->expects($this->once())->method('delete');
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('subscriber_email', $customerEmail)
            ->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$subscriber]));
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->shareConfig->method('isWebsiteScope')->willReturn(false);
        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')->willReturn($storeIds);
        $this->storeManager->method('getWebsite')->with($websiteId)->willReturn($website);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn($customerEmail);

        $this->assertTrue($this->plugin->afterDelete($subject, true, $customer));
    }

    /**
     * Test to delete subscriptions after delete customer by id
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::aroundDeleteById()
     * @return void
     */
    public function testAroundDeleteById(): void
    {
        $customerId = 1;
        $customerEmail = 'test@test.com';
        $websiteId = 1;
        $storeIds = [1, 2];
        $deleteCustomerById = function () {
            return true;
        };
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())->method('getEmail')->willReturn($customerEmail);
        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $subject->expects($this->once())->method('getById')->with($customerId)->willReturn($customer);

        $subscriber = $this->createMock(Subscriber::class);
        $subscriber->expects($this->once())->method('delete');
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('subscriber_email', $customerEmail)
            ->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$subscriber]));
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->shareConfig->method('isWebsiteScope')->willReturn(false);
        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')->willReturn($storeIds);
        $this->storeManager->method('getWebsite')->with($websiteId)->willReturn($website);

        $this->assertTrue($this->plugin->aroundDeleteById($subject, $deleteCustomerById, $customerId));
    }

    /**
     * Test to load extension attribute after get by id when not set
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterGetById()
     * @return void
     */
    public function testAfterGetByIdCreatesExtensionAttributes(): void
    {
        $storeId = 2;
        $websiteId = 1;
        $customerId = 3;
        $customerEmail = 'email@example.com';
        $subscribed = true;

        $this->customerSubscriberCache->method('getCustomerSubscriber')->with($customerId)->willReturn(null);
        $this->customerSubscriberCache->expects($this->once())
            ->method('setCustomerSubscriber')
            ->with($customerId, $this->isInstanceOf(Subscriber::class));

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn($storeId);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->willReturn($store);

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getId')->willReturn($customerId);
        $customer->method('getEmail')->willReturn($customerEmail);
        $customer->method('getExtensionAttributes')->willReturn(null);

        $subscriber = $this->createMock(Subscriber::class);
        $subscriber->method('getEmail')->willReturn($customerEmail);
        $subscriber->method('isSubscribed')->willReturn($subscribed);
        $subscriber->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerId, $websiteId)
            ->willReturnSelf();
        $subscriber->expects($this->once())
            ->method('loadBySubscriberEmail')
            ->with($customerEmail, $websiteId)
            ->willReturnSelf();
        $this->subscriberFactory->method('create')->willReturn($subscriber);

        $customerExtension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['getIsSubscribed', 'setIsSubscribed']
        );
        $customerExtension->expects($this->once())->method('setIsSubscribed')->with($subscribed);
        $this->extensionFactory->expects($this->once())->method('create')->willReturn($customerExtension);
        $customer->expects($this->once())->method('setExtensionAttributes')->with($customerExtension);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertSame(
            $customer,
            $this->plugin->afterGetById($subject, $customer)
        );
    }

    /**
     * Test afterGetById does not overwrite when extension attribute is_subscribed already set
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterGetById()
     * @return void
     */
    public function testAfterGetByIdDoesNotOverwriteWhenExtensionAttributeAlreadySet(): void
    {
        $customerExtension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['getIsSubscribed', 'setIsSubscribed']
        );
        $customerExtension->method('getIsSubscribed')->willReturn(true);
        $customerExtension->expects($this->never())->method('setIsSubscribed');

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getExtensionAttributes')->willReturn($customerExtension);

        $this->subscriberFactory->expects($this->never())->method('create');
        $this->extensionFactory->expects($this->never())->method('create');

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertSame($customer, $this->plugin->afterGetById($subject, $customer));
    }

    /**
     * Test afterGetList adds subscription status to each customer in search results
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterGetList()
     * @return void
     */
    public function testAfterGetListAddsSubscriptionStatusToCustomers(): void
    {
        $customer1Id = 1;
        $customer2Id = 2;
        $email1 = 'customer1@example.com';
        $email2 = 'customer2@example.com';

        /** @var CustomerInterface|MockObject $customer1 */
        $customer1 = $this->createMock(CustomerInterface::class);
        $customer1->method('getId')->willReturn($customer1Id);
        $customer1->method('getEmail')->willReturn($email1);

        $extension1 = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['setIsSubscribed']
        );
        $extension1->expects($this->once())->method('setIsSubscribed')->with(true);
        $customer1->method('getExtensionAttributes')->willReturn($extension1);

        /** @var CustomerInterface|MockObject $customer2 */
        $customer2 = $this->createMock(CustomerInterface::class);
        $customer2->method('getId')->willReturn($customer2Id);
        $customer2->method('getEmail')->willReturn($email2);

        $extension2 = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['setIsSubscribed']
        );
        $extension2->expects($this->once())->method('setIsSubscribed')->with(false);
        $customer2->method('getExtensionAttributes')->willReturn($extension2);

        $subscriber1 = $this->createMock(Subscriber::class);
        $subscriber1->method('getStatus')->willReturn(Subscriber::STATUS_SUBSCRIBED);
        $subscriber2 = $this->createMock(Subscriber::class);
        $subscriber2->method('getStatus')->willReturn(Subscriber::STATUS_UNSUBSCRIBED);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('subscriber_email', ['in' => [$email1, $email2]])
            ->willReturnSelf();
        $collection->method('getItemByColumnValue')
            ->willReturnMap([
                ['customer_id', $customer1Id, $subscriber1],
                ['customer_id', $customer2Id, $subscriber2],
            ]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $searchResults = $this->createMock(SearchResults::class);
        $searchResults->method('getItems')->willReturn([$customer1, $customer2]);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertSame(
            $searchResults,
            $this->plugin->afterGetList($subject, $searchResults)
        );
    }

    /**
     * Test afterGetList uses customer subscriber row when same email has guest and customer rows.
     *
     * Scenario: a guest is subscribed in one store, and a customer with the same email is
     * unsubscribed in another store. For customer API search results, is_subscribed must be resolved
     * from the customer row, not from the guest row.
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterGetList()
     * @return void
     */
    public function testAfterGetListIgnoresSubscribedGuestRowWhenCustomerRowIsUnsubscribed(): void
    {
        $customerId = 2;
        $email = 'customer@example.com';

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getId')->willReturn($customerId);
        $customer->method('getEmail')->willReturn($email);
        $customer->method('getStoreId')->willReturn(2);

        $extension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['setIsSubscribed']
        );
        $extension->expects($this->once())->method('setIsSubscribed')->with(false);
        $customer->method('getExtensionAttributes')->willReturn($extension);

        $guestSubscriber = $this->createMock(Subscriber::class);
        $guestSubscriber->method('getStatus')->willReturn(Subscriber::STATUS_SUBSCRIBED);

        $customerSubscriber = $this->createMock(Subscriber::class);
        $customerSubscriber->method('getStatus')->willReturn(Subscriber::STATUS_UNSUBSCRIBED);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('subscriber_email', ['in' => [$email]])
            ->willReturnSelf();
        $collection->expects($this->never())->method('getFirstItem');
        $collection->expects($this->once())
            ->method('getItemByColumnValue')
            ->willReturnCallback(function (
                string $field,
                int|string $value
            ) use (
                $customerId,
                $guestSubscriber,
                $customerSubscriber
            ) {
                $this->assertSame('customer_id', $field);
                $this->assertSame($customerId, $value);
                $this->assertSame(Subscriber::STATUS_SUBSCRIBED, (int)$guestSubscriber->getStatus());

                return $customerSubscriber;
            });
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $searchResults = $this->createMock(SearchResults::class);
        $searchResults->method('getItems')->willReturn([$customer]);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertSame(
            $searchResults,
            $this->plugin->afterGetList($subject, $searchResults)
        );
    }

    /**
     * Test afterGetList ignores guest row status when customer row is subscribed.
     *
     * This is the inverse guest/customer status combination to ensure we do not accidentally pick
     * guest data when rows share the same email across stores.
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterGetList()
     * @return void
     */
    public function testAfterGetListIgnoresUnsubscribedGuestRowWhenCustomerRowIsSubscribed(): void
    {
        $customerId = 2;
        $email = 'customer@example.com';

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getId')->willReturn($customerId);
        $customer->method('getEmail')->willReturn($email);
        $customer->method('getStoreId')->willReturn(2);

        $extension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['setIsSubscribed']
        );
        $extension->expects($this->once())->method('setIsSubscribed')->with(true);
        $customer->method('getExtensionAttributes')->willReturn($extension);

        $guestSubscriber = $this->createMock(Subscriber::class);
        $guestSubscriber->method('getStatus')->willReturn(Subscriber::STATUS_UNSUBSCRIBED);

        $customerSubscriber = $this->createMock(Subscriber::class);
        $customerSubscriber->method('getStatus')->willReturn(Subscriber::STATUS_SUBSCRIBED);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('subscriber_email', ['in' => [$email]])
            ->willReturnSelf();
        $collection->expects($this->never())->method('getFirstItem');
        $collection->expects($this->once())
            ->method('getItemByColumnValue')
            ->willReturnCallback(function (
                string $field,
                int|string $value
            ) use (
                $customerId,
                $guestSubscriber,
                $customerSubscriber
            ) {
                $this->assertSame('customer_id', $field);
                $this->assertSame($customerId, $value);
                $this->assertSame(Subscriber::STATUS_UNSUBSCRIBED, (int)$guestSubscriber->getStatus());

                return $customerSubscriber;
            });
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $searchResults = $this->createMock(SearchResults::class);
        $searchResults->method('getItems')->willReturn([$customer]);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertSame(
            $searchResults,
            $this->plugin->afterGetList($subject, $searchResults)
        );
    }

    /**
     * Test afterGetList when the same email exists in multiple stores.
     *
     * This guards against the original regression where resolving by subscriber_email could pick
     * the wrong row for a customer returned by searchCriteria when the same email exists in more
     * than one store. The plugin must resolve by customer_id so that each customer gets the
     * subscription status from the correct row.
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterGetList()
     * @return void
     */
    public function testAfterGetListUsesCustomerIdSubscriberWhenSameEmailExistsAcrossStores(): void
    {
        $customer1Id = 1;
        $customer2Id = 2;
        $email = 'customer@example.com';
        $store1Id = 1;
        $store2Id = 2;

        /** @var CustomerInterface|MockObject $customerFromStore1 */
        $customerFromStore1 = $this->createMock(CustomerInterface::class);
        $customerFromStore1->method('getId')->willReturn($customer1Id);
        $customerFromStore1->method('getEmail')->willReturn($email);
        $customerFromStore1->method('getStoreId')->willReturn($store1Id);

        $store1Extension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['setIsSubscribed']
        );
        $store1Extension->expects($this->once())->method('setIsSubscribed')->with(true);
        $customerFromStore1->method('getExtensionAttributes')->willReturn($store1Extension);

        /** @var CustomerInterface|MockObject $customerFromStore2 */
        $customerFromStore2 = $this->createMock(CustomerInterface::class);
        $customerFromStore2->method('getId')->willReturn($customer2Id);
        $customerFromStore2->method('getEmail')->willReturn($email);
        $customerFromStore2->method('getStoreId')->willReturn($store2Id);

        $store2Extension = $this->createPartialMockWithReflection(
            CustomerExtensionInterface::class,
            ['setIsSubscribed']
        );
        $store2Extension->expects($this->once())->method('setIsSubscribed')->with(false);
        $customerFromStore2->method('getExtensionAttributes')->willReturn($store2Extension);

        $customerSubscriberFromStore1 = $this->createMock(Subscriber::class);
        $customerSubscriberFromStore1->method('getStatus')->willReturn(Subscriber::STATUS_SUBSCRIBED);

        $customerSubscriberFromStore2 = $this->createMock(Subscriber::class);
        $customerSubscriberFromStore2->method('getStatus')->willReturn(Subscriber::STATUS_UNSUBSCRIBED);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('subscriber_email', ['in' => [$email, $email]])
            ->willReturnSelf();
        $collection->expects($this->exactly(2))
            ->method('getItemByColumnValue')
            ->willReturnCallback(function (
                string $field,
                int|string $value
            ) use (
                $customer1Id,
                $customer2Id,
                $customerSubscriberFromStore1,
                $customerSubscriberFromStore2
            ) {
                $this->assertSame('customer_id', $field);

                if ($field === 'customer_id' && $value === $customer1Id) {
                    return $customerSubscriberFromStore1;
                }

                if ($field === 'customer_id' && $value === $customer2Id) {
                    return $customerSubscriberFromStore2;
                }

                return null;
            });
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $searchResults = $this->createMock(SearchResults::class);
        $searchResults->method('getItems')->willReturn([$customerFromStore1, $customerFromStore2]);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertSame(
            $searchResults,
            $this->plugin->afterGetList($subject, $searchResults)
        );
    }

    /**
     * Test afterDelete logs exception when website scope is enabled and website not found
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterDelete()
     * @return void
     */
    public function testAfterDeleteWithWebsiteScopeLogsExceptionWhenWebsiteNotFound(): void
    {
        $customerEmail = 'email@example.com';
        $websiteId = 1;

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('subscriber_email', $customerEmail)
            ->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $this->shareConfig->method('isWebsiteScope')->willReturn(true);
        $exception = new NoSuchEntityException(__('Website not found'));
        $this->storeManager->method('getWebsite')->with($websiteId)->willThrowException($exception);
        $this->logger->expects($this->once())->method('error')->with($exception);

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn($customerEmail);
        $customer->method('getWebsiteId')->willReturn($websiteId);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertTrue($this->plugin->afterDelete($subject, true, $customer));
    }

    /**
     * Test afterDelete with website scope filters collection by store ids
     *
     * @covers \Magento\Newsletter\Model\Plugin\CustomerPlugin::afterDelete()
     * @return void
     */
    public function testAfterDeleteWithWebsiteScopeFiltersByStoreIds(): void
    {
        $customerEmail = 'email@example.com';
        $websiteId = 1;
        $storeIds = [1, 2];

        $subscriber = $this->createMock(Subscriber::class);
        $subscriber->expects($this->once())->method('delete');
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->exactly(2))->method('addFieldToFilter')->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$subscriber]));
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $this->shareConfig->method('isWebsiteScope')->willReturn(true);
        $website = $this->createMock(Website::class);
        $website->method('getStoreIds')->willReturn($storeIds);
        $this->storeManager->method('getWebsite')->with($websiteId)->willReturn($website);

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn($customerEmail);
        $customer->method('getWebsiteId')->willReturn($websiteId);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->createMock(CustomerRepositoryInterface::class);
        $this->assertTrue($this->plugin->afterDelete($subject, true, $customer));
    }
}
