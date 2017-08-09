<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

class QuoteAddressValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\QuoteAddressValidator
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->addressRepositoryMock = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->quoteAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getCustomerId', 'load', 'getId', '__wakeup']
        );
        $this->customerRepositoryMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->model = $this->objectManager->getObject(
            \Magento\Quote\Model\QuoteAddressValidator::class,
            [
                'addressRepository' => $this->addressRepositoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerSession' => $this->customerSessionMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Invalid customer id 100
     */
    public function testValidateInvalidCustomer()
    {
        $customerId = 100;
        $address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);

        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->with($customerId)
            ->willReturn($customerMock);
        $this->model->validate($address);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Invalid address id 101
     */
    public function testValidateInvalidAddress()
    {
        $address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->customerRepositoryMock->expects($this->never())->method('getById');
        $address->expects($this->atLeastOnce())->method('getCustomerAddressId')->willReturn(101);
        $address->expects($this->once())->method('getId')->willReturn(101);

        $this->addressRepositoryMock->expects($this->once())->method('getById')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $this->model->validate($address);
    }

    /**
     * Neither customer id used nor address id exists
     */
    public function testValidateNewAddress()
    {
        $this->customerRepositoryMock->expects($this->never())->method('getById');
        $this->addressRepositoryMock->expects($this->never())->method('getById');
        $address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->assertTrue($this->model->validate($address));
    }

    public function testValidateWithValidAddress()
    {
        $addressCustomer = 100;
        $customerAddressId = 42;

        $address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($addressCustomer);
        $address->expects($this->atLeastOnce())->method('getCustomerAddressId')->willReturn($customerAddressId);
        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerAddress = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $this->customerRepositoryMock->expects($this->exactly(2))->method('getById')->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getId')->willReturn($addressCustomer);

        $this->addressRepositoryMock->expects($this->once())->method('getById')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->any())->method('getCustomerId')->willReturn($addressCustomer);

        $customerMock->expects($this->once())->method('getAddresses')->willReturn([$customerAddress]);
        $customerAddress->expects($this->once())->method('getId')->willReturn(42);

        $this->assertTrue($this->model->validate($address));
    }
}
