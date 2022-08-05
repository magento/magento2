<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Rss\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Rss\Product\NotifyStock;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Magento\CatalogInventory\Model\ResourceModel\StockFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotifyStockTest extends TestCase
{
    /**
     * @var NotifyStock
     */
    protected $notifyStock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject
     */
    protected $productFactory;

    /**
     * @var MockObject|Product
     */
    protected $product;

    /**
     * @var MockObject|StockFactory
     */
    protected $stockFactory;

    /**
     * @var MockObject|Stock
     */
    protected $stock;

    /**
     * @var Status|MockObject
     */
    protected $status;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->productFactory->expects($this->any())->method('create')->willReturn($this->product);

        $this->stock = $this->createMock(Stock::class);
        $this->stockFactory = $this->createPartialMock(
            StockFactory::class,
            ['create']
        );
        $this->stockFactory->expects($this->any())->method('create')->willReturn($this->stock);

        $this->status = $this->createMock(Status::class);
        $this->eventManager = $this->createMock(Manager::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->notifyStock = $this->objectManagerHelper->getObject(
            NotifyStock::class,
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
        /** @var Collection $productCollection */
        $productCollection =
            $this->createMock(Collection::class);
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
