<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Category\Action;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Indexer\Product\Category\Action\Rows;
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
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                Visibility::class,
                $this->createMock(Visibility::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->workingStateProvider = $this->createMock(WorkingStateProvider::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')->willReturn($this->connection);
        $this->select = $this->createMock(Select::class);
        $this->select->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('distinct')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('joinInner')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('group')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('joinLeft')
            ->willReturnSelf();
        $this->select->expects($this->any())
            ->method('columns')
            ->willReturnSelf();
        $this->connection->method('select')->willReturn($this->select);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->queryGenerator = $this->createMock(QueryGenerator::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->cacheContext = $this->createMock(CacheContext::class);
        $this->eventManager = $this->createMock(EventManagerInterface::class);
        $this->indexerRegistry = $this->createMock(IndexerRegistry::class);
        $this->indexer = $this->createMock(IndexerInterface::class);
        $this->tableMaintainer = $this->createMock(TableMaintainer::class);

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
        $store = $this->createMock(Store::class);
        $store->method('getRootCategoryId')->willReturn($categoryId);
        $store->method('getId')->willReturn(1);

        $attribute = $this->createMock(AbstractAttribute::class);
        $this->config->method('getAttribute')->willReturn($attribute);

        $table = $this->createMock(Table::class);
        $this->connection->method('newTable')->willReturn($table);

        $metadata = $this->createMock(EntityMetadataInterface::class);
        $this->metadataPool->method('getMetadata')->willReturn($metadata);

        $this->connection->method('fetchAll')->willReturn([]);
        $this->connection->method('fetchCol')->willReturn([]);

        $this->connection->method('fetchOne')->willReturn($categoryId);
        $this->indexerRegistry
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [ProductCategoryIndexer::INDEXER_ID] => $this->indexer,
                [CategoryProductIndexer::INDEXER_ID] => $this->indexer
            });
        $this->indexer->method('getId')->willReturn(CategoryProductIndexer::INDEXER_ID);
        $this->workingStateProvider->expects($this->any())
            ->method('isWorking')
            ->with(CategoryProductIndexer::INDEXER_ID)
            ->willReturn(true);
        $this->storeManager->method('getStores')->willReturn([$store]);

        $this->connection->expects($this->once())
            ->method('delete');

        $result = $this->rowsModel->execute([1, 2, 3]);
        $this->assertInstanceOf(Rows::class, $result);
    }
}
