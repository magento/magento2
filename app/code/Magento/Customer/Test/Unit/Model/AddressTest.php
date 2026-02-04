<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    private const ORIG_CUSTOMER_ID = 1;
    private const ORIG_PARENT_ID = 2;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var Customer|MockObject
     */
    protected $customer;

    /**
     * @var CustomerFactory|MockObject
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address|MockObject
     */
    protected $resource;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->customer = $this->createMock(Customer::class);
        $this->customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::ORIG_CUSTOMER_ID);
        $this->customer->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->customerFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->customer);

        $this->resource = $this->createMock(\Magento\Customer\Model\ResourceModel\Address::class);

        $this->address = $this->objectManager->getObject(
            Address::class,
            [
                'customerFactory' => $this->customerFactory,
                'resource' => $this->resource,
            ]
        );
    }

    public function testCustomerId()
    {
        $this->address->setParentId(self::ORIG_PARENT_ID);
        $this->assertEquals(self::ORIG_PARENT_ID, $this->address->getCustomerId());

        $this->address->setCustomerId(self::ORIG_CUSTOMER_ID);
        $this->assertEquals(self::ORIG_CUSTOMER_ID, $this->address->getCustomerId());
    }

    public function testCustomer()
    {
        $this->address->unsetData('customer_id');
        $this->assertFalse($this->address->getCustomer());

        $this->address->setCustomerId(self::ORIG_CUSTOMER_ID);

        $customer = $this->address->getCustomer();
        $this->assertEquals(self::ORIG_CUSTOMER_ID, $customer->getId());

        /** @var Customer $customer */
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->any())
            ->method('getId')
            ->willReturn(self::ORIG_CUSTOMER_ID + 1);

        $this->address->setCustomer($customer);
        $this->assertEquals(self::ORIG_CUSTOMER_ID + 1, $this->address->getCustomerId());
    }

    public function testGetAttributes()
    {
        $resultValue = 'test';

        $this->resource->expects($this->any())
            ->method('loadAllAttributes')
            ->willReturnSelf();
        $this->resource->expects($this->any())
            ->method('getSortedAttributes')
            ->willReturn($resultValue);

        $this->assertEquals($resultValue, $this->address->getAttributes());
    }

    public function testRegionId()
    {
        $this->address->setRegionId(1);
        $this->assertEquals(1, $this->address->getRegionId());
    }

    public function testGetEntityTypeId()
    {
        $mockEntityType = $this->createMock(Type::class);
        $mockEntityType->expects($this->any())
            ->method('getId')
            ->willReturn(self::ORIG_CUSTOMER_ID);

        $this->resource->expects($this->any())
            ->method('getEntityType')
            ->willReturn($mockEntityType);

        $this->assertEquals(self::ORIG_CUSTOMER_ID, $this->address->getEntityTypeId());
    }
}
