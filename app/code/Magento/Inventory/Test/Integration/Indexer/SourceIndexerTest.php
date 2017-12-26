<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\Source\SourceIndexer;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceIndexerTest extends TestCase
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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->get(IndexerInterface::class);
        $this->indexer->load(SourceIndexer::INDEXER_ID);

        $this->getProductQuantityInStock = Bootstrap::getObjectManager()
            ->get(GetProductQuantityInStockInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
        
        $this->sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);

        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
    }

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
        $source = $this->sourceRepository->get('eu-1');
        $this->indexer->reindexRow($source->getData(SourceResourceModel::SOURCE_ID_FIELD));

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
        $sourceIds = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceInterface::CODE, ['eu-1', 'us-1'], 'in')
            ->create();
        $sourceList = $this->sourceRepository->getList($searchCriteria);
        foreach ($sourceList->getItems() as $source) {
            $sourceIds[] = $source->getData(SourceResourceModel::SOURCE_ID_FIELD);
        }

        $this->indexer->reindexList($sourceIds);

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
}
