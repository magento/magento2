<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Model\Plugin\CustomerPlugin;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Newsletter Plugin for customer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerPluginTest extends TestCase
{
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
        $this->subscriptionManager = $this->getMockForAbstractClass(SubscriptionManagerInterface::class);
        $this->shareConfig = $this->createMock(Share::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
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
            ]
        );
    }

    /**
     * Test to update customer subscription after save customer
     *
     * @param int|null $originalStatus
     * @param bool|null $newValue
     * @param bool|null $expectedSubscribe
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave(?int $originalStatus, ?bool $newValue, ?bool $expectedSubscribe)
    {
        $storeId = 2;
        $websiteId = 1;
        $customerId = 3;
        $customerEmail = 'email@example.com';

        $store = $this->getMockForAbstractClass(StoreInterface::class);
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

        $customerExtension = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getIsSubscribed', 'setIsSubscribed'])
            ->getMockForAbstractClass();
        $customerExtension->method('getIsSubscribed')->willReturn($newValue);
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getExtensionAttributes')->willReturn($customerExtension);

        $resultIsSubscribed = $newValue ?? $originalStatus === Subscriber::STATUS_SUBSCRIBED;
        if ($expectedSubscribe !== null) {
            $resultSubscriber = $this->createMock(Subscriber::class);
            $resultSubscriber->method('isSubscribed')->willReturn($resultIsSubscribed);
            $this->subscriptionManager->expects($this->once())
                ->method($expectedSubscribe ? 'subscribeCustomer' : 'unsubscribeCustomer')
                ->with($customerId, $storeId)
                ->willReturn($resultSubscriber);
        } else {
            $this->subscriptionManager->expects($this->never())->method('subscribeCustomer');
            $this->subscriptionManager->expects($this->never())->method('unsubscribeCustomer');
        }
        $resultExtension = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getIsSubscribed', 'setIsSubscribed'])
            ->getMockForAbstractClass();
        $resultExtension->expects($this->once())->method('setIsSubscribed')->with($resultIsSubscribed);
        /** @var CustomerInterface|MockObject $result */
        $result = $this->getMockForAbstractClass(CustomerInterface::class);
        $result->method('getId')->willReturn($customerId);
        $result->method('getEmail')->willReturn($customerEmail);
        $result->method('getExtensionAttributes')->willReturn($resultExtension);

        /** @var CustomerRepository|MockObject $subject */
        $subject = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->assertEquals($result, $this->plugin->afterSave($subject, $result, $customer));
    }

    /**
     * Data provider for testAfterSave()
     *
     * @return array
     */
    public function afterSaveDataProvider(): array
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
     */
    public function testAfterDelete()
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
        $subject = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getEmail')->willReturn($customerEmail);

        $this->assertTrue($this->plugin->afterDelete($subject, true, $customer));
    }

    /**
     * Test to delete subscriptions after delete customer by id
     */
    public function testAroundDeleteById()
    {
        $customerId = 1;
        $customerEmail = 'test@test.com';
        $websiteId = 1;
        $storeIds = [1, 2];
        $deleteCustomerById = function () {
            return true;
        };
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->expects($this->once())->method('getEmail')->willReturn($customerEmail);
        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
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
     * Test to load extension attribute after get by id
     */
    public function testAfterGetByIdCreatesExtensionAttributes(): void
    {
        $storeId = 2;
        $websiteId = 1;
        $customerId = 3;
        $customerEmail = 'email@example.com';
        $subscribed = true;

        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->method('getId')->willReturn($storeId);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->willReturn($store);

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getId')->willReturn($customerId);
        $customer->method('getEmail')->willReturn($customerEmail);

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

        $customerExtension = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getIsSubscribed', 'setIsSubscribed'])
            ->getMockForAbstractClass();
        $customerExtension->expects($this->once())->method('setIsSubscribed')->with($subscribed);
        $this->extensionFactory->expects($this->once())->method('create')->willReturn($customerExtension);
        $customer->expects($this->once())->method('setExtensionAttributes')->with($customerExtension);

        /** @var CustomerRepositoryInterface|MockObject $subject */
        $subject = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->assertEquals(
            $customer,
            $this->plugin->afterGetById($subject, $customer)
        );
    }
}
