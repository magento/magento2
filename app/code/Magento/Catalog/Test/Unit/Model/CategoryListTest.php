<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryList;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\Data\CategorySearchResultsInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryList
     */
    protected $model;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryCollectionFactory;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CategorySearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categorySearchResultsFactory;

    /**
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepository;

    protected function setUp()
    {
        $this->categoryCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->extensionAttributesJoinProcessor = $this->getMock(JoinProcessorInterface::class);
        $this->categorySearchResultsFactory = $this->getMockBuilder(CategorySearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->categoryRepository = $this->getMock(CategoryRepositoryInterface::class);

        $this->model = (new ObjectManager($this))->getObject(
            CategoryList::class,
            [
                'categoryCollectionFactory' => $this->categoryCollectionFactory,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessor,
                'categorySearchResultsFactory' => $this->categorySearchResultsFactory,
                'categoryRepository' => $this->categoryRepository,
            ]
        );
    }

    public function testGetList()
    {
        $fieldName = 'field_1';
        $value = 'value_1';
        $conditionType = 'eq';
        $currentPage = 2;
        $pageSize = 1;
        $totalCount = 2;
        $categoryIdFirst = 1;
        $categoryIdSecond = 2;

        $categoryFirst = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
        $categorySecond = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();

        $filter = $this->getMockBuilder(Filter::class)->disableOriginalConstructor()->getMock();
        $filter->expects($this->atLeastOnce())->method('getConditionType')->willReturn($conditionType);
        $filter->expects($this->atLeastOnce())->method('getField')->willReturn($fieldName);
        $filter->expects($this->once())->method('getValue')->willReturn($value);

        $filterGroup = $this->getMockBuilder(FilterGroup::class)->disableOriginalConstructor()->getMock();
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$filter]);

        $sortOrder = $this->getMockBuilder(SortOrder::class)->disableOriginalConstructor()->getMock();
        $sortOrder->expects($this->once())->method('getField')->willReturn($fieldName);
        $sortOrder->expects($this->once())->method('getDirection')->willReturn(SortOrder::SORT_ASC);

        /** @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject $searchCriteria */
        $searchCriteria = $this->getMock(SearchCriteriaInterface::class);
        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $searchCriteria->expects($this->once())->method('getSortOrders')->willReturn([$sortOrder]);

        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with([['attribute' => $fieldName, $conditionType => $value]]);
        $collection->expects($this->once())->method('addOrder')->with($fieldName, SortOrder::SORT_ASC);
        $collection->expects($this->once())->method('setCurPage')->with($currentPage);
        $collection->expects($this->once())->method('setPageSize')->with($pageSize);
        $collection->expects($this->once())->method('getSize')->willReturn($totalCount);
        $collection->expects($this->once())->method('getAllIds')->willReturn([$categoryIdFirst, $categoryIdSecond]);

        $searchResult = $this->getMock(CategorySearchResultsInterface::class);
        $searchResult->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $searchResult->expects($this->once())->method('setItems')->with([$categoryFirst, $categorySecond]);
        $searchResult->expects($this->once())->method('setTotalCount')->with($totalCount);

        $this->categoryRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [$categoryIdFirst, $categoryFirst],
                [$categoryIdSecond, $categorySecond],
            ])
            ->willReturn($categoryFirst);

        $this->categorySearchResultsFactory->expects($this->once())->method('create')->willReturn($searchResult);
        $this->categoryCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())->method('process')->with($collection);

        $this->assertEquals($searchResult, $this->model->getList($searchCriteria));
    }
}
