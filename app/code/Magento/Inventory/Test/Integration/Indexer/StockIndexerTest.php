<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\Stock\StockIndexer;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class StockIndexerTest extends TestCase
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQuantityInStock;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(StockIndexer::INDEXER_ID);

        $this->getProductQuantityInStock = Bootstrap::getObjectManager()
            ->create(GetProductQuantityInStockInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->create(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->removeIndexData->execute([10, 20, 30]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexRow()
    {
        $this->indexer->reindexRow(10);

        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 10));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexList()
    {
        $this->indexer->reindexList([10, 20]);

        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 10));
        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 20));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexAll()
    {
        $this->indexer->reindexAll();

        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 30));

        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 20));
        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 30));
    }
}
