<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\QuoteAddressValidator;

class QuoteAddressValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteAddressValidator
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
    protected $customerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    public function setUp()
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
        $this->customerFactoryMock = $this->getMock(
            '\Magento\Customer\Model\CustomerFactory', ['create', '__wakeup'], [], '', false);
        $this->customerMock = $this->getMock('\Magento\Customer\Model\Customer', [], [], '', false);

        $this->model = $this->objectManager->getObject(
            '\Magento\Quote\Model\QuoteAddressValidator',
            [
                'addressRepository' => $this->addressRepositoryMock,
                'customerFactory' => $this->customerFactoryMock
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

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->once())->method('load')->with($customerId);
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);
        $this->model->validate($address);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Invalid address id 101
     */
    public function testValidateInvalidAddress()
    {
        $this->customerFactoryMock->expects($this->never())->method('create');
        $this->customerMock->expects($this->never())->method('load');

        $this->addressRepositoryMock->expects($this->once())->method('getById')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $address->expects($this->atLeastOnce())->method('getId')->willReturn(101);
        $this->model->validate($address);
    }

    /**
     * Neither customer id used nor address id exists
     */
    public function testValidateNewAddress()
    {
        $this->customerFactoryMock->expects($this->never())->method('create');
        $this->addressRepositoryMock->expects($this->never())->method('getById');

        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $this->assertTrue($this->model->validate($address));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Address with id 100 belongs to another customer
     */
    public function testValidateWithAddressOfOtherCustomer()
    {
        $addressCustomer = 100;
        $addressId = 100;

        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $address->expects($this->atLeastOnce())->method('getId')->willReturn($addressId);
        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($addressCustomer);

        /** Customer mock */
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->once())->method('load')->with($addressCustomer);
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue($addressCustomer));

        /** Quote address mock */
        $this->addressRepositoryMock->expects($this->once())->method('getById')
            ->will($this->returnValue($this->quoteAddressMock));

        $this->quoteAddressMock->expects($this->atLeastOnce())->method('getCustomerId')
            ->will($this->returnValue(10));

        /** Validate */
        $this->model->validate($address);
    }

    public function testValidateWithValidAddress()
    {
        $addressCustomer = 100;
        $addressId = 100;

        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $address->expects($this->atLeastOnce())->method('getId')->willReturn($addressId);
        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($addressCustomer);

        /** Customer mock */
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->once())->method('load')->with($addressCustomer);
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue($addressCustomer));

        /** Quote address mock */
        $this->addressRepositoryMock->expects($this->once())->method('getById')
            ->will($this->returnValue($this->quoteAddressMock));

        $this->quoteAddressMock->expects($this->any())->method('getCustomerId')
            ->will($this->returnValue($addressCustomer));

        /** Validate */
        $this->model->validate($address);
    }
}
