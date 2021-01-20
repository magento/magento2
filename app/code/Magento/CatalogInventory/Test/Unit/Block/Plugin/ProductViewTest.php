<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Block\Plugin;

class ProductViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Block\Plugin\ProductView
     */
    protected $block;

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

        $this->stockItem = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMinSaleQty', 'getMaxSaleQty', 'getQtyIncrements'])
            ->getMock();

        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockRegistryInterface::class)
            ->getMock();

        $this->block = $objectManager->getObject(
            \Magento\CatalogInventory\Block\Plugin\ProductView::class,
            [
                'stockRegistry' => $this->stockRegistry
            ]
        );
    }

    public function testAfterGetQuantityValidators()
    {
        $result = [
            'validate-item-quantity' =>
                [
                    'minAllowed' => 0.5,
                    'maxAllowed' => 5.0,
                    'qtyIncrements' => 3.0
                ]
        ];
        $validators = [];
        $productViewBlock = $this->getMockBuilder(\Magento\Catalog\Block\Product\View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'getId', 'getStore'])
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId', '_wakeup'])
            ->getMock();

        $productViewBlock->expects($this->any())->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->once())->method('getId')->willReturn('productId');
        $productMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn('websiteId');
        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->with('productId', 'websiteId')
            ->willReturn($this->stockItem);
        $this->stockItem->expects($this->once())->method('getMinSaleQty')->willReturn(0.5);
        $this->stockItem->expects($this->any())->method('getMaxSaleQty')->willReturn(5);
        $this->stockItem->expects($this->any())->method('getQtyIncrements')->willReturn(3);

        $this->assertEquals($result, $this->block->afterGetQuantityValidators($productViewBlock, $validators));
    }
}
