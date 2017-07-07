<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

class CustomerPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Plugin\CustomerPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriberFactory;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriber;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->subscriberFactory = $this->getMockBuilder(\Magento\Newsletter\Model\SubscriberFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subscriber = $this->getMockBuilder(\Magento\Newsletter\Model\Subscriber::class)
            ->setMethods(
                [
                    'loadByEmail',
                    'getId',
                    'delete',
                    'updateSubscription',
                    'subscribeCustomerById',
                    'unsubscribeCustomerById'
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $this->subscriberFactory->expects($this->any())->method('create')->willReturn($this->subscriber);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->plugin = $this->objectManager->getObject(
            \Magento\Newsletter\Model\Plugin\CustomerPlugin::class,
            [
                'subscriberFactory' => $this->subscriberFactory
            ]
        );
    }

    public function testAfterSaveWithoutIsSubscribed()
    {
        $customerId = 1;
        /** @var CustomerInterface | \PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        /** @var CustomerRepository | \PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);

        $customer->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn($customerId);

        $this->assertEquals($customer, $this->plugin->afterSave($subject, $customer, $customer));
    }

    /**
     * @return array
     */
    public function afterSaveExtensionAttributeDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * @param boolean $isSubscribed
     * @param boolean $subscribeIsCreated
     * @dataProvider afterSaveExtensionAttributeDataProvider
     */
    public function testAfterSaveWithIsSubscribed($isSubscribed, $subscribeIsCreated)
    {
        $customerId = 1;
        /** @var CustomerInterface | \PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $extensionAttributes = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->setMethods(["getIsSubscribed", "setIsSubscribed"])
            ->getMock();

        $extensionAttributes
            ->expects($this->atLeastOnce())
            ->method("getIsSubscribed")
            ->willReturn($isSubscribed);

        $customer->expects($this->atLeastOnce())
            ->method("getExtensionAttributes")
            ->willReturn($extensionAttributes);

        if ($subscribeIsCreated) {
            $this->subscriber->expects($this->once())
                ->method("subscribeCustomerById")
                ->with($customerId);
        } else {
            $this->subscriber->expects($this->once())
                ->method("unsubscribeCustomerById")
                ->with($customerId);
        }

        /** @var CustomerRepository | \PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);

        $customer->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn($customerId);

        $this->assertEquals($customer, $this->plugin->afterSave($subject, $customer, $customer));
    }

    public function testAfterDelete()
    {
        $subject = $this->getMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->once())->method('getEmail')->willReturn('test@test.com');
        $this->subscriber->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $this->subscriber->expects($this->once())->method('getId')->willReturn(1);
        $this->subscriber->expects($this->once())->method('delete')->willReturnSelf();

        $this->assertEquals(true, $this->plugin->afterDelete($subject, true, $customer));
    }

    public function testAroundDeleteById()
    {
        $customerId = 1;
        $deleteCustomerById = function () {
            return true;
        };
        $subject = $this->getMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $subject->expects($this->once())->method('getById')->willReturn($customer);
        $customer->expects($this->once())->method('getEmail')->willReturn('test@test.com');
        $this->subscriber->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $this->subscriber->expects($this->once())->method('getId')->willReturn(1);
        $this->subscriber->expects($this->once())->method('delete')->willReturnSelf();

        $this->assertEquals(true, $this->plugin->aroundDeleteById($subject, $deleteCustomerById, $customerId));
    }
}
