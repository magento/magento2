<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Fulltext;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use Magento\CatalogUrlRewrite\Model\Storage\DbStorage;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * Test class for Fulltext Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SearchInterface|MockObject
     */
    private $search;

    /**
     * @var MockObject
     */
    private $criteriaBuilder;

    /**
     * @var MockObject
     */
    private $storeManager;

    /**
     * @var MockObject
     */
    private $universalFactory;

    /**
     * @var MockObject
     */
    private $scopeConfig;

    /**
     * @var MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchResultApplierFactory|MockObject
     */
    private $searchResultApplierFactory;

    /**
     * @var Collection
     */
    private $model;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManager = $this->getStoreManager();
        $this->universalFactory = $this->getUniversalFactory();
        $this->scopeConfig = $this->getScopeConfig();
        $this->criteriaBuilder = $this->getCriteriaBuilder();
        $this->filterBuilder = $this->getFilterBuilder();

        $objects = [
            [
                TableMaintainer::class,
                $this->createMock(TableMaintainer::class)
            ],
            [
                PriceTableResolver::class,
                $this->createMock(PriceTableResolver::class)
            ],
            [
                DimensionFactory::class,
                $this->createMock(DimensionFactory::class)
            ],
            [
                Category::class,
                $this->createMock(Category::class)
            ],
            [
                DbStorage::class,
                $this->createMock(DbStorage::class)
            ],
            [
            GalleryReadHandler::class,
                $this->createMock(GalleryReadHandler::class)
            ],
            [
                Gallery::class,
                $this->createMock(Gallery::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $productLimitationMock = $this->createMock(ProductLimitation::class);
        $productLimitationFactoryMock = $this->getMockBuilder(ProductLimitationFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);

        $searchCriteriaResolver = $this->getMockBuilder(SearchCriteriaResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolve'])
            ->getMockForAbstractClass();
        $searchCriteriaResolverFactory = $this->getMockBuilder(SearchCriteriaResolverFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $searchCriteriaResolverFactory->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteriaResolver);

        $this->searchResultApplierFactory = $this->getMockBuilder(SearchResultApplierFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $totalRecordsResolver = $this->getMockBuilder(TotalRecordsResolverInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolve'])
            ->getMockForAbstractClass();
        $totalRecordsResolverFactory = $this->getMockBuilder(TotalRecordsResolverFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $totalRecordsResolverFactory->expects($this->any())
            ->method('create')
            ->willReturn($totalRecordsResolver);

        $this->model = $this->objectManager->getObject(
            Collection::class,
            [
                'storeManager' => $this->storeManager,
                'universalFactory' => $this->universalFactory,
                'scopeConfig' => $this->scopeConfig,
                'productLimitationFactory' => $productLimitationFactoryMock,
                'searchCriteriaResolverFactory' => $searchCriteriaResolverFactory,
                'searchResultApplierFactory' => $this->searchResultApplierFactory,
                'totalRecordsResolverFactory' => $totalRecordsResolverFactory
            ]
        );

        $this->search = $this->getMockBuilder(SearchInterface::class)
            ->onlyMethods(['search'])
            ->getMockForAbstractClass();
        $this->model->setSearchCriteriaBuilder($this->criteriaBuilder);
        $this->model->setSearch($this->search);
        $this->model->setFilterBuilder($this->filterBuilder);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $reflectionProperty = new ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);
    }

    /**
     * Test to Return field faceted data from faceted search result.
     *
     * @return void
     */
    public function testGetFacetedDataWithEmptyAggregations(): void
    {
        $pageSize = 10;

        $searchResult = $this->getMockBuilder(SearchResultInterface::class)
            ->getMockForAbstractClass();
        $this->search->expects($this->once())
            ->method('search')
            ->willReturn($searchResult);

        $searchResultApplier = $this->getMockBuilder(SearchResultApplierInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['apply'])
            ->getMockForAbstractClass();
        $this->searchResultApplierFactory->expects($this->any())
            ->method('create')
            ->willReturn($searchResultApplier);

        $this->model->setPageSize($pageSize);
        $this->model->setCurPage(0);

        $this->searchResultApplierFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'collection' => $this->model,
                    'searchResult' => $searchResult,
                    'orders' => [],
                    'size' => $pageSize,
                    'currentPage' => 0,
                ]
            )
            ->willReturn($searchResultApplier);

        $this->model->getFacetedData('field');
    }

    /**
     * Test to Apply attribute filter to facet collection
     */
    public function testAddFieldToFilter()
    {
        $this->filter = $this->createFilter();
        $this->criteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with($this->filter);
        $this->filterBuilder->expects($this->once())->method('create')->willReturn($this->filter);
        $this->model->addFieldToFilter('someMultiselectValue', [3, 5, 8]);
    }

    /**
     * @return MockObject
     */
    protected function getScopeConfig()
    {
        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $scopeConfig;
    }

    /**
     * @return MockObject
     */
    protected function getCriteriaBuilder(): MockObject
    {
        $criteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->addMethods(['setRequestName'])
            ->onlyMethods(['addFilter','create'])
            ->disableOriginalConstructor()
            ->getMock();

        return $criteriaBuilder;
    }

    /**
     * @return MockObject
     */
    protected function getFilterBuilder(): MockObject
    {
        $filterBuilder = $this->createMock(FilterBuilder::class);

        return $filterBuilder;
    }

    /**
     * @param MockObject $filterBuilder
     * @param array $filters
     *
     * @return MockObject
     */
    protected function addFiltersToFilterBuilder(MockObject $filterBuilder, array $filters): MockObject
    {
        $fields = $values = [];

        foreach ($filters as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }

        $filterBuilder->method('setField')
            ->with(...$fields)
            ->willReturnSelf();
        $filterBuilder->method('setValue')
            ->with(...$values)
            ->willReturnSelf();

        return $filterBuilder;
    }

    /**
     * @return MockObject
     */
    protected function createFilter(): MockObject
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $filter;
    }

    /**
     * Get Mocks for StoreManager so Collection can be used.
     *
     * @return MockObject
     */
    private function getStoreManager(): MockObject
    {
        $store = $this->getMockBuilder(Store::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->onlyMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        return $storeManager;
    }

    /**
     * Get mock for UniversalFactory so Collection can be used.
     *
     * @return MockObject
     */
    private function getUniversalFactory(): MockObject
    {
        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['select'])
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($select);

        $entity = $this->getMockBuilder(AbstractEntity::class)
            ->onlyMethods(['getConnection', 'getTable', 'getDefaultAttributes', 'getEntityTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);
        $entity->expects($this->exactly(2))
            ->method('getTable')
            ->willReturnArgument(0);
        $entity->expects($this->once())
            ->method('getDefaultAttributes')
            ->willReturn(['attr1', 'attr2']);
        $entity->expects($this->once())
            ->method('getEntityTable')
            ->willReturn('table');

        $universalFactory = $this->getMockBuilder(UniversalFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory->expects($this->once())
            ->method('create')
            ->willReturn($entity);

        return $universalFactory;
    }
}
