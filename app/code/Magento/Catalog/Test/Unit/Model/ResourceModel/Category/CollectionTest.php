<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category;

use Magento\Framework\DB\Ddl\Table;
use Magento\Catalog\Model\Category;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category as CategoryEntity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    /**
     * Test that loadProductCount calls getCountFromCategoryTableBulk
     */
    public function testLoadProductCountCallsBulkMethodForLargeCategoryCount()
    {
        $websiteId = 1;
        $storeId = 1;
        $categoryCount = 401;
        $items = [];
        $categoryIds = [];
        for ($i = 1; $i <= $categoryCount; $i++) {
            $category = $this->createPartialMock(Category::class, ['getId', 'setProductCount']);
            $category->method('getId')->willReturn($i);
            $category->setData('is_anchor', true);
            $category->expects($this->once())->method('setProductCount')->with(5);
            $items[$i] = $category;
            $categoryIds[] = $i;
        }
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($storeMock);
        $this->connection->method('select')->willReturn($this->select);
        $counts = array_fill_keys($categoryIds, 5);
        $tableMock = $this->createMock(Table::class);
        $tableMock->method('addColumn')->willReturnSelf();
        $tableMock->method('addIndex')->willReturnSelf();
        $this->connection->method('newTable')
            ->with($this->stringContains('temp_category_descendants_'))
            ->willReturn($tableMock);
        $this->connection->expects($this->once())->method('createTemporaryTable')->with($tableMock);
        $this->connection->expects($this->once())->method('dropTemporaryTable')
            ->with($this->stringContains('temp_category_descendants_'));
        $this->select->method('from')->willReturnSelf();
        $this->select->expects($this->once())->method('joinInner')
            ->with(
                ['ce2' => null],
                'ce2.path LIKE CONCAT(ce.path, \'/%\')',
                []
            )->willReturnSelf();
        $this->select->method('where')->willReturnSelf();
        $this->connection->method('select')->willReturn($this->select);
        $this->connection->method('insertFromSelect')->willReturn('INSERT QUERY');
        $this->connection->method('query')->with('INSERT QUERY')->willReturnSelf();
        $this->select->method('from')->willReturnSelf();
        $this->select->method('joinLeft')->willReturnSelf();
        $this->select->method('join')->willReturnSelf();
        $this->select->method('where')->willReturnSelf();
        $this->select->method('group')->willReturnSelf();
        $this->connection->method('fetchPairs')
            ->with($this->isInstanceOf(Select::class))
            ->willReturn($counts);
        $this->collection->setProductStoreId($storeId);
        $this->collection->loadProductCount($items, false, true);
    }
}
