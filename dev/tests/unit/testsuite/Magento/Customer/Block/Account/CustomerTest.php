<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCustomerName()
    {
        $customerName = 'John Doe';

        $customer = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(
            '\Magento\Customer\Api\CustomerRepositoryInterface'
        )->disableOriginalConstructor()->getMock();
        $customerServiceMock->expects($this->any())->method('getById')->will($this->returnValue($customer));

        $viewHelperMock = $this->getMockBuilder(
            'Magento\Customer\Helper\View'
        )->disableOriginalConstructor()->getMock();
        $viewHelperMock->expects($this->any())->method('getCustomerName')->will($this->returnValue($customerName));

        $escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')->disableOriginalConstructor()->getMock();
        $escaperMock->expects(
            $this->any()
        )->method(
            'escapeHtml'
        )->with(
            $customerName
        )->will(
            $this->returnValue($customerName)
        );

        $contextMock = $this->getMockBuilder(
            'Magento\Framework\View\Element\Template\Context'
        )->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())->method('getEscaper')->will($this->returnValue($escaperMock));

        $httpContextMock = $this->getMockBuilder('Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $currentCustomer = $this->getMockBuilder('Magento\Customer\Helper\Session\CurrentCustomer')
            ->disableOriginalConstructor()
            ->getMock();

        $block = new \Magento\Customer\Block\Account\Customer(
            $contextMock,
            $customerServiceMock,
            $viewHelperMock,
            $httpContextMock,
            $currentCustomer
        );

        $this->assertSame($customerName, $block->getCustomerName());
    }
}
