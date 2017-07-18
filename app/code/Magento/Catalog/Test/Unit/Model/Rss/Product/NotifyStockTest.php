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
class NotifyStockTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */

    protected $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Model\ResourceModel\StockFactory
     */
    protected $stockFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Model\ResourceModel\Stock
     */
    protected $stock;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $status;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    protected function setUp()
    {
        $this->product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->productFactory = $this->getMock(\Magento\Catalog\Model\ProductFactory::class, ['create'], [], '', false);
        $this->productFactory->expects($this->any())->method('create')->will($this->returnValue($this->product));

        $this->stock = $this->getMock(\Magento\CatalogInventory\Model\ResourceModel\Stock::class, [], [], '', false);
        $this->stockFactory = $this->getMock(
            \Magento\CatalogInventory\Model\ResourceModel\StockFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->stockFactory->expects($this->any())->method('create')->will($this->returnValue($this->stock));

        $this->status = $this->getMock(\Magento\Catalog\Model\Product\Attribute\Source\Status::class);
        $this->eventManager = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);

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
            $this->getMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, [], [], '', false);
        $this->product->expects($this->once())->method('getCollection')->will($this->returnValue($productCollection));

        $productCollection->expects($this->once())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('setOrder')->will($this->returnSelf());

        $this->eventManager->expects($this->once())->method('dispatch')->with(
            'rss_catalog_notify_stock_collection_select'
        );
        $this->stock->expects($this->once())->method('addLowStockFilter')->with($productCollection);

        $products = $this->notifyStock->getProductsCollection();
        $this->assertEquals($productCollection, $products);
    }
}
