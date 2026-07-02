<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Collector;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Quote\Model\Quote\QuantityCollector;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Quote\TotalsCollectorList;
use Magento\Quote\Model\QuoteValidator;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TotalsCollectorTest extends TestCase
{
    /**
     * @var TotalsCollector|MockObject
     */
    private TotalsCollector $model;

    /**
     * @var TotalFactory|MockObject
     */
    private TotalFactory $totalFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private ManagerInterface $eventManagerMock;

    /**
     * @var QuoteValidator|MockObject
     */
    private QuoteValidator $quoteValidatorMock;

    /**
     * @var QuantityCollector|MockObject
     */
    private QuantityCollector $quantityCollectorMock;

    /**
     * @var ObjectManagerHelper
     */
    private ObjectManagerHelper $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->totalFactoryMock = $this->createMock(TotalFactory::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->quoteValidatorMock = $this->createMock(QuoteValidator::class);
        $this->quantityCollectorMock = $this->createMock(QuantityCollector::class);

        $this->model = $this->getMockBuilder(TotalsCollector::class)
            ->setConstructorArgs([
                $this->createMock(Collector::class),
                $this->createMock(CollectorFactory::class),
                $this->eventManagerMock,
                $this->createMock(StoreManagerInterface::class),
                $this->totalFactoryMock,
                $this->createMock(TotalsCollectorList::class),
                $this->createMock(ShippingFactory::class),
                $this->createMock(ShippingAssignmentFactory::class),
                $this->quoteValidatorMock,
                $this->quantityCollectorMock,
            ])
            ->onlyMethods(['collectAddressTotals'])
            ->getMock();
    }

    /**
     * Verifies that shipping totals from the shipping address are not overwritten
     * when billing address is collected after the shipping address.
     *
     * @see https://github.com/magento/magento2/issues/26209
     */
    public function testCollectPreservesShippingAmountWhenBillingAddressComesAfter(): void
    {
        $shippingAmount     = 10.0;
        $shippingDescription = 'Flat Rate - Fixed';

        $shippingAddress = $this->createMock(Address::class);
        $shippingAddress->method('getAddressType')->willReturn(Address::ADDRESS_TYPE_SHIPPING);

        $billingAddress = $this->createMock(Address::class);
        $billingAddress->method('getAddressType')->willReturn(Address::ADDRESS_TYPE_BILLING);

        $shippingAddressTotal = $this->createMock(Total::class);
        $shippingAddressTotal->method('getShippingAmount')->willReturn($shippingAmount);
        $shippingAddressTotal->method('getBaseShippingAmount')->willReturn($shippingAmount);
        $shippingAddressTotal->method('getShippingDescription')->willReturn($shippingDescription);
        $shippingAddressTotal->method('getSubtotal')->willReturn(100.0);
        $shippingAddressTotal->method('getBaseSubtotal')->willReturn(100.0);
        $shippingAddressTotal->method('getSubtotalWithDiscount')->willReturn(100.0);
        $shippingAddressTotal->method('getBaseSubtotalWithDiscount')->willReturn(100.0);
        $shippingAddressTotal->method('getGrandTotal')->willReturn(110.0);
        $shippingAddressTotal->method('getBaseGrandTotal')->willReturn(110.0);

        $billingAddressTotal = $this->createMock(Total::class);
        $billingAddressTotal->method('getShippingAmount')->willReturn(0.0);
        $billingAddressTotal->method('getBaseShippingAmount')->willReturn(0.0);
        $billingAddressTotal->method('getShippingDescription')->willReturn('');
        $billingAddressTotal->method('getSubtotal')->willReturn(0.0);
        $billingAddressTotal->method('getBaseSubtotal')->willReturn(0.0);
        $billingAddressTotal->method('getSubtotalWithDiscount')->willReturn(0.0);
        $billingAddressTotal->method('getBaseSubtotalWithDiscount')->willReturn(0.0);
        $billingAddressTotal->method('getGrandTotal')->willReturn(0.0);
        $billingAddressTotal->method('getBaseGrandTotal')->willReturn(0.0);

        $quoteMock = $this->createMock(Quote::class);
        // Billing address comes AFTER shipping address (triggering the bug)
        $quoteMock->method('getAllAddresses')->willReturn([$shippingAddress, $billingAddress]);
        $quoteMock->method('getGrandTotal')->willReturn(110.0);
        $quoteMock->method('getBaseGrandTotal')->willReturn(110.0);
        $quoteMock->method('getData')->with('coupon_code')->willReturn(null);

        /** @var Total $aggregateTotal */
        $aggregateTotal = $this->objectManagerHelper->getObject(Total::class);

        $this->totalFactoryMock->method('create')->willReturn($aggregateTotal);

        $this->model->method('collectAddressTotals')
            ->willReturnCallback(
                static function (Quote $quote, Address $address) use (
                    $shippingAddress,
                    $shippingAddressTotal,
                    $billingAddressTotal
                ): Total {
                    return $address === $shippingAddress ? $shippingAddressTotal : $billingAddressTotal;
                }
            );

        $this->eventManagerMock->method('dispatch');
        $this->quantityCollectorMock->method('collectItemsQtys');
        $this->quoteValidatorMock->method('validateQuoteAmount');

        $result = $this->model->collect($quoteMock);

        $this->assertEquals(
            $shippingAmount,
            $result->getShippingAmount(),
            'Shipping amount must come from the shipping address and must not be overwritten by the billing address'
        );
        $this->assertEquals(
            $shippingAmount,
            $result->getBaseShippingAmount(),
            'Base shipping amount must come from the shipping address and must not be overwritten by the billing address'
        );
        $this->assertEquals(
            $shippingDescription,
            $result->getShippingDescription(),
            'Shipping description must come from the shipping address and must not be overwritten by the billing address'
        );
    }
}
