<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryList;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
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

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

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
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            CategoryList::class,
            [
                'categoryCollectionFactory' => $this->categoryCollectionFactory,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessor,
                'categorySearchResultsFactory' => $this->categorySearchResultsFactory,
                'categoryRepository' => $this->categoryRepository,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
    }

    public function testGetList()
    {
        $totalCount = 2;
        $categoryIdFirst = 1;
        $categoryIdSecond = 2;

        $categoryFirst = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
        $categorySecond = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();

        /** @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject $searchCriteria */
        $searchCriteria = $this->getMock(SearchCriteriaInterface::class);

        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('getSize')->willReturn($totalCount);
        $collection->expects($this->once())->method('getAllIds')->willReturn([$categoryIdFirst, $categoryIdSecond]);

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

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
