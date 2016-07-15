<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $this->subscriberFactory = $this->getMockBuilder('\Magento\Newsletter\Model\SubscriberFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subscriber = $this->getMockBuilder('\Magento\Newsletter\Model\Subscriber')
            ->setMethods(['loadByEmail', 'getId', 'delete', 'updateSubscription', 'subscribeCustomerById', 'unsubscribeCustomerById'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriberFactory->expects($this->any())->method('create')->willReturn($this->subscriber);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->plugin = $this->objectManager->getObject(
            'Magento\Newsletter\Model\Plugin\CustomerPlugin',
            [
                'subscriberFactory' => $this->subscriberFactory
            ]
        );
    }

    public function testAfterSave()
    {
        $customerId = 1;
        $subject = $this->getMock('\Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $this->getMock('Magento\Customer\Api\Data\CustomerInterface');
        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $this->subscriber->expects($this->once())->method('updateSubscription')->with($customerId)->willReturnSelf();

        $this->assertEquals($customer, $this->plugin->afterSave($subject, $customer));
    }

    public function testAroundSaveWithoutIsSubscribed()
    {
        $passwordHash = null;
        $customerId = 1;
        /** @var CustomerInterface | \PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMock('Magento\Customer\Api\Data\CustomerInterface');
        $proceed  = function(CustomerInterface $customer, $passwordHash = null) use($customer) {
            return $customer;
        };
        /** @var CustomerRepository | \PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock('\Magento\Customer\Api\CustomerRepositoryInterface');

        $customer->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn($customerId);

        $this->assertEquals($customer, $this->plugin->aroundSave($subject, $proceed, $customer, $passwordHash));
    }

    /**
     * @return array
     */
    public function provideExtensionAttributeDataForAroundSave() {
        return [
            [true, true] ,
            [false, false]
        ];
    }

    /**
     * @dataProvider provideExtensionAttributeDataForAroundSave
     */
    public function testAroundSaveWithIsSubscribed($isSubscribed, $subscribeIsCreated) {
        $passwordHash = null;
        $customerId = 1;
        /** @var CustomerInterface | \PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMock('Magento\Customer\Api\Data\CustomerInterface');
        $extensionAttributes = $this
            ->getMockBuilder("Magento\Customer\Api\Data\CustomerExtensionInterface")
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

        $proceed  = function(CustomerInterface $customer, $passwordHash = null) use($customer) {
            return $customer;
        };
        /** @var CustomerRepository | \PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock('\Magento\Customer\Api\CustomerRepositoryInterface');

        $customer->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn($customerId);

        $this->assertEquals($customer, $this->plugin->aroundSave($subject, $proceed, $customer, $passwordHash));
    }

    public function testAroundDelete()
    {
        $deleteCustomer = function () {
            return true;
        };
        $subject = $this->getMock('\Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $this->getMock('Magento\Customer\Api\Data\CustomerInterface');
        $customer->expects($this->once())->method('getEmail')->willReturn('test@test.com');
        $this->subscriber->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $this->subscriber->expects($this->once())->method('getId')->willReturn(1);
        $this->subscriber->expects($this->once())->method('delete')->willReturnSelf();

        $this->assertEquals(true, $this->plugin->aroundDelete($subject, $deleteCustomer, $customer));
    }

    public function testAroundDeleteById()
    {
        $customerId = 1;
        $deleteCustomerById = function () {
            return true;
        };
        $subject = $this->getMock('\Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $this->getMock('Magento\Customer\Api\Data\CustomerInterface');
        $subject->expects($this->once())->method('getById')->willReturn($customer);
        $customer->expects($this->once())->method('getEmail')->willReturn('test@test.com');
        $this->subscriber->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $this->subscriber->expects($this->once())->method('getId')->willReturn(1);
        $this->subscriber->expects($this->once())->method('delete')->willReturnSelf();

        $this->assertEquals(true, $this->plugin->aroundDeleteById($subject, $deleteCustomerById, $customerId));
    }
}
