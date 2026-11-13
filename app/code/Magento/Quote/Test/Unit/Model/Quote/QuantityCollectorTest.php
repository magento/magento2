<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\QuantityCollector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Test for QuantityCollector virtual and total item qty counting
 */
class QuantityCollectorTest extends TestCase
{
    /**
     * @param array $items
     * @param float $itemsQty
     * @param float $virtualQty
     * @return void
     */
    #[DataProvider('itemsDataProvider')]
    public function testCollectItemsQtys(array $items, float $itemsQty, float $virtualQty): void
    {
        $quote = $this->createPartialMock(Quote::class, ['getAllVisibleItems']);
        $quote->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn(array_map([$this, 'createItem'], $items));

        (new QuantityCollector())->collectItemsQtys($quote);

        $this->assertSame(count($items), $quote->getItemsCount());
        $this->assertEquals($itemsQty, $quote->getItemsQty());
        $this->assertEquals($virtualQty, $quote->getVirtualItemsQty());
    }

    /**
     * Each item: [qty, isVirtual, shipSeparately, children as [qty, isVirtual]]
     *
     * @return array
     */
    public static function itemsDataProvider(): array
    {
        return [
            'virtual product' => [[[3, true, false, []]], 3, 3],
            'physical product' => [[[3, false, false, []]], 3, 0],
            'all-virtual bundle shipped separately' => [
                [[2, true, true, [[1, true], [1, true]]], [1, false, false, []]],
                3,
                4,
            ],
            'mixed bundle shipped separately' => [[[2, false, true, [[1, true], [1, false]]]], 2, 2],
            'all-virtual bundle shipped together' => [[[2, true, false, [[1, true], [1, true]]]], 2, 2],
        ];
    }

    /**
     * @param array $data
     * @return Item|Stub
     */
    private function createItem(array $data): Item
    {
        [$qty, $isVirtual, $shipSeparately, $children] = $data;
        $product = $this->createConfiguredStub(Product::class, ['getIsVirtual' => $isVirtual]);

        return $this->createConfiguredStub(
            Item::class,
            [
                'getParentItem' => null,
                'getQty' => $qty,
                'getProduct' => $product,
                'isShipSeparately' => $shipSeparately,
                'getChildren' => array_map(fn(array $child) => $this->createItem([...$child, false, []]), $children),
            ]
        );
    }
}
