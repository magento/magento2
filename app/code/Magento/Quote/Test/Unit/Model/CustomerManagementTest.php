<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model;

/**
 * Class CustomerManagementTest
 */
class CustomerManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagementMock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressRepositoryMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressMock;

    protected function setUp()
    {
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->customerAddressRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AddressRepositoryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->accountManagementMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AccountManagementInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['getId', 'getCustomer', 'getBillingAddress', 'getShippingAddress', 'setCustomer', 'getPasswordHash'],
            [],
            '',
            false
        );
        $this->quoteAddressMock = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            [],
            [],
            '',
            false
        );
        $this->customerMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getId', 'getDefaultBilling']
        );
        $this->customerAddressMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AddressInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->customerManagement = new \Magento\Quote\Model\CustomerManagement(
            $this->customerRepositoryMock,
            $this->customerAddressRepositoryMock,
            $this->accountManagementMock
        );
    }

    public function testPopulateCustomerInfo()
    {
        $this->quoteMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customerMock);
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getDefaultBilling')
            ->willReturn(100500);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getBillingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getShippingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('setCustomer')
            ->with($this->customerMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn('password hash');
        $this->quoteAddressMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->customerAddressRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with(100500)
            ->willReturn($this->customerAddressMock);
        $this->quoteAddressMock->expects($this->atLeastOnce())
            ->method('importCustomerAddressData')
            ->willReturnSelf();
        $this->accountManagementMock->expects($this->once())
            ->method('createAccountWithPasswordHash')
            ->with($this->customerMock, 'password hash')
            ->willReturn($this->customerMock);
        $this->customerManagement->populateCustomerInfo($this->quoteMock);
    }
}
