<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Fulltext;

use Magento\CatalogSearch\Test\Unit\Model\ResourceModel\BaseCollectionTest;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends BaseCollectionTest
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $temporaryStorage;

    /**
     * @var \Magento\Search\Api\SearchInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $model;

    /**
     * @var \Magento\Framework\Api\Filter
     */
    private $filter;

    /**
     * setUp method for CollectionTest
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManager = $this->getStoreManager();
        $this->universalFactory = $this->getUniversalFactory();
        $this->scopeConfig = $this->getScopeConfig();
        $this->criteriaBuilder = $this->getCriteriaBuilder();
        $this->filterBuilder = $this->getFilterBuilder();

        $productLimitationMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMock(ProductLimitationFactory::class, ['create']);
        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);

        $this->temporaryStorage = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $temporaryStorageFactory = $this->getMockBuilder(TemporaryStorageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $temporaryStorageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->temporaryStorage);

        $this->model = $this->objectManager->getObject(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class,
            [
                'storeManager' => $this->storeManager,
                'universalFactory' => $this->universalFactory,
                'scopeConfig' => $this->scopeConfig,
                'temporaryStorageFactory' => $temporaryStorageFactory,
                'productLimitationFactory' => $productLimitationFactoryMock
            ]
        );

        $this->search = $this->getMockBuilder(\Magento\Search\Api\SearchInterface::class)
            ->setMethods(['search'])
            ->getMockForAbstractClass();
        $this->model->setSearchCriteriaBuilder($this->criteriaBuilder);
        $this->model->setSearch($this->search);
        $this->model->setFilterBuilder($this->filterBuilder);
    }

    protected function tearDown()
    {
        $reflectionProperty = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 333
     * @expectedExceptionMessage setRequestName
     */
    public function testGetFacetedDataWithException()
    {
        $criteria = $this->getMock(\Magento\Framework\Api\Search\SearchCriteria::class, [], [], '', false);
        $this->criteriaBuilder->expects($this->once())->method('create')->willReturn($criteria);
        $criteria->expects($this->once())
            ->method('setRequestName')
            ->withConsecutive(['catalog_view_container'])
            ->willThrowException(new \Exception('setRequestName', 333));
        $this->model->getFacetedData('field');
    }

    public function testGetFacetedDataWithEmptyAggregations()
    {
        $criteria = $this->getMock(\Magento\Framework\Api\Search\SearchCriteria::class, [], [], '', false);
        $this->criteriaBuilder->expects($this->once())->method('create')->willReturn($criteria);
        $criteria->expects($this->once())
            ->method('setRequestName')
            ->withConsecutive(['catalog_view_container']);
        $searchResult = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultInterface::class)
            ->getMockForAbstractClass();
        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->setMethods(['getName'])
            ->getMock();
        $this->temporaryStorage->expects($this->once())
            ->method('storeApiDocuments')
            ->willReturn($table);
        $this->search->expects($this->once())
            ->method('search')
            ->willReturn($searchResult);
        $this->model->getFacetedData('field');
    }

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
        $filterBuilder = $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        return $filterBuilder;
    }

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

    protected function createFilter()
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $filter;
    }
}
