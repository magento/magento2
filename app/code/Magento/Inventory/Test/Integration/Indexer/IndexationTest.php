<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Indexer\Model\Indexer;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\SourceIndexerInterface;
use Magento\Inventory\Indexer\SourceItemIndexerInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

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
class IndexationTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var StockItemIndexerInterface
     */
    private $stockItemIndexer;

    /**
     * @var SourceItemIndexerInterface
     */
    private $sourceItemIndexer;

    /**
     * @var SourceIndexerInterface
     */
    private $sourceIndexer;

    /**
     * @var Checker
     */
    private $indexerChecker;

    protected function setUp()
    {
        $this->stockItemIndexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->stockItemIndexer->load(StockItemIndexerInterface::INDEXER_ID);

        $this->sourceItemIndexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->sourceItemIndexer->load(SourceItemIndexerInterface::INDEXER_ID);

        $this->sourceIndexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->sourceIndexer->load(SourceIndexerInterface::INDEXER_ID);

        $this->indexerChecker = Bootstrap::getObjectManager()->create(Checker::class);
    }

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
    public function testStockItemIndexerReindex()
    {
        $this->stockItemIndexer->reindexRow(10);

        self::assertEquals(8.5, $this->indexerChecker->execute(10, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(20, 'SKU-1'));

        $this->stockItemIndexer->reindexList([10, 30]);
        self::assertEquals(8.5, $this->indexerChecker->execute(30, 'SKU-1'));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_index_table_creator.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testSourceItemIndexerReindex()
    {
        $this->sourceItemIndexer->reindexRow(1);

        self::assertEquals(8.5, $this->indexerChecker->execute(10, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(20, 'SKU-1'));
        self::assertEquals(8.5, $this->indexerChecker->execute(30, 'SKU-1'));

        // set Source-30::SKU-1 to 'In Stock'
        /** @var SourceItemInterfaceFactory $sourceItemFactory */
        $sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
        /** @var DataObjectHelper $dataObjectHelper */
        $dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
        /** @var  SourceItemsSaveInterface $sourceItemsSave */
        $sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $sourceItem = $sourceItemFactory->create();
        $sourceItemData = [
            SourceItemInterface::SOURCE_ID => 30, // EU-source-3
            SourceItemInterface::SKU => 'SKU-1',
            SourceItemInterface::QUANTITY => 10,
            SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
        ];
        $dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
        $sourceItemsSave->execute([$sourceItem]);

        $this->sourceItemIndexer->reindexList([1, 5]);
        self::assertEquals(18.5, $this->indexerChecker->execute(10, 'SKU-1'));
        self::assertEquals(5, $this->indexerChecker->execute(20, 'SKU-2'));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testSourceIndexerReindex()
    {
        $this->sourceIndexer->reindexList([10, 20, 30, 40, 50]);

        self::assertEquals(8.5, $this->indexerChecker->execute(10, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(20, 'SKU-1'));
        self::assertEquals(5, $this->indexerChecker->execute(20, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(30, 'SKU-2'));
        self::assertEquals(8.5, $this->indexerChecker->execute(30, 'SKU-1'));

        // Source-40 enable
        $sourceData = [
                SourceInterface::SOURCE_ID => 40,
                SourceInterface::NAME => 'EU-source-disabled',
                SourceInterface::ENABLED => true,
                SourceInterface::PRIORITY => 10,
                SourceInterface::POSTCODE => 'postcode',
                SourceInterface::COUNTRY_ID => 'DE',
        ];
        /** @var SourceInterfaceFactory $sourceFactory */
        $sourceFactory = Bootstrap::getObjectManager()->get(SourceInterfaceFactory::class);
        /** @var DataObjectHelper $dataObjectHelper */
        $dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
        /** @var SourceRepositoryInterface $sourceRepository */
        $sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);

        /** @var SourceInterface $source */
        $source = $sourceFactory->create();
        $dataObjectHelper->populateWithArray($source, $sourceData, SourceInterface::class);
        $sourceRepository->save($source);

        $this->sourceIndexer->reindexRow(40);
        self::assertEquals(18.5, $this->indexerChecker->execute(10, 'SKU-1'));
        self::assertEquals(18.5, $this->indexerChecker->execute(30, 'SKU-1'));
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
        $this->stockItemIndexer->reindexAll();

        self::assertEquals(8.5, $this->indexerChecker->execute(10, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(20, 'SKU-1'));
        self::assertEquals(8.5, $this->indexerChecker->execute(30, 'SKU-1'));

        self::assertEquals(0, $this->indexerChecker->execute(10, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(20, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(30, 'SKU-2'));
        self::assertEquals(0, $this->indexerChecker->execute(10, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(20, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(30, 'SKU-2'));

        /** @var UnassignSourceFromStockInterface $assignSourcesToStock */
        $unassignSourcesToStock = Bootstrap::getObjectManager()->get(UnassignSourceFromStockInterface::class);
        $unassignSourcesToStock->execute(20, 30);

        $this->stockItemIndexer->reindexAll();

        /**
         * Asserts after unassign action.
         */
        self::assertEquals(5.5, $this->indexerChecker->execute(30, 'SKU-1'));
    }
}
