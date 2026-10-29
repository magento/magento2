<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Plugin\AfterProductLoad;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AfterProductLoadTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var AfterProductLoad
     */
    protected $plugin;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var ProductExtension|MockObject
     */
    protected $productExtensionMock;

    protected function setUp(): void
    {
        $stockRegistryMock = $this->createMock(StockRegistryInterface::class);

        $this->plugin = new AfterProductLoad(
            $stockRegistryMock
        );

        $productId = 5494;
        $stockItemMock = $this->createMock(StockItemInterface::class);

        $stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId)
            ->willReturn($stockItemMock);

        $this->productExtensionMock = $this->createPartialMockWithReflection(
            ProductExtension::class,
            ['setStockItem']
        );
        $this->productExtensionMock->expects($this->once())
            ->method('setStockItem')
            ->with($stockItemMock)
            ->willReturnSelf();

        $this->productMock = $this->createMock(Product::class);
        $this->productMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->productExtensionMock)
            ->willReturnSelf();
        $this->productMock->expects(($this->once()))
            ->method('getId')
            ->willReturn($productId);
    }

    public function testAfterLoad()
    {
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);

        $this->assertEquals(
            $this->productMock,
            $this->plugin->afterLoad($this->productMock)
        );
    }
}
