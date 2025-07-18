<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Product;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Product\QuantityValidator;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\CatalogInventory\Model\Product\QuantityValidator
 */
class QuantityValidatorTest extends TestCase
{
    private const PRODUCT_ID = 42;
    private const WEBSITE_ID = 1;

    /**
     * @var QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private $stockRegistry;

    protected function setUp(): void
    {
        $this->stockRegistry = $this->createMock(StockRegistryInterface::class);

        $this->quantityValidator = new QuantityValidator(
            $this->stockRegistry
        );
    }

    public function testGetDataWithMinMaxAndIncrements(): void
    {
        $stockItem = $this->createMock(StockItemInterface::class);

        $stockItem->method('getMinSaleQty')
            ->willReturn(2.0);

        $stockItem->method('getMaxSaleQty')
            ->willReturn(10.0);

        $stockItem->method('getQtyIncrements')
            ->willReturn(2.0);

        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->with(self::PRODUCT_ID, self::WEBSITE_ID)
            ->willReturn($stockItem);

        $expected = [
            'validate-item-quantity' => [
                'minAllowed' => 2.0,
                'maxAllowed' => 10.0,
                'qtyIncrements' => 2.0
            ]
        ];

        $result = $this->quantityValidator->getData(self::PRODUCT_ID, self::WEBSITE_ID);
        $this->assertEquals($expected, $result);
    }

    public function testReturnsEmptyArrayForNonExistentProductOrWebsite(): void
    {
        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->with(self::PRODUCT_ID, self::WEBSITE_ID)
            ->willReturn(null);

        $result = $this->quantityValidator->getData(self::PRODUCT_ID, self::WEBSITE_ID);
        $this->assertSame([], $result, 'Should return empty array when StockItem is not found');
    }

    public function testHandlesNullValuesFromStockItem(): void
    {
        $stockItem = $this->createMock(StockItemInterface::class);
        $stockItem->method('getMinSaleQty')
            ->willReturn(null);
        $stockItem->method('getMaxSaleQty')
            ->willReturn(null);
        $stockItem->method('getQtyIncrements')
            ->willReturn(null);

        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->with(self::PRODUCT_ID, self::WEBSITE_ID)
            ->willReturn($stockItem);

        $expected = [
            'validate-item-quantity' => [
                'minAllowed'    => null
            ],
        ];
        $result = $this->quantityValidator->getData(self::PRODUCT_ID, self::WEBSITE_ID);
        $this->assertEquals($expected, $result);
    }

    public function testHandlesInvalidValuesFromStockItem(): void
    {
        $stockItem = $this->createMock(StockItemInterface::class);
        $stockItem->method('getMinSaleQty')
            ->willReturn('not-a-number');
        $stockItem->method('getMaxSaleQty')
            ->willReturn(-5);
        $stockItem->method('getQtyIncrements')
            ->willReturn(false);

        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->with(self::PRODUCT_ID, self::WEBSITE_ID)
            ->willReturn($stockItem);

        $expected = [
            'validate-item-quantity' => [
                'minAllowed'    => 'not-a-number',
                'maxAllowed'    => -5
            ],
        ];
        $result = $this->quantityValidator->getData(self::PRODUCT_ID, self::WEBSITE_ID);
        $this->assertEquals($expected, $result);
    }
}
