<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for CustomerRegistry
 *
 */
class CustomerRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**#@+
     * Sample customer data
     */
    const CUSTOMER_ID = 1;
    const CUSTOMER_EMAIL = 'customer@example.com';
    const WEBSITE_ID = 1;

    protected function setUp()
    {
        $this->customerFactory = $this->getMockBuilder(\Magento\Customer\Model\CustomerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->customerRegistry = $objectManager->getObject(
            \Magento\Customer\Model\CustomerRegistry::class,
            ['customerFactory' => $this->customerFactory]
        );
        $this->customer = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
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
    }

    public function testRetrieve()
    {
        $this->customer->expects($this->once())
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->will($this->returnValue($this->customer));
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
        $this->customer->expects($this->once())
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->will($this->returnValue($this->customer));
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
        $this->customer->expects($this->exactly(2))
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->will($this->returnValue($this->customer));
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
