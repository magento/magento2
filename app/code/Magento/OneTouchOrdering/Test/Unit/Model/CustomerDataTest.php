<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\OneTouchOrdering\Model\CustomerData;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CustomerDataTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;
    /**
     * @var CustomerData
     */
    protected $customerData;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDataModel;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressDataModel;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->customerSession = $this->createMock(Session::class);
        $this->customer = $this->createMock(Customer::class);
        $this->customerDataModel = $this->createMock(CustomerInterface::class);
        $this->customerAddressMock = $this->createMock(Address::class);
        $this->customerAddressDataModel = $this->createMock(AddressInterface::class);

        $this->customerData = $objectManager->getObject(
            CustomerData::class,
            [
                'customerSession' => $this->customerSession
            ]
        );
    }

    public function testGetDefaultBillingAddressDataModel()
    {
        $this->customerSession
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);
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
        $this->customerSession
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);
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
        $this->customerSession
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customer);
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
        $this->customerSession
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $result = $this->customerData->getCustomerId();
        $this->assertSame($result, $customerId);
    }
}
