<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\ResourceModel\CustomerFactory as CustomerResourceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for CustomerRegistry
 *
 */
class CustomerRegistryTest extends TestCase
{
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerFactory|MockObject
     */
    private $customerFactory;

    /**
     * @var Customer|MockObject
     */
    private $customer;

    /**
     * @var CustomerResource|MockObject
     */
    private $customerResource;

    /**
     * @var CustomerResourceFactory|MockObject
     */
    private $customerResourceFactory;

    /**#@+
     * Sample customer data
     */
    const CUSTOMER_ID = 1;
    const CUSTOMER_EMAIL = 'customer@example.com';
    const WEBSITE_ID = 1;

    protected function setUp()
    {
        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerResourceFactory = $this->createPartialMock(
            CustomerResourceFactory::class,
            ['create']
        );
        $objectManager = new ObjectManager($this);
        $this->customerRegistry = $objectManager->getObject(
            CustomerRegistry::class,
            [
                'customerFactory' => $this->customerFactory,
                'customerResourceFactory' => $this->customerResourceFactory
            ]
        );
        $this->customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getEmail',
                    'getWebsiteId',
                    '__wakeup',
                    'setEmail',
                    'setWebsiteId',
                    'loadByEmail',
                ]
            )
            ->getMock();

        $this->customerResource = $this->createPartialMock(
            CustomerResource::class,
            ['load']
        );
    }

    public function testRetrieve()
    {
        $this->customerResourceFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerResource));
        $this->customerResource->expects($this->once())
            ->method('load')
            ->with($this->customer, self::CUSTOMER_ID);
        $this->customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customer));
        $actual = $this->customerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->customer, $actual);
        $actualCached = $this->customerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->customer, $actualCached);
    }

    public function testRetrieveByEmail()
    {
        $this->customer->expects($this->once())
            ->method('loadByEmail')
            ->with(self::CUSTOMER_EMAIL)
            ->will($this->returnValue($this->customer));
        $this->customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->customer->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue(self::CUSTOMER_EMAIL));
        $this->customer->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(self::WEBSITE_ID));
        $this->customer->expects($this->any())
            ->method('setEmail')
            ->will($this->returnValue($this->customer));
        $this->customer->expects($this->any())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->customer));
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customer));
        $actual = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actual);
        $actualCached = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actualCached);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveException()
    {
        $this->customerResourceFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerResource));
        $this->customerResource->expects($this->once())
            ->method('load')
            ->with($this->customer, self::CUSTOMER_ID);
        $this->customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customer));
        $this->customerRegistry->retrieve(self::CUSTOMER_ID);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveByEmailException()
    {
        $this->customer->expects($this->once())
            ->method('loadByEmail')
            ->with(self::CUSTOMER_EMAIL)
            ->will($this->returnValue($this->customer));
        $this->customer->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue(null));
        $this->customer->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(null));
        $this->customer->expects($this->any())
            ->method('setEmail')
            ->will($this->returnValue($this->customer));
        $this->customer->expects($this->any())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->customer));
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customer));
        $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
    }

    public function testRemove()
    {
        $this->customerResourceFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->customerResource));
        $this->customerResource->expects($this->atLeastOnce())
            ->method('load')
            ->with($this->customer, self::CUSTOMER_ID);
        $this->customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->customerFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($this->customer));
        $actual = $this->customerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->customer, $actual);
        $this->customerRegistry->remove(self::CUSTOMER_ID);
        $actual = $this->customerRegistry->retrieve(self::CUSTOMER_ID);
        $this->assertEquals($this->customer, $actual);
    }

    public function testRemoveByEmail()
    {
        $this->customer->expects($this->exactly(2))
            ->method('loadByEmail')
            ->with(self::CUSTOMER_EMAIL)
            ->will($this->returnValue($this->customer));
        $this->customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CUSTOMER_ID));
        $this->customer->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue(self::CUSTOMER_EMAIL));
        $this->customer->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(self::WEBSITE_ID));
        $this->customer->expects($this->any())
            ->method('setEmail')
            ->will($this->returnValue($this->customer));
        $this->customer->expects($this->any())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->customer));
        $this->customerFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($this->customer));
        $actual = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actual);
        $this->customerRegistry->removeByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $actual = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actual);
    }
}
