<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Block;

/**
 * Unit test for Qtyincrements block
 */
class QtyincrementsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Block\Qtyincrements
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->stockItem = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->setMethods(['getQtyIncrements', 'getStockItem'])
            ->getMockForAbstractClass();
        $this->stockItem->expects($this->any())->method('getStockItem')->willReturn(1);
        $this->stockRegistry = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class,
            ['getStockItem'],
            '',
            false
        );
        $this->stockRegistry->expects($this->any())->method('getStockItem')->willReturn($this->stockItem);

        $this->block = $objectManager->getObject(
            \Magento\CatalogInventory\Block\Qtyincrements::class,
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
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->once())->method('getIdentities')->willReturn($productTags);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId', '__wakeup']);
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

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('isSaleable')->willReturn($isSaleable);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId', '__wakeup']);
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
    public function getProductQtyIncrementsDataProvider()
    {
        return [
            [1, 100, true, 100],
            [1, 100, false, false],
        ];
    }
}
