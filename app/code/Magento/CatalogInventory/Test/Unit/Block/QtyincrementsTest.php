<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Block;

/**
 * Unit test for Qtyincrements block
 */
class QtyincrementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Block\Qtyincrements
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->stockItem = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class,
            ['getQtyIncrements'],
            '',
            false
        );
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

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTags));
        $store = $this->getMock(\Magento\Store\Model\Store::class, ['getWebsiteId', '__wakeup'], [], '', false);
        $store->expects($this->any())->method('getWebsiteId')->willReturn(0);
        $product->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));
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
            ->will($this->returnValue($qtyInc));

        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $product->expects($this->once())->method('isSaleable')->will($this->returnValue($isSaleable));
        $store = $this->getMock(\Magento\Store\Model\Store::class, ['getWebsiteId', '__wakeup'], [], '', false);
        $store->expects($this->any())->method('getWebsiteId')->willReturn(0);
        $product->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

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
