<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Tests Magento\Sales\Observer\Frontend\RestoreCustomerGroupIdTest
 */
class RestoreCustomerGroupIdTest extends TestCase
{
    use MockCreationTrait;

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
     */
    #[DataProvider('restoreCustomerGroupIdDataProvider')]
    public function testExecute($configAddressType)
    {
        $eventMock = $this->createPartialMockWithReflection(Event::class, ['getShippingAssignment', 'getQuote']);
        $observer = $this->createPartialMock(Observer::class, ['getEvent']);
        $observer->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
        $quoteMock = $this->createMock(Quote::class);

        $eventMock->expects($this->once())->method('getShippingAssignment')->willReturn($shippingAssignmentMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $shippingMock = $this->createMock(ShippingInterface::class);
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);

        $quoteAddress = $this->createPartialMockWithReflection(QuoteAddress::class, array_merge([
                'getPrevQuoteCustomerGroupId',
                'unsPrevQuoteCustomerGroupId',
                'hasPrevQuoteCustomerGroupId',
                'setCustomerGroupId'
            ], ['getQuote']));
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
