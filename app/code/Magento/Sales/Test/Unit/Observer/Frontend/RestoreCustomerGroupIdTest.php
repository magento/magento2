<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Observer\Frontend;

use Magento\Sales\Observer\Frontend\RestoreCustomerGroupId;

/**
 * Tests Magento\Sales\Observer\Frontend\RestoreCustomerGroupIdTest
 */
class RestoreCustomerGroupIdTest extends \PHPUnit\Framework\TestCase
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
        $this->customerAddressHelperMock = $this->createMock(\Magento\Customer\Helper\Address::class);
        $this->quote = new RestoreCustomerGroupId($this->customerAddressHelperMock);
    }

    /**
     * @param string|null $configAddressType
     * @dataProvider restoreCustomerGroupIdDataProvider
     */
    public function testExecute($configAddressType)
    {
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getShippingAssignment', 'getQuote']);
        $observer = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $observer->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);

        $eventMock->expects($this->once())->method('getShippingAssignment')->willReturn($shippingAssignmentMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $shippingMock = $this->createMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);

        $quoteAddress = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'getPrevQuoteCustomerGroupId',
                'unsPrevQuoteCustomerGroupId',
                'hasPrevQuoteCustomerGroupId',
                'setCustomerGroupId',
                'getQuote'
            ]
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
