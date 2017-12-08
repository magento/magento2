<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\SourceItem\SourceItemIndexer;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class SourceItemIndexerTest extends TestCase
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->get(IndexerInterface::class);
        $this->indexer->load(SourceItemIndexer::INDEXER_ID);

        $this->getProductQuantityInStock = Bootstrap::getObjectManager()
            ->get(GetProductQuantityInStockInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
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
        $stockItemId = $this->getInventorySourceItemId('SKU-1', 10);
        $this->indexer->reindexRow($stockItemId);

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
        $stockItemIdSku1 = $this->getInventorySourceItemId('SKU-1', 10);
        $stockItemIdSku2 = $this->getInventorySourceItemId('SKU-2', 50);
        $this->indexer->reindexList([$stockItemIdSku1, $stockItemIdSku2]);

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
        $this->indexer->reindexAll();

        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getProductQuantityInStock->execute('SKU-1', 30));

        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 20));
        self::assertEquals(5, $this->getProductQuantityInStock->execute('SKU-2', 30));
    }

    /**
     * @param string $sku
     * @param int $sourceId
     *
     * @return int
     */
    private function getInventorySourceItemId(string $sku, int $sourceId): int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $connection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM),
            [SourceItem::ID_FIELD_NAME]
        )->where(SourceItemInterface::SKU . ' = ?', $sku)->where(
            SourceItemInterface::SOURCE_ID . ' = ?',
            $sourceId
        );

        return (int)$connection->fetchOne($select);
    }
}
