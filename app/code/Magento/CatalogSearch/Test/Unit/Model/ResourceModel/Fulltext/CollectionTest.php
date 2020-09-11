<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Fulltext;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Fulltext Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Search\Api\SearchInterface|MockObject
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
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $model;

    /**
     * @var \Magento\Framework\Api\Filter
     */
    private $filter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManager = $this->getStoreManager();
        $this->universalFactory = $this->getUniversalFactory();
        $this->scopeConfig = $this->getScopeConfig();
        $this->criteriaBuilder = $this->getCriteriaBuilder();
        $this->filterBuilder = $this->getFilterBuilder();

        $productLimitationMock = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMockBuilder(ProductLimitationFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);

        $searchCriteriaResolver = $this->getMockBuilder(SearchCriteriaResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMockForAbstractClass();
        $searchCriteriaResolverFactory = $this->getMockBuilder(SearchCriteriaResolverFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $searchCriteriaResolverFactory->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteriaResolver);

        $this->searchResultApplierFactory = $this->getMockBuilder(SearchResultApplierFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $totalRecordsResolver = $this->getMockBuilder(TotalRecordsResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMockForAbstractClass();
        $totalRecordsResolverFactory = $this->getMockBuilder(TotalRecordsResolverFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $totalRecordsResolverFactory->expects($this->any())
            ->method('create')
            ->willReturn($totalRecordsResolver);

        $this->model = $this->objectManager->getObject(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class,
            [
                'storeManager' => $this->storeManager,
                'universalFactory' => $this->universalFactory,
                'scopeConfig' => $this->scopeConfig,
                'productLimitationFactory' => $productLimitationFactoryMock,
                'searchCriteriaResolverFactory' => $searchCriteriaResolverFactory,
                'searchResultApplierFactory' => $this->searchResultApplierFactory,
                'totalRecordsResolverFactory' => $totalRecordsResolverFactory,
            ]
        );

        $this->search = $this->getMockBuilder(\Magento\Search\Api\SearchInterface::class)
            ->setMethods(['search'])
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
        $reflectionProperty = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }

    /**
     * Test to Return field faceted data from faceted search result
     */
    public function testGetFacetedDataWithEmptyAggregations()
    {
        $pageSize = 10;

        $searchResult = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultInterface::class)
            ->getMockForAbstractClass();
        $this->search->expects($this->once())
            ->method('search')
            ->willReturn($searchResult);

        $searchResultApplier = $this->getMockBuilder(SearchResultApplierInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['apply'])
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
        $scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $scopeConfig;
    }

    /**
     * @return MockObject
     */
    protected function getCriteriaBuilder()
    {
        $criteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create', 'setRequestName'])
            ->disableOriginalConstructor()
            ->getMock();

        return $criteriaBuilder;
    }

    /**
     * @return MockObject
     */
    protected function getFilterBuilder()
    {
        $filterBuilder = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);

        return $filterBuilder;
    }

    /**
     * @param MockObject $filterBuilder
     * @param array $filters
     * @return MockObject
     */
    protected function addFiltersToFilterBuilder(MockObject $filterBuilder, array $filters)
    {
        $i = 1;
        foreach ($filters as $field => $value) {
            $filterBuilder->expects($this->at($i++))
                ->method('setField')
                ->with($field)
                ->willReturnSelf();
            $filterBuilder->expects($this->at($i++))
                ->method('setValue')
                ->with($value)
                ->willReturnSelf();
        }

        return $filterBuilder;
    }

    /**
     * @return MockObject
     */
    protected function createFilter()
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $filter;
    }

    /**
     * Get Mocks for StoreManager so Collection can be used.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getStoreManager()
    {
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore'])
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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getUniversalFactory()
    {
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['select'])
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($select);

        $entity = $this->getMockBuilder(\Magento\Eav\Model\Entity\AbstractEntity::class)
            ->setMethods(['getConnection', 'getTable', 'getDefaultAttributes', 'getEntityTable'])
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

        $universalFactory = $this->getMockBuilder(\Magento\Framework\Validator\UniversalFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory->expects($this->once())
            ->method('create')
            ->willReturn($entity);

        return $universalFactory;
    }
}
