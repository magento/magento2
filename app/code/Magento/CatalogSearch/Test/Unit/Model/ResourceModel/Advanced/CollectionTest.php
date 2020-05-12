<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Advanced;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use Magento\CatalogSearch\Test\Unit\Model\ResourceModel\BaseCollection;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Api\SearchInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class CollectionTest extends BaseCollection
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Collection
     */
    private $advancedCollection;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $criteriaBuilder;

    /**
     * @var TemporaryStorageFactory|MockObject
     */
    private $temporaryStorageFactory;

    /**
     * @var SearchInterface|MockObject
     */
    private $search;

    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var SearchResultApplierFactory|MockObject
     */
    private $searchResultApplierFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->eavConfig = $this->createMock(Config::class);
        $storeManager = $this->getStoreManager();
        $universalFactory = $this->getUniversalFactory();
        $this->criteriaBuilder = $this->getCriteriaBuilder();
        $this->filterBuilder = $this->createMock(FilterBuilder::class);
        $this->temporaryStorageFactory = $this->createMock(
            TemporaryStorageFactory::class
        );
        $this->search = $this->getMockForAbstractClass(SearchInterface::class);

        $productLimitationMock = $this->createMock(
            ProductLimitation::class
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

        $this->advancedCollection = $this->objectManager->getObject(
            Collection::class,
            [
                'eavConfig' => $this->eavConfig,
                'storeManager' => $storeManager,
                'universalFactory' => $universalFactory,
                'searchCriteriaBuilder' => $this->criteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'temporaryStorageFactory' => $this->temporaryStorageFactory,
                'search' => $this->search,
                'productLimitationFactory' => $productLimitationFactoryMock,
                'collectionProvider' => null,
                'searchCriteriaResolverFactory' => $searchCriteriaResolverFactory,
                'searchResultApplierFactory' => $this->searchResultApplierFactory,
                'totalRecordsResolverFactory' => $totalRecordsResolverFactory
            ]
        );
    }

    /**
     * Test to Load data with filter in place
     */
    public function testLoadWithFilterNoFilters()
    {
        $this->advancedCollection->loadWithFilter();
    }

    /**
     * Test a search using 'like' condition
     */
    public function testLike()
    {
        $pageSize = 10;
        $attributeCode = 'description';
        $attributeCodeId = 42;
        $attribute = $this->createMock(Attribute::class);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavConfig->expects($this->once())->method('getAttribute')->with(Product::ENTITY, $attributeCodeId)
            ->willReturn($attribute);
        $filtersData = ['catalog_product_entity_text' => [$attributeCodeId => ['like' => 'search text']]];
        $this->filterBuilder->expects($this->once())->method('setField')->with($attributeCode)
            ->willReturn($this->filterBuilder);
        $this->filterBuilder->expects($this->once())->method('setValue')->with('search text')
            ->willReturn($this->filterBuilder);

        $filter = $this->createMock(Filter::class);
        $this->filterBuilder->expects($this->any())->method('create')->willReturn($filter);

        $searchResult = $this->getMockForAbstractClass(SearchResultInterface::class);
        $this->search->expects($this->once())->method('search')->willReturn($searchResult);

        $this->advancedCollection->setPageSize($pageSize);
        $this->advancedCollection->setCurPage(0);

        $searchResultApplier = $this->getMockForAbstractClass(SearchResultApplierInterface::class);
        $this->searchResultApplierFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'collection' => $this->advancedCollection,
                    'searchResult' => $searchResult,
                    'orders' => [],
                    'size' => $pageSize,
                    'currentPage' => 0,
                ]
            )
            ->willReturn($searchResultApplier);

        // addFieldsToFilter will load filters,
        //   then loadWithFilter will trigger _renderFiltersBefore code in Advanced/Collection
        $this->assertSame(
            $this->advancedCollection,
            $this->advancedCollection->addFieldsToFilter($filtersData)->loadWithFilter()
        );
    }

    /**
     * @return MockObject
     */
    protected function getCriteriaBuilder()
    {
        $criteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create', 'setRequestName'])
            ->disableOriginalConstructor()
            ->getMock();

        return $criteriaBuilder;
    }
}
