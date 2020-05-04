<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
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

    /**#@+
     * Sample customer data
     */
    const CUSTOMER_ID = 1;
    const CUSTOMER_EMAIL = 'customer@example.com';
    const WEBSITE_ID = 1;

    protected function setUp(): void
    {
        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->customerRegistry = $objectManager->getObject(
            CustomerRegistry::class,
            ['customerFactory' => $this->customerFactory]
        );
        $this->customer = $this->getMockBuilder(Customer::class)
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
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::CUSTOMER_ID);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customer);
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
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::CUSTOMER_ID);
        $this->customer->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::CUSTOMER_EMAIL);
        $this->customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->customer->expects($this->any())
            ->method('setEmail')
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('setWebsiteId')
            ->willReturn($this->customer);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customer);
        $actual = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actual);
        $actualCached = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actualCached);
    }

    public function testRetrieveException()
    {
        $this->expectException(NoSuchEntityException::class);

        $this->customer->expects($this->once())
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customer);
        $this->customerRegistry->retrieve(self::CUSTOMER_ID);
    }

    public function testRetrieveByEmailException()
    {
        $this->expectException(NoSuchEntityException::class);

        $this->customer->expects($this->once())
            ->method('loadByEmail')
            ->with(self::CUSTOMER_EMAIL)
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('getEmail')
            ->willReturn(null);
        $this->customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(null);
        $this->customer->expects($this->any())
            ->method('setEmail')
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('setWebsiteId')
            ->willReturn($this->customer);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customer);
        $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
    }

    public function testRemove()
    {
        $this->customer->expects($this->exactly(2))
            ->method('load')
            ->with(self::CUSTOMER_ID)
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::CUSTOMER_ID);
        $this->customerFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->customer);
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
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::CUSTOMER_ID);
        $this->customer->expects($this->any())
            ->method('getEmail')
            ->willReturn(self::CUSTOMER_EMAIL);
        $this->customer->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->customer->expects($this->any())
            ->method('setEmail')
            ->willReturn($this->customer);
        $this->customer->expects($this->any())
            ->method('setWebsiteId')
            ->willReturn($this->customer);
        $this->customerFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->customer);
        $actual = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actual);
        $this->customerRegistry->removeByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $actual = $this->customerRegistry->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->assertEquals($this->customer, $actual);
    }
}
