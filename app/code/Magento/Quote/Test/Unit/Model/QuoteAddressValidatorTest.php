<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

class QuoteAddressValidatorTest extends \PHPUnit_Framework_TestCase
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

        $this->addressRepositoryMock = $this->getMock(
            '\Magento\Customer\Api\AddressRepositoryInterface',
            [],
            [],
            '',
            false
        );
        $this->quoteAddressMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Address',
            ['getCustomerId', 'load', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMock(
            '\Magento\Customer\Api\CustomerRepositoryInterface',
            [],
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->model = $this->objectManager->getObject(
            '\Magento\Quote\Model\QuoteAddressValidator',
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
        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');

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
        $this->markTestSkipped('MAGETWO-48531');
        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $this->customerRepositoryMock->expects($this->never())->method('getById');
        $address->expects($this->atLeastOnce())->method('getId')->willReturn(101);

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
        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $this->assertTrue($this->model->validate($address));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Invalid address id 100
     */
    public function testValidateWithAddressOfOtherCustomer()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $addressCustomer = 100;
        $addressId = 100;
        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');

        $this->customerRepositoryMock->expects($this->once())->method('getById')->with($addressCustomer)
            ->willReturn($customerMock);
        $this->addressRepositoryMock->expects($this->once())->method('getById')->willReturn($this->quoteAddressMock);
        $customerMock->expects($this->once())->method('getId')->willReturn(42);
        $address->expects($this->atLeastOnce())->method('getId')->willReturn($addressId);
        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($addressCustomer);

        $this->quoteAddressMock->expects($this->once())->method('getCustomerId')->willReturn(42);
        $this->model->validate($address);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Invalid address id 42
     */
    public function testValidateWithInvalidCustomerAddressId()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $customerAddressId = 42;
        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $customerAddress = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');

        $address->expects($this->atLeastOnce())->method('getCustomerAddressId')->willReturn($customerAddressId);
        $this->customerSessionMock->expects($this->once())->method('getCustomerDataObject')->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getAddresses')->willReturn([$customerAddress]);
        $customerAddress->expects($this->once())->method('getId')->willReturn(43);

        $this->model->validate($address);
    }

    public function testValidateWithValidAddress()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $addressCustomer = 100;
        $addressId = 100;
        $customerAddressId = 42;

        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $address->expects($this->atLeastOnce())->method('getId')->willReturn($addressId);
        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($addressCustomer);
        $address->expects($this->atLeastOnce())->method('getCustomerAddressId')->willReturn($customerAddressId);
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');
        $customerAddress = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getId')->willReturn($addressCustomer);

        $this->addressRepositoryMock->expects($this->once())->method('getById')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->any())->method('getCustomerId')->willReturn($addressCustomer);

        $this->customerSessionMock->expects($this->once())->method('getCustomerDataObject')->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getAddresses')->willReturn([$customerAddress]);
        $customerAddress->expects($this->once())->method('getId')->willReturn(42);

        $this->assertTrue($this->model->validate($address));
    }
}
