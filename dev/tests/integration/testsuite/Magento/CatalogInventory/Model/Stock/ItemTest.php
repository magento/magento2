<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\Indexer\Model\Indexer\State;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Model\Stock\Item'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveWithNullQty()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Model\Product');

        $product->load(1);

        /** @var \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository */
        $stockItemRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Model\Stock\StockItemRepository');

        /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $stockItemCriteria */
        $stockItemCriteria = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Api\StockItemCriteriaInterface');

        $savedStockItem = current($stockItemRepository->getList($stockItemCriteria)->getItems());
        $savedStockItemId = $savedStockItem->getItemId();

        $savedStockItem->setQty(null);
        $savedStockItem->save();

        $savedStockItem->setQty(2);
        $savedStockItem->save();
        $this->assertEquals('2.0000', $savedStockItem->load($savedStockItemId)->getQty());

        $savedStockItem->setQty(0);
        $savedStockItem->save();
        $this->assertEquals('0.0000', $savedStockItem->load($savedStockItemId)->getQty());

        $savedStockItem->setQty(null);
        $savedStockItem->save();

        $this->assertEquals(null, $savedStockItem->load($savedStockItemId)->getQty());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testIndexerInvalidation()
    {
        /** @var \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository */
        $stockItemRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Model\Stock\StockItemRepository');

        /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $stockItemCriteria */
        $stockItemCriteria = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Api\StockItemCriteriaInterface');
        /** @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor $indexerProcessor */
        $indexerProcessor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Model\Indexer\Stock\Processor');
        $indexer = $indexerProcessor->getIndexer();
        $indexer->setScheduled(true);
        $indexer->getState()->setStatus(State::STATUS_VALID)->save();

        /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface $savedStockItem */
        $savedStockItem = current($stockItemRepository->getList($stockItemCriteria)->getItems());
        $savedStockItem->setQty(1);
        $savedStockItem->setIsInStock(false);
        $savedStockItem->save();


        $this->assertEquals('invalid', $indexerProcessor->getIndexer()->getStatus());

        $indexer->setScheduled(false);
        $indexer->getState()->setStatus(State::STATUS_VALID)->save();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     */
    public function testStockStatusChangedAuto()
    {
        /** @var \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository */
        $stockItemRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Model\Stock\StockItemRepository');

        /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $stockItemCriteria */
        $stockItemCriteria = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Api\StockItemCriteriaInterface');

        $savedStockItem = current($stockItemRepository->getList($stockItemCriteria)->getItems());

        $savedStockItem->setQty(1);
        $savedStockItem->save();

        $this->assertEquals(0, $savedStockItem->getStockStatusChangedAuto());

        $savedStockItem->setStockStatusChangedAutomaticallyFlag(1);
        $savedStockItem->save();
        $this->assertEquals(1, $savedStockItem->getStockStatusChangedAuto());
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/enable_qty_increments 1
     */
    public function testSetGetEnableQtyIncrements()
    {
        $this->assertFalse($this->_model->getEnableQtyIncrements());

        $this->_model->setUseConfigEnableQtyInc(true);
        $this->assertTrue($this->_model->getEnableQtyIncrements());
    }
}
