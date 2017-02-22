<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Observer\Frontend;

use \Magento\Sales\Observer\Frontend\RestoreCustomerGroupId;

/**
 * Tests Magento\Sales\Observer\Frontend\RestoreCustomerGroupIdTest
 */
class RestoreCustomerGroupIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressHelperMock;

    /**
     * @var RestoreCustomerGroupId
     */
    protected $quote;

    protected function setUp()
    {
        $this->customerAddressHelperMock = $this->getMock('\Magento\Customer\Helper\Address', [], [], '', false);
        $this->quote = new RestoreCustomerGroupId($this->customerAddressHelperMock);
    }

    /**
     * @param string|null $configAddressType
     * @dataProvider restoreCustomerGroupIdDataProvider
     */
    public function testExecute($configAddressType)
    {
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getShippingAssignment', 'getQuote'], [], '', false);
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $observer->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $shippingAssignmentMock = $this->getMock('\Magento\Quote\Api\Data\ShippingAssignmentInterface');
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);

        $eventMock->expects($this->once())->method('getShippingAssignment')->willReturn($shippingAssignmentMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $shippingMock = $this->getMock('\Magento\Quote\Api\Data\ShippingInterface');
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);

        $quoteAddress = $this->getMock(
            '\Magento\Quote\Model\Quote\Address',
            ['getPrevQuoteCustomerGroupId', 'unsPrevQuoteCustomerGroupId', 'hasPrevQuoteCustomerGroupId'],
            [],
            '',
            false
        );
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($quoteAddress);

        $this->customerAddressHelperMock->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($configAddressType));

        $quoteAddress->expects($this->once())->method('hasPrevQuoteCustomerGroupId');
        $id = $quoteAddress->expects($this->any())->method('getPrevQuoteCustomerGroupId');
        $quoteAddress->expects($this->any())->method('setCustomerGroupId')->with($id);
        $quoteAddress->expects($this->any())->method('getQuote');
        $quoteAddress->expects($this->any())->method('unsPrevQuoteCustomerGroupId');

        $this->quote->execute($observer);
    }

    public function restoreCustomerGroupIdDataProvider()
    {
        return [
            [\Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING],
            [null],
            [\Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING],
        ];
    }
}
