<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\OrderItemPrices;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\QuoteGraphQl\Model\GetOptionsRegularPrice;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\SalesGraphQl\Model\OrderItemPrices\PricesProvider;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Model\Config as TaxConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PricesProviderTest extends TestCase
{
    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var GetOptionsRegularPrice|MockObject
     */
    private $getOptionsRegularPriceMock;

    /**
     * @var TaxConfig|MockObject
     */
    private $taxConfigMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var PricesProvider|MockObject
     */
    private PricesProvider $pricesProvider;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->getOptionsRegularPriceMock = $this->createMock(GetOptionsRegularPrice::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->priceCurrencyMock->method('round')
            ->willReturnCallback(static fn(float $price): float => round($price, 2));
        $this->pricesProvider = new PricesProvider(
            $this->priceCurrencyMock,
            $this->getOptionsRegularPriceMock,
            $this->taxConfigMock
        );
    }

    /**
     * Catalog prices exclude tax
     */
    public function testOriginalIncludingTaxWhenCatalogPriceExcludesTax(): void
    {
        $itemMock = $this->createConfiguredItemMock(
            originalPrice: 100.00,
            taxPercent: 21.0,
            qty: 2,
            productOptions: []
        );
        $this->orderMock->method('getStore')->willReturn($this->storeMock);
        $this->taxConfigMock->expects($this->once())
            ->method('priceIncludesTax')
            ->with($this->storeMock)
            ->willReturn(false);
        $result = $this->pricesProvider->execute($itemMock);
        $this->assertSame(100.00, $result['original_price']['value']);
        $this->assertEqualsWithDelta(121.00, $result['original_price_including_tax']['value'], 0.001);
        $this->assertSame(200.00, $result['original_row_total']['value']);
        $this->assertEqualsWithDelta(
            242.00,
            $result['original_row_total_including_tax']['value'],
            0.001
        );
    }

    /**
     * Catalog prices include tax
     */
    public function testOriginalIncludingTaxWhenCatalogPriceIncludesTax(): void
    {
        $itemMock = $this->createConfiguredItemMock(
            originalPrice: 49.99,
            taxPercent: 21.0,
            qty: 1,
            productOptions: []
        );
        $this->orderMock->method('getStore')->willReturn($this->storeMock);
        $this->taxConfigMock->expects($this->once())
            ->method('priceIncludesTax')
            ->with($this->storeMock)
            ->willReturn(true);
        $result = $this->pricesProvider->execute($itemMock);
        $this->assertSame(49.99, $result['original_price']['value']);
        $this->assertSame(49.99, $result['original_price_including_tax']['value']);
        $this->assertSame(49.99, $result['original_row_total']['value']);
        $this->assertSame(49.99, $result['original_row_total_including_tax']['value']);
    }

    /**
     * @param array<string, mixed> $productOptions
     */
    private function createConfiguredItemMock(
        float $originalPrice,
        float $taxPercent,
        float $qty,
        array $productOptions
    ): MockObject {
        $itemMock = $this->createMock(Item::class);
        $itemMock->method('getOrder')->willReturn($this->orderMock);
        $this->orderMock->method('getOrderCurrencyCode')->willReturn('USD');
        $itemMock->method('getPrice')->willReturn($originalPrice * 0.8);
        $itemMock->method('getPriceInclTax')->willReturn($originalPrice);
        $itemMock->method('getRowTotal')->willReturn($originalPrice * $qty * 0.8);
        $itemMock->method('getRowTotalInclTax')->willReturn($originalPrice * $qty);
        $itemMock->method('getDiscountAmount')->willReturn(0.0);
        $itemMock->method('getOriginalPrice')->willReturn($originalPrice);
        $itemMock->method('getTaxPercent')->willReturn($taxPercent);
        $itemMock->method('getQtyOrdered')->willReturn($qty);
        $itemMock->method('getProductOptions')->willReturn($productOptions);
        return $itemMock;
    }
}
