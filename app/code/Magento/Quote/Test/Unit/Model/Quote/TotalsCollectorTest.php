<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Framework\Event\ManagerInterface;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
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
        $shippingAmount = 10.0;
        $shippingDescription = 'Flat Rate - Fixed';

        $shippingAddress = new class extends Address {
            public function __construct()
            {
            }

            public function getAddressType(): string
            {
                return self::ADDRESS_TYPE_SHIPPING;
            }
        };

        $billingAddress = new class extends Address {
            public function __construct()
            {
            }

            public function getAddressType(): string
            {
                return self::ADDRESS_TYPE_BILLING;
            }
        };

        // Use real Total instances — Total extends DataObject and all its get/set
        // methods are magic, which PHPUnit cannot configure via method stubs.
        $shippingAddressTotal = new Total([
            'shipping_amount'              => $shippingAmount,
            'base_shipping_amount'         => $shippingAmount,
            'shipping_description'         => $shippingDescription,
            'subtotal'                     => 100.0,
            'base_subtotal'                => 100.0,
            'subtotal_with_discount'       => 100.0,
            'base_subtotal_with_discount'  => 100.0,
            'grand_total'                  => 110.0,
            'base_grand_total'             => 110.0,
        ]);

        $billingAddressTotal = new Total();

        $quoteMock = $this->createMock(Quote::class);
        // Billing address comes AFTER shipping address (triggering the bug)
        $quoteMock->method('getAllAddresses')->willReturn([$shippingAddress, $billingAddress]);
        $quoteMock->method('getData')->with('coupon_code')->willReturn(null);

        $aggregateTotal = new Total();
        $this->totalFactoryMock->method('create')->willReturn($aggregateTotal);

        $this->model->method('collectAddressTotals')
            ->willReturnMap([
                [$quoteMock, $shippingAddress, $shippingAddressTotal],
                [$quoteMock, $billingAddress, $billingAddressTotal],
            ]);

        $this->eventManagerMock->method('dispatch');
        $this->quantityCollectorMock->method('collectItemsQtys');
        $this->quoteValidatorMock->method('validateQuoteAmount');

        $result = $this->model->collect($quoteMock);

        $this->assertEquals(
            $shippingAmount,
            $result->getShippingAmount(),
            'Shipping amount must not be overwritten by the billing address'
        );
        $this->assertEquals(
            $shippingAmount,
            $result->getBaseShippingAmount(),
            'Base shipping amount must not be overwritten by the billing address'
        );
        $this->assertEquals(
            $shippingDescription,
            $result->getShippingDescription(),
            'Shipping description must not be overwritten by the billing address'
        );
    }

    /**
     * Verifies that shipping amount is 0 (not null) for virtual-only quotes
     * that have only a billing address.
     *
     * @see https://github.com/magento/magento2/issues/26209
     */
    public function testCollectSetsZeroShippingAmountForVirtualOnlyQuote(): void
    {
        $billingAddress = new class extends Address {
            public function __construct()
            {
            }

            public function getAddressType(): string
            {
                return self::ADDRESS_TYPE_BILLING;
            }
        };

        $billingAddressTotal = new Total([
            'subtotal'                    => 50.0,
            'base_subtotal'               => 50.0,
            'subtotal_with_discount'      => 50.0,
            'base_subtotal_with_discount' => 50.0,
            'grand_total'                 => 50.0,
            'base_grand_total'            => 50.0,
        ]);

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getAllAddresses')->willReturn([$billingAddress]);
        $quoteMock->method('getData')->with('coupon_code')->willReturn(null);

        $aggregateTotal = new Total();
        $this->totalFactoryMock->method('create')->willReturn($aggregateTotal);

        $this->model->method('collectAddressTotals')
            ->willReturnMap([
                [$quoteMock, $billingAddress, $billingAddressTotal],
            ]);

        $this->eventManagerMock->method('dispatch');
        $this->quantityCollectorMock->method('collectItemsQtys');
        $this->quoteValidatorMock->method('validateQuoteAmount');

        $result = $this->model->collect($quoteMock);

        $this->assertSame(
            0,
            $result->getShippingAmount(),
            'Shipping amount must be 0 (not null) for virtual-only quotes'
        );
        $this->assertSame(
            0,
            $result->getBaseShippingAmount(),
            'Base shipping amount must be 0 (not null) for virtual-only quotes'
        );
    }
}
