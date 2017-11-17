<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Preconditions:
 *
 * Products to Sources links:
 *   SKU-1 - EU-source-1(id:10) - 5.5qty
 *   SKU-1 - EU-source-2(id:20) - 3qty
 *   SKU-1 - EU-source-3(id:30) - 10qty (out of stock)
 *   SKU-1 - EU-source-4(id:40) - 10qty (disabled source)
 *
 *   SKU-2 - US-source-1(id:30) - 5qty
 *
 * Sources to Stocks links:
 *   EU-source-1(id:10) - EU-stock(id:10)
 *   EU-source-2(id:20) - EU-stock(id:10)
 *   EU-source-3(id:30) - EU-stock(id:10)
 *   EU-source-disabled(id:40) - EU-stock(id:10)
 *
 *   US-source-1(id:50) - US-stock(id:20)
 *
 *   EU-source-1(id:10) - Global-stock(id:30)
 *   EU-source-2(id:20) - Global-stock(id:30)
 *   EU-source-3(id:30) - Global-stock(id:30)
 *   EU-source-disabled(id:40) - Global-stock(id:30)
 *   US-source-1(id:50) - Global-stock(id:30)
 *
 * TODO: fixture via composer
 */
class IndexationTest extends TestCase
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQtyInStock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->get(Indexer::class);
        $this->indexer->load(StockItemIndexerInterface::INDEXER_ID);
        $this->getProductQtyInStock = Bootstrap::getObjectManager()->get(
            GetProductQuantityInStockInterface::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        /** @var IndexNameBuilder $indexNameBuilder */
        $indexNameBuilder = Bootstrap::getObjectManager()->get(IndexNameBuilder::class);
        /** @var IndexStructureInterface $indexStructure */
        $indexStructure = Bootstrap::getObjectManager()->get(IndexStructureInterface::class);

        foreach ([10, 20, 30] as $stockId) {
            $indexName = $indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();
            $indexStructure->delete($indexName, ResourceConnection::DEFAULT_CONNECTION);
        }
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
        $this->indexer->reindexRow(1);

        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 30));
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
        $this->indexer->reindexList([1, 5]);

        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 10));
        self::assertEquals(0, $this->getProductQtyInStock->execute('SKU-1', 20));
        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 30));

        self::assertEquals(0, $this->getProductQtyInStock->execute('SKU-2', 10));
        self::assertEquals(5, $this->getProductQtyInStock->execute('SKU-2', 20));
        self::assertEquals(5, $this->getProductQtyInStock->execute('SKU-2', 30));
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

        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 10));
        self::assertEquals(0, $this->getProductQtyInStock->execute('SKU-1', 20));
        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 30));

        self::assertEquals(0, $this->getProductQtyInStock->execute('SKU-2', 10));
        self::assertEquals(5, $this->getProductQtyInStock->execute('SKU-2', 20));
        self::assertEquals(5, $this->getProductQtyInStock->execute('SKU-2', 30));
    }
}
