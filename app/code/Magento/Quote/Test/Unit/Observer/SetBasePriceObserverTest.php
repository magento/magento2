<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Observer;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Observer\SetBasePriceObserver;
use PHPUnit\Framework\TestCase;

class SetBasePriceObserverTest extends TestCase
{
    /**
     * @var CatalogHelper
     */
    private CatalogHelper $catalogHelper;

    /**
     * @var SetBasePriceObserver
     */
    private SetBasePriceObserver $observer;

    protected function setUp(): void
    {
        $this->catalogHelper = $this->createMock(CatalogHelper::class);
        $this->observer = new SetBasePriceObserver($this->catalogHelper);
    }

    public function testSkipsWhenQuoteItemIsNotItem(): void
    {
        $observer = $this->buildObserverWithQuoteItem(new \stdClass());

        $this->observer->execute($observer);
        $this->addToAssertionCount(1);
    }

    public function testSkipsWhenProductIsMissing(): void
    {
        $quoteItem = $this->createQuoteItemMock();
        $quoteItem->expects($this->once())->method('getProduct')->willReturn(null);

        $observer = $this->buildObserverWithQuoteItem($quoteItem);

        $this->observer->execute($observer);
        $this->addToAssertionCount(1);
    }

    public function testSkipsWhenBasePriceAlreadyPresent(): void
    {
        $product = $this->createMock(Product::class);

        $quoteItem = $this->createQuoteItemMock();
        $quoteItem->expects($this->once())->method('getProduct')->willReturn($product);
        $quoteItem->expects($this->once())->method('getBasePrice')->willReturn(10.0);
        $quoteItem->expects($this->once())->method('getPrice')->willReturn(null);
        $quoteItem->expects($this->never())->method('setBasePrice');
        $product->expects($this->never())->method('getPriceInfo');

        $observer = $this->buildObserverWithQuoteItem($quoteItem);

        $this->observer->execute($observer);
    }

    public function testSkipsWhenPriceAlreadyPresent(): void
    {
        $product = $this->createMock(Product::class);

        $quoteItem = $this->createQuoteItemMock();
        $quoteItem->expects($this->once())->method('getProduct')->willReturn($product);
        $quoteItem->expects($this->once())->method('getBasePrice')->willReturn(null);
        $quoteItem->expects($this->once())->method('getPrice')->willReturn(50.0);
        $quoteItem->expects($this->never())->method('setBasePrice');
        $product->expects($this->never())->method('getPriceInfo');

        $observer = $this->buildObserverWithQuoteItem($quoteItem);

        $this->observer->execute($observer);
    }

    public function testSetsBasePriceFromProductPriceInfo(): void
    {
        $priceValue = 123.45;

        $price = $this->createMock(PriceInterface::class);
        $price->expects($this->once())->method('getValue')->willReturn($priceValue);

        $priceInfo = $this->createMock(PriceInfoInterface::class);
        $priceInfo->expects($this->once())->method('getPrice')
            ->with('base_price')
            ->willReturn($price);

        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getPriceInfo')->willReturn($priceInfo);

        $quoteItem = $this->createQuoteItemMock();
        $quoteItem->expects($this->exactly(2))->method('getProduct')->willReturn($product);
        $quoteItem->expects($this->once())->method('getBasePrice')->willReturn(null);
        $quoteItem->expects($this->once())->method('getPrice')->willReturn(null);
        $quoteItem->expects($this->once())->method('setBasePrice')->with($priceValue);

        $observer = $this->buildObserverWithQuoteItem($quoteItem);

        $this->observer->execute($observer);
    }

    public function testSwallowsExceptionsFromPriceInfo(): void
    {
        $priceInfo = $this->createMock(PriceInfoInterface::class);
        $priceInfo->expects($this->once())->method('getPrice')
            ->with('base_price')
            ->willThrowException(new \RuntimeException('failure'));

        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getPriceInfo')->willReturn($priceInfo);

        $quoteItem = $this->createQuoteItemMock();
        $quoteItem->expects($this->exactly(2))->method('getProduct')->willReturn($product);
        $quoteItem->expects($this->once())->method('getBasePrice')->willReturn(null);
        $quoteItem->expects($this->once())->method('getPrice')->willReturn(null);
        $quoteItem->expects($this->never())->method('setBasePrice');

        $observer = $this->buildObserverWithQuoteItem($quoteItem);

        $this->observer->execute($observer);
        $this->addToAssertionCount(1);
    }

    private function buildObserverWithQuoteItem($quoteItem): Observer
    {
        $event = new Event(['quote_item' => $quoteItem]);
        return new Observer(['event' => $event]);
    }

    private function createQuoteItemMock(): Item
    {
        return $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct', 'getPrice'])
            ->addMethods(['getBasePrice', 'setBasePrice'])
            ->getMock();
    }
}
