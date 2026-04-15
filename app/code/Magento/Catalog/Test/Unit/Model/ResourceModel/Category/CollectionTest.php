<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category;

use Magento\Framework\DB\Ddl\Table;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category as CategoryEntity;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var EntityFactory|MockObject
     */
    private $entityFactory;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    private $fetchStrategy;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var EavEntityFactory|MockObject
     */
    private $eavEntityFactory;

    /**
     * @var Helper|MockObject
     */
    private $resourceHelper;

    /**
     * @var UniversalFactory|MockObject
     */
    private $universalFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Visibility|MockObject
     */
    private $catalogProductVisibility;

    /**
     * @var CategoryEntity|MockObject
     */
    private $categoryEntity;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->entityFactory = $this->createMock(EntityFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fetchStrategy = $this->createMock(FetchStrategyInterface::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->eavConfig = $this->createMock(Config::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->eavEntityFactory = $this->createMock(EavEntityFactory::class);
        $this->resourceHelper = $this->createMock(Helper::class);
        $this->universalFactory = $this->createMock(UniversalFactory::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->catalogProductVisibility = $this->createMock(Visibility::class);

        $this->categoryEntity = $this->createMock(CategoryEntity::class);
        $this->universalFactory->method('create')->willReturn($this->categoryEntity);
        $this->categoryEntity->method('getConnection')->willReturn($this->connection);
        $this->categoryEntity->method('getDefaultAttributes')->willReturn([]);

        $this->select = $this->createMock(Select::class);
        $this->connection->method('select')->willReturn($this->select);

        $this->store = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStore')->willReturn($this->store);

        $this->collection = new Collection(
            $this->entityFactory,
            $this->logger,
            $this->fetchStrategy,
            $this->eventManager,
            $this->eavConfig,
            $this->resource,
            $this->eavEntityFactory,
            $this->resourceHelper,
            $this->universalFactory,
            $this->storeManager,
            $this->connection,
            $this->scopeConfig,
            $this->catalogProductVisibility
        );
    }

    public function testLoadProductCount() : void
    {
        $this->select->expects($this->exactly(1))
            ->method('from')
            ->willReturnSelf();
        $this->select->expects($this->exactly(1))
            ->method('where')
            ->willReturnSelf();
        $this->connection->expects($this->exactly(1))
            ->method('fetchPairs')
            ->with($this->select)
            ->willReturn([]);
        $this->collection->loadProductCount([]);
    }

    public function testLoadProductCountWithAnchors()
    {
        $websiteId = 1;
        $storeId = 1;
        $items = [];
        $categoryIds = range(1, 10);
        foreach ($categoryIds as $id) {
            $category = $this->getMockBuilder(Category::class)
                ->addMethods(['getIsAnchor'])
                ->onlyMethods(['getId', 'setProductCount'])
                ->disableOriginalConstructor()
                ->getMock();
            $category->method('getId')->willReturn($id);
            $category->method('getIsAnchor')->willReturn(true);
            $category
                ->expects($this->once())
                ->method('setProductCount')->with(5);
            $items[$id] = $category;
        }

        $store = $this->createMock(Store::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);

        $indexedIds = array_slice($categoryIds, 0, 5);
        $firstCounts = array_fill_keys($indexedIds, 5);

        $missingIds = array_diff($categoryIds, $indexedIds);
        $fallbackCounts = array_fill_keys($missingIds, 5);

        $this->connection->method('fetchPairs')
            ->willReturnOnConsecutiveCalls($firstCounts, $fallbackCounts);

        $tableMock = $this->createMock(Table::class);
        $tableMock->method('addColumn')->willReturnSelf();
        $tableMock->method('addIndex')->willReturnSelf();

        $this->connection->method('newTable')->willReturn($tableMock);
        $this->connection->expects($this->once())->method('createTemporaryTable')->with($tableMock);
        $this->connection->expects($this->once())->method('dropTemporaryTable');

        $this->connection->method('insertFromSelect')->willReturn('SQL');
        $this->connection->method('query')->with('SQL');

        $expectedData = [];
        foreach ($missingIds as $id) {
            $expectedData[] = ['category_id' => $id, 'descendant_id' => $id];
        }
        $this->connection->expects($this->once())->method('insertMultiple')
            ->with($this->stringContains('temp_category_descendants_'), $expectedData);

        $this->connection->method('select')->willReturn($this->select);
        $this->select->method('from')->willReturnSelf();
        $this->select->method('joinInner')->willReturnSelf();
        $this->select->method('joinLeft')->willReturnSelf();
        $this->select->method('join')->willReturnSelf();
        $this->select->method('where')->willReturnSelf();
        $this->select->method('group')->willReturnSelf();

        $this->collection->setProductStoreId($storeId);
        $this->collection->loadProductCount($items, false, true);
    }
}
