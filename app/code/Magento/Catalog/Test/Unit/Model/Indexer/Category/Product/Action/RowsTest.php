<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Action;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Rows;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Indexer\Model\WorkingStateProvider;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Rows action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) to preserve compatibility with tested class
 */
class RowsTest extends TestCase
{
    /**
     * @var WorkingStateProvider|MockObject
     */
    private $workingStateProvider;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var QueryGenerator|MockObject
     */
    private $queryGenerator;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var CacheContext|MockObject
     */
    private $cacheContext;

    /**
     * @var EventManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistry;

    /**
     * @var TableMaintainer|MockObject
     */
    private $tableMaintainer;

    /**
     * @var IndexerInterface|MockObject
     */
    private $indexer;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var Rows
     */
    private $rowsModel;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->workingStateProvider = $this->getMockBuilder(WorkingStateProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->select->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('joinInner')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('joinLeft')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('columns')
            ->willReturnSelf();
        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($this->select);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryGenerator = $this->getMockBuilder(QueryGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheContext = $this->getMockBuilder(CacheContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManager = $this->getMockBuilder(EventManagerInterface::class)
            ->getMockForAbstractClass();
        $this->indexerRegistry = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexer = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $this->tableMaintainer = $this->getMockBuilder(TableMaintainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rowsModel = new Rows(
            $this->resource,
            $this->storeManager,
            $this->config,
            $this->queryGenerator,
            $this->metadataPool,
            $this->tableMaintainer,
            $this->cacheContext,
            $this->eventManager,
            $this->indexerRegistry,
            $this->workingStateProvider
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithIndexerWorking() : void
    {
        $categoryId = '1';
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getRootCategoryId')
            ->willReturn($categoryId);
        $store->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->config->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attribute);

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->any())
            ->method('newTable')
            ->willReturn($table);

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->connection->expects($this->any())
            ->method('fetchAll')
            ->willReturn([]);

        $this->connection->expects($this->any())
            ->method('fetchOne')
            ->willReturn($categoryId);
        $this->indexerRegistry
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [ProductCategoryIndexer::INDEXER_ID] => $this->indexer,
                [CategoryProductIndexer::INDEXER_ID] => $this->indexer
            });

        $this->indexer->expects($this->any())
            ->method('getId')
            ->willReturn(ProductCategoryIndexer::INDEXER_ID);
        $this->workingStateProvider->expects($this->any())
            ->method('isWorking')
            ->with(ProductCategoryIndexer::INDEXER_ID)
            ->willReturn(true);
        $this->storeManager->expects($this->any())
            ->method('getStores')
            ->willReturn([$store]);

        $this->connection->expects($this->once())
            ->method('delete');

        $result = $this->rowsModel->execute([1, 2, 3]);
        $this->assertInstanceOf(Rows::class, $result);
    }
}
