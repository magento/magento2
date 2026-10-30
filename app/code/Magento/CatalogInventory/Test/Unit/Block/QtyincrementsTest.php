<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Block;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Block\Qtyincrements;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Qtyincrements block
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QtyincrementsTest extends TestCase
{
    use MockCreationTrait;

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
        $contextMock = $this->createMock(Context::class);
        
        $this->registryMock = $this->createMock(Registry::class);
        
        $this->stockItem = $this->createPartialMockWithReflection(
            Item::class,
            ['getStockItem', 'getQtyIncrements', 'setQtyIncrements']
        );
        
        // Implement stateful behavior for QtyIncrements
        $qtyIncrements = null;
        $stockItemMock = $this->stockItem;
        
        $this->stockItem->method('setQtyIncrements')->willReturnCallback(
            function ($val) use (&$qtyIncrements, $stockItemMock) {
                $qtyIncrements = $val;
                return $stockItemMock;
            }
        );
        $this->stockItem->method('getQtyIncrements')->willReturnCallback(function () use (&$qtyIncrements) {
            return $qtyIncrements;
        });
        
        $this->stockRegistry = $this->createMock(StockRegistryInterface::class);
        $this->stockRegistry->method('getStockItem')->willReturn($this->stockItem);

        $this->block = new Qtyincrements(
            $contextMock,
            $this->registryMock,
            $this->stockRegistry,
            []
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
        $store->method('getWebsiteId')->willReturn(0);
        $product->method('getStore')->willReturn($store);
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
     */
    #[DataProvider('getProductQtyIncrementsDataProvider')]
    public function testGetProductQtyIncrements($productId, $qtyInc, $isSaleable, $result)
    {
        $this->stockItem->setQtyIncrements($qtyInc);

        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('isSaleable')->willReturn($isSaleable);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId', '__wakeup']);
        $store->method('getWebsiteId')->willReturn(0);
        $product->method('getStore')->willReturn($store);

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
