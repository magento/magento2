<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Indexer;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\GetSourceItemId;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceItemIndexerTest extends TestCase
{
    /**
     * @var SourceItemIndexer
     */
    private $sourceItemIndexer;

    /**
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQuantityInStock;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    /**
     * @var GetSourceItemId
     */
    private $getSourceItemId;

    protected function setUp()
    {
        $this->sourceItemIndexer = Bootstrap::getObjectManager()->get(IndexerInterface::class);

        $this->getProductQuantityInStock = Bootstrap::getObjectManager()
            ->get(GetProductQuantityInStockInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);

        $this->getSourceItemId = Bootstrap::getObjectManager()->get(GetSourceItemId::class);
    }

    /**
     * We broke transaction during indexation so we need to clean db state manually
     */
    protected function tearDown()
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
        $this->sourceItemIndexer->executeRow($this->getSourceItemId->execute('SKU-1', 'eu-1'));

        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 30));
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
        $this->sourceItemIndexer->executeList([
            $this->getSourceItemId->execute('SKU-1', 'eu-1'),
            $this->getSourceItemId->execute('SKU-2', 'us-1'),
        ]);

        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 30));

        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 20));
        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 30));
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
        $this->sourceItemIndexer->executeFull();

        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 30));

        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 20));
        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 30));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testStockItemsHasZeroQuantityIfSourceItemsAreOutOfStock()
    {
        $this->sourceItemIndexer->executeFull();

        self::assertEquals(0, $this->getProductQuantityInStock->execute('SKU-3', 10));
    }
}
