<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Helper\Session;

class CurrentCustomerAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomerAddress
     */
    protected $currentCustomerAddress;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentCustomerMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->currentCustomerMock = $this->getMockBuilder('Magento\Customer\Helper\Session\CurrentCustomer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAccountManagementMock = $this->getMockBuilder('Magento\Customer\Api\AccountManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();

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
            ->will($this->returnValue($this->customerCurrentId));

        $this->customerAccountManagementMock->expects($this->once())
            ->method('getDefaultBillingAddress')
            ->will($this->returnValue($this->customerAddressDataMock));
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
            ->will($this->returnValue($this->customerCurrentId));
        $this->customerAccountManagementMock->expects($this->once())
            ->method('getDefaultShippingAddress')
            ->will($this->returnValue($this->customerAddressDataMock));
        $this->assertEquals(
            $this->customerAddressDataMock,
            $this->currentCustomerAddress->getDefaultShippingAddress()
        );
    }
}
