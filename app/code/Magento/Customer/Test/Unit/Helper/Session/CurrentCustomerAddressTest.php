<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Helper\Session;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\Session\CurrentCustomerAddress;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrentCustomerAddressTest extends TestCase
{
    /**
     * @var CurrentCustomerAddress
     */
    protected $currentCustomerAddress;

    /**
     * @var CurrentCustomer|MockObject
     */
    protected $currentCustomerMock;

    /**
     * @var AccountManagementInterface|MockObject
     */
    protected $customerAccountManagementMock;

    /**
     * @var AddressInterface
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
        $this->currentCustomerMock = $this->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAccountManagementMock = $this->getMockBuilder(
            AccountManagementInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->currentCustomerAddress = new CurrentCustomerAddress(
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
