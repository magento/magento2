<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Rss\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NotifyStockTest
 * @package Magento\Catalog\Model\Rss\Product
 */
class NotifyStockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Rss\Product\NotifyStock
     */
    protected $notifyStock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */

    protected $productFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\CatalogInventory\Model\ResourceModel\StockFactory
     */
    protected $stockFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\CatalogInventory\Model\ResourceModel\Stock
     */
    protected $stock;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $status;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    protected function setUp(): void
    {
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productFactory = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->productFactory->expects($this->any())->method('create')->willReturn($this->product);

        $this->stock = $this->createMock(\Magento\CatalogInventory\Model\ResourceModel\Stock::class);
        $this->stockFactory = $this->createPartialMock(
            \Magento\CatalogInventory\Model\ResourceModel\StockFactory::class,
            ['create']
        );
        $this->stockFactory->expects($this->any())->method('create')->willReturn($this->stock);

        $this->status = $this->createMock(\Magento\Catalog\Model\Product\Attribute\Source\Status::class);
        $this->eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->notifyStock = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Rss\Product\NotifyStock::class,
            [
                'productFactory' => $this->productFactory,
                'stockFactory' => $this->stockFactory,
                'productStatus' => $this->status,
                'eventManager' => $this->eventManager
            ]
        );
    }

    public function testGetProductsCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection =
            $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->product->expects($this->once())->method('getCollection')->willReturn($productCollection);

        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('setOrder')->willReturnSelf();

        $this->eventManager->expects($this->once())->method('dispatch')->with(
            'rss_catalog_notify_stock_collection_select'
        );
        $this->stock->expects($this->once())->method('addLowStockFilter')->with($productCollection);

        $products = $this->notifyStock->getProductsCollection();
        $this->assertEquals($productCollection, $products);
    }
}
