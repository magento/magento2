<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Helper\Session;

class CurrentCustomerAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomerAddress
     */
    protected $currentCustomerAddress;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $currentCustomerMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerAccountManagementMock;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface
     */
    protected $customerAddressDataMock;

    /**
     * @var int
     */
    protected $customerCurrentId = 100;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->currentCustomerMock = $this->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAccountManagementMock = $this->getMockBuilder(
            \Magento\Customer\Api\AccountManagementInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->currentCustomerAddress = new \Magento\Customer\Helper\Session\CurrentCustomerAddress(
            $this->currentCustomerMock,
            $this->customerAccountManagementMock
        );
    }

    /**
     * test getDefaultBillingAddress
     */
    public function testGetDefaultBillingAddress()
    {
        $this->currentCustomerMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($this->customerCurrentId);

        $this->customerAccountManagementMock->expects($this->once())
            ->method('getDefaultBillingAddress')
            ->willReturn($this->customerAddressDataMock);
        $this->assertEquals(
            $this->customerAddressDataMock,
            $this->currentCustomerAddress->getDefaultBillingAddress()
        );
    }

    /**
     * test getDefaultShippingAddress
     */
    public function testGetDefaultShippingAddress()
    {
        $this->currentCustomerMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($this->customerCurrentId);
        $this->customerAccountManagementMock->expects($this->once())
            ->method('getDefaultShippingAddress')
            ->willReturn($this->customerAddressDataMock);
        $this->assertEquals(
            $this->customerAddressDataMock,
            $this->currentCustomerAddress->getDefaultShippingAddress()
        );
    }
}
