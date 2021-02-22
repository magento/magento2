<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model\Plugin;

use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;

class CustomerPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Plugin\CustomerPlugin
     */
    private $plugin;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subscriberFactory;

    /**
     * @var SubscriberModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subscriber;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var ExtensionAttributesFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $extensionFactoryMock;

    /**
     * @var CustomerExtensionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerExtensionMock;

    /**
     * @var Subscriber|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subscriberResourceMock;

    /**
     * @var CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerMock;

    protected function setUp(): void
    {
        $this->subscriberFactory = $this->getMockBuilder(\Magento\Newsletter\Model\SubscriberFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subscriber = $this->getMockBuilder(SubscriberModel::class)
            ->setMethods(
                [
                    'loadByEmail',
                    'getId',
                    'getStatus',
                    'delete',
                    'updateSubscription',
                    'subscribeCustomerById',
                    'unsubscribeCustomerById',
                    'isSubscribed',
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $this->extensionFactoryMock = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerExtensionMock = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getIsSubscribed', 'setIsSubscribed'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->subscriberResourceMock = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->setMethods(['getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->subscriberFactory->expects($this->any())->method('create')->willReturn($this->subscriber);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->plugin = $this->objectManager->getObject(
            \Magento\Newsletter\Model\Plugin\CustomerPlugin::class,
            [
                'subscriberFactory' => $this->subscriberFactory,
                'extensionFactory' => $this->extensionFactoryMock,
                'subscriberResource' => $this->subscriberResourceMock,
            ]
        );
    }

    /**
     * @param bool $subscriptionOriginalValue
     * @param bool $subscriptionNewValue
     * @dataProvider afterSaveDataProvider
     * @return void
     */
    public function testAfterSave($subscriptionOriginalValue, $subscriptionNewValue)
    {
        $customerId = 1;
        /** @var CustomerInterface | \PHPUnit\Framework\MockObject\MockObject $result */
        $result = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        /** @var CustomerRepository | \PHPUnit\Framework\MockObject\MockObject $subject */
        $subject = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);

        /** @var CustomerExtensionInterface|\PHPUnit\Framework\MockObject\MockObject $resultExtensionAttributes */
        $resultExtensionAttributes = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getIsSubscribed', 'setIsSubscribed'])
            ->getMockForAbstractClass();
        $result->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $result->expects($this->any())->method('getExtensionAttributes')->willReturn(null);
        $this->extensionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($resultExtensionAttributes);
        $result->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($resultExtensionAttributes)
            ->willReturnSelf();
        $this->customerMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->customerExtensionMock);
        $resultExtensionAttributes->expects($this->any())
            ->method('getIsSubscribed')
            ->willReturn($subscriptionOriginalValue);
        $this->customerExtensionMock->expects($this->any())
            ->method('getIsSubscribed')
            ->willReturn($subscriptionNewValue);

        $this->subscriber->expects($this->any())->method('getId')->willReturn(1);

        $subscriptionStatus = $subscriptionNewValue
            ? SubscriberModel::STATUS_SUBSCRIBED
            : SubscriberModel::STATUS_UNSUBSCRIBED;

        $this->subscriber->expects($this->any())->method('getStatus')->willReturn($subscriptionStatus);

        if ($subscriptionOriginalValue !== $subscriptionNewValue) {
            if ($subscriptionNewValue) {
                $this->subscriber->expects($this->once())->method('subscribeCustomerById')->with($customerId);
            } else {
                $this->subscriber->expects($this->once())->method('unsubscribeCustomerById')->with($customerId);
            }
            $this->subscriber->expects($this->any())->method('isSubscribed')->willReturn($subscriptionNewValue);
            $resultExtensionAttributes->expects($this->once())->method('setIsSubscribed')->with($subscriptionNewValue);
        }

        $this->assertEquals($result, $this->plugin->afterSave($subject, $result, $this->customerMock));
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider()
    {
        return [
            [true, true],
            [false, false],
            [true, false],
            [false, true],
        ];
    }

    public function testAfterDelete()
    {
        $subject = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->once())->method('getEmail')->willReturn('test@test.com');
        $this->subscriber->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $this->subscriber->expects($this->once())->method('getId')->willReturn(1);
        $this->subscriber->expects($this->once())->method('delete')->willReturnSelf();

        $this->assertTrue($this->plugin->afterDelete($subject, true, $customer));
    }

    public function testAroundDeleteById()
    {
        $customerId = 1;
        $deleteCustomerById = function () {
            return true;
        };
        $subject = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $subject->expects($this->once())->method('getById')->willReturn($customer);
        $customer->expects($this->once())->method('getEmail')->willReturn('test@test.com');
        $this->subscriber->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $this->subscriber->expects($this->once())->method('getId')->willReturn(1);
        $this->subscriber->expects($this->once())->method('delete')->willReturnSelf();

        $this->assertTrue($this->plugin->aroundDeleteById($subject, $deleteCustomerById, $customerId));
    }

    /**
     * @param int|null $subscriberStatusKey
     * @param int|null $subscriberStatusValue
     * @param bool $isSubscribed
     * @dataProvider afterGetByIdDataProvider
     * @return void
     */
    public function testAfterGetByIdCreatesExtensionAttributesIfItIsNotSet(
        $subscriberStatusKey,
        $subscriberStatusValue,
        $isSubscribed
    ) {
        $subject = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $subscriber = [$subscriberStatusKey => $subscriberStatusValue];

        $this->extensionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerExtensionMock);
        $this->customerMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->customerExtensionMock)
            ->willReturnSelf();
        $this->customerMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->subscriberResourceMock->expects($this->once())
            ->method('loadByCustomerData')
            ->with($this->customerMock)
            ->willReturn($subscriber);
        $this->customerExtensionMock->expects($this->once())->method('setIsSubscribed')->with($isSubscribed);

        $this->assertEquals(
            $this->customerMock,
            $this->plugin->afterGetById($subject, $this->customerMock)
        );
    }

    public function testAfterGetByIdSetsIsSubscribedFlagIfItIsNotSet()
    {
        $subject = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $subscriber = ['subscriber_id' => 1, 'subscriber_status' => 1];

        $this->customerMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($this->customerExtensionMock);
        $this->customerExtensionMock->expects($this->any())
            ->method('getIsSubscribed')
            ->willReturn(null);
        $this->subscriberResourceMock->expects($this->once())
            ->method('loadByCustomerData')
            ->with($this->customerMock)
            ->willReturn($subscriber);
        $this->customerExtensionMock->expects($this->once())
            ->method('setIsSubscribed')
            ->willReturnSelf();

        $this->assertEquals(
            $this->customerMock,
            $this->plugin->afterGetById($subject, $this->customerMock)
        );
    }

    /**
     * @return array
     */
    public function afterGetByIdDataProvider()
    {
        return [
            ['subscriber_status', 1, true],
            ['subscriber_status', 2, false],
            ['subscriber_status', 3, false],
            ['subscriber_status', 4, false],
            [null, null, false],
        ];
    }
}
