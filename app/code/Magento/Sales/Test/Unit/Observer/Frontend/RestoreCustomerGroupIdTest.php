<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer\Frontend;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Observer\Frontend\RestoreCustomerGroupId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Sales\Observer\Frontend\RestoreCustomerGroupIdTest
 */
class RestoreCustomerGroupIdTest extends TestCase
{
    /**
     * @var Address|MockObject
     */
    protected $customerAddressHelperMock;

    /**
     * @var RestoreCustomerGroupId
     */
    protected $quote;

    protected function setUp(): void
    {
        $this->customerAddressHelperMock = $this->createMock(Address::class);
        $this->quote = new RestoreCustomerGroupId($this->customerAddressHelperMock);
    }

    /**
     * @param string|null $configAddressType
     * @dataProvider restoreCustomerGroupIdDataProvider
     */
    public function testExecute($configAddressType)
    {
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getShippingAssignment', 'getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $observer = $this->createPartialMock(Observer::class, ['getEvent']);
        $observer->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $quoteMock = $this->createMock(Quote::class);

        $eventMock->expects($this->once())->method('getShippingAssignment')->willReturn($shippingAssignmentMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);

        $quoteAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)->addMethods(
            [
                'getPrevQuoteCustomerGroupId',
                'unsPrevQuoteCustomerGroupId',
                'hasPrevQuoteCustomerGroupId',
                'setCustomerGroupId'
            ]
        )
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($quoteAddress);

        $this->customerAddressHelperMock->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->willReturn($configAddressType);

        $quoteAddress->expects($this->once())->method('hasPrevQuoteCustomerGroupId');
        $id = $quoteAddress->expects($this->any())->method('getPrevQuoteCustomerGroupId');
        $quoteAddress->expects($this->any())->method('setCustomerGroupId')->with($id);
        $quoteAddress->expects($this->any())->method('getQuote');
        $quoteAddress->expects($this->any())->method('unsPrevQuoteCustomerGroupId');

        $this->quote->execute($observer);
    }

    /**
     * @return array
     */
    public static function restoreCustomerGroupIdDataProvider()
    {
        return [
            [AbstractAddress::TYPE_SHIPPING],
            [null],
            [AbstractAddress::TYPE_BILLING],
        ];
    }
}
