<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Block;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Block\Qtyincrements;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Qtyincrements block
 */
class QtyincrementsTest extends TestCase
{
    /**
     * @var Qtyincrements
     */
    protected $block;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var StockItemInterface|MockObject
     */
    protected $stockItem;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->registryMock = $this->createMock(Registry::class);
        $this->stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->addMethods(['getStockItem'])
            ->onlyMethods(['getQtyIncrements'])
            ->getMockForAbstractClass();
        $this->stockItem->expects($this->any())->method('getStockItem')->willReturn(1);
        $this->stockRegistry = $this->getMockForAbstractClass(
            StockRegistryInterface::class,
            ['getStockItem'],
            '',
            false
        );
        $this->stockRegistry->expects($this->any())->method('getStockItem')->willReturn($this->stockItem);

        $this->block = $objectManager->getObject(
            Qtyincrements::class,
            [
                'registry' => $this->registryMock,
                'stockRegistry' => $this->stockRegistry
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')->willReturn($productTags);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId', '__wakeup']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn(0);
        $product->expects($this->any())->method('getStore')->willReturn($store);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->willReturn($product);
        $this->assertEquals($productTags, $this->block->getIdentities());
    }

    /**
     * @param int $productId
     * @param int $qtyInc
     * @param bool $isSaleable
     * @param int|bool $result
     * @dataProvider getProductQtyIncrementsDataProvider
     */
    public function testGetProductQtyIncrements($productId, $qtyInc, $isSaleable, $result)
    {
        $this->stockItem->expects($this->once())
            ->method('getQtyIncrements')
            ->willReturn($qtyInc);

        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('isSaleable')->willReturn($isSaleable);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId', '__wakeup']);
        $store->expects($this->any())->method('getWebsiteId')->willReturn(0);
        $product->expects($this->any())->method('getStore')->willReturn($store);

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->willReturn($product);

        $this->assertSame($result, $this->block->getProductQtyIncrements());
        // test lazy load
        $this->assertSame($result, $this->block->getProductQtyIncrements());
    }

    /**
     * @return array
     */
    public static function getProductQtyIncrementsDataProvider()
    {
        return [
            [1, 100, true, 100],
            [1, 100, false, false],
        ];
    }
}
