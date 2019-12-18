<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Advanced;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use Magento\CatalogSearch\Test\Unit\Model\ResourceModel\BaseCollection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class CollectionTest extends BaseCollection
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
     */
    private $advancedCollection;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|MockObject
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory|MockObject
     */
    private $temporaryStorageFactory;

    /**
     * @var \Magento\Search\Api\SearchInterface|MockObject
     */
    private $search;

    /**
     * @var \Magento\Eav\Model\Config|MockObject
     */
    private $eavConfig;

    /**
     * @var SearchResultApplierFactory|MockObject
     */
    private $searchResultApplierFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $storeManager = $this->getStoreManager();
        $universalFactory = $this->getUniversalFactory();
        $this->criteriaBuilder = $this->getCriteriaBuilder();
        $this->filterBuilder = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->temporaryStorageFactory = $this->createMock(
            \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory::class
        );
        $this->search = $this->createMock(\Magento\Search\Api\SearchInterface::class);

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

        $this->advancedCollection = $this->objectManager->getObject(
            \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection::class,
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
        $attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavConfig->expects($this->once())->method('getAttribute')->with(Product::ENTITY, $attributeCodeId)
            ->willReturn($attribute);
        $filtersData = ['catalog_product_entity_text' => [$attributeCodeId => ['like' => 'search text']]];
        $this->filterBuilder->expects($this->once())->method('setField')->with($attributeCode)
            ->willReturn($this->filterBuilder);
        $this->filterBuilder->expects($this->once())->method('setValue')->with('search text')
            ->willReturn($this->filterBuilder);

        $filter = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilder->expects($this->any())->method('create')->willReturn($filter);

        $searchResult = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $this->search->expects($this->once())->method('search')->willReturn($searchResult);

        $this->advancedCollection->setPageSize($pageSize);
        $this->advancedCollection->setCurPage(0);

        $searchResultApplier = $this->createMock(SearchResultApplierInterface::class);
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
        $criteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create', 'setRequestName'])
            ->disableOriginalConstructor()
            ->getMock();

        return $criteriaBuilder;
    }
}
