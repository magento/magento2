<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Advanced;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Test\Unit\Model\ResourceModel\BaseCollectionTest;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;

/**
 * Tests Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends BaseCollectionTest
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
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $temporaryStorageFactory;

    /**
     * @var \Magento\Search\Api\SearchInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $search;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * setUp method for CollectionTest
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->eavConfig = $this->getMock(\Magento\Eav\Model\Config::class, [], [], '', false);
        $storeManager = $this->getStoreManager();
        $universalFactory = $this->getUniversalFactory();
        $this->criteriaBuilder = $this->getCriteriaBuilder();
        $this->filterBuilder = $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        $this->temporaryStorageFactory = $this->getMock(
            \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory::class,
            [],
            [],
            '',
            false
        );
        $this->search = $this->getMock(\Magento\Search\Api\SearchInterface::class, [], [], '', false);

        $productLimitationMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMockBuilder(ProductLimitationFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);

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
            ]
        );
    }

    public function testLoadWithFilterNoFilters()
    {
        $this->advancedCollection->loadWithFilter();
    }

    /**
     * Test a search using 'like' condition
     */
    public function testLike()
    {
        $attributeCode = 'description';
        $attributeCodeId = 42;
        $attribute = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavConfig->expects($this->once())->method('getAttribute')->with(Product::ENTITY, $attributeCodeId)
            ->willReturn($attribute);
        $filtersData = ['catalog_product_entity_text' => [$attributeCodeId => ['like' => 'search text']]];
        $this->filterBuilder->expects($this->once())->method('setField')->with($attributeCode)
            ->willReturn($this->filterBuilder);
        $this->filterBuilder->expects($this->once())->method('setValue')->with('search text')
            ->willReturn($this->filterBuilder);

        $filter = $this->getMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilder->expects($this->once())->method('create')->willReturn($filter);

        $criteria = $this->getMock(\Magento\Framework\Api\Search\SearchCriteria::class, [], [], '', false);
        $this->criteriaBuilder->expects($this->once())->method('create')->willReturn($criteria);
        $criteria->expects($this->once())
            ->method('setRequestName')
            ->with('advanced_search_container');

        $tempTable = $this->getMock(\Magento\Framework\DB\Ddl\Table::class, [], [], '', false);
        $temporaryStorage = $this->getMock(
            \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::class,
            [],
            [],
            '',
            false
        );
        $temporaryStorage->expects($this->once())->method('storeApiDocuments')->willReturn($tempTable);
        $this->temporaryStorageFactory->expects($this->once())->method('create')->willReturn($temporaryStorage);
        $searchResult = $this->getMock(\Magento\Framework\Api\Search\SearchResultInterface::class, [], [], '', false);
        $this->search->expects($this->once())->method('search')->willReturn($searchResult);

        // addFieldsToFilter will load filters,
        //   then loadWithFilter will trigger _renderFiltersBefore code in Advanced/Collection
        $this->assertSame(
            $this->advancedCollection,
            $this->advancedCollection->addFieldsToFilter($filtersData)->loadWithFilter()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
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
