<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\OneTouchOrdering\Model\CustomerData;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CustomerDataTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    private $customer;
    /**
     * @var CustomerData
     */
    private $customerData;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerDataModel;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressDataModel;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->customer = $this->createMock(Customer::class);
        $this->customerDataModel = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->customerAddressMock = $this->createMock(Address::class);
        $this->customerAddressDataModel = $this->createMock(
            \Magento\Customer\Api\Data\AddressInterface::class
        );

        $this->customerData = $objectManager->getObject(CustomerData::class);
    }

    public function testGetDefaultBillingAddressDataModel()
    {
        $this->customerData->setCustomer($this->customer);
        $this->customer
            ->expects($this->once())
            ->method('getDefaultBillingAddress')
            ->willReturn($this->customerAddressMock);
        $this->customerAddressMock
            ->expects($this->once())
            ->method('getDataModel')
            ->willReturn($this->customerAddressDataModel);

        $result = $this->customerData->getDefaultBillingAddressDataModel();
        $this->assertSame($result, $this->customerAddressDataModel);
    }

    public function testGetDefaultShippingAddressDataModel()
    {
        $this->customerData->setCustomer($this->customer);
        $this->customer
            ->expects($this->once())
            ->method('getDefaultShippingAddress')
            ->willReturn($this->customerAddressMock);
        $this->customerAddressMock
            ->expects($this->once())
            ->method('getDataModel')
            ->willReturn($this->customerAddressDataModel);

        $result = $this->customerData->getDefaultShippingAddressDataModel();
        $this->assertSame($result, $this->customerAddressDataModel);
    }

    public function testShippingAddressDataModel()
    {
        $addressId = 123;

        $this->customerSession
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);
        $this->customer
            ->expects($this->once())
            ->method('getAddressById')
            ->with($addressId)
            ->willReturn($this->customerAddressMock);
        $this->customerAddressMock
            ->expects($this->once())
            ->method('getDataModel')
            ->willReturn($this->customerAddressDataModel);

        $result = $this->customerData->getShippingAddressDataModel($addressId);
        $this->assertSame($result, $this->customerAddressDataModel);
    }

    public function testGetCustomerDataModel()
    {
        $this->customerData->setCustomer($this->customer);
        $this->customer
            ->expects($this->once())
            ->method('getDataModel')
            ->willReturn($this->customerDataModel);
        $result = $this->customerData->getCustomerDataModel();
        $this->assertSame($result, $this->customerDataModel);
    }

    public function testGetCustomerId()
    {
        $customerId = 32;
        $this->customerData->setCustomer($this->customer);
        $this->customer
            ->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $result = $this->customerData->getCustomerId();
        $this->assertSame($result, $customerId);
    }

    public function testNoCustomer()
    {
        $this->expectException(LocalizedException::class);
        $this->customerData->getCustomerDataModel();
    }
}
