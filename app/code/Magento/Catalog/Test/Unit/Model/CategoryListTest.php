<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryList;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryListTest extends TestCase
{
    /**
     * @var CategoryList
     */
    protected $model;

    /**
     * @var CollectionFactory|MockObject
     */
    private $categoryCollectionFactory;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CategorySearchResultsInterfaceFactory|MockObject
     */
    private $categorySearchResultsFactory;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->categoryCollectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->extensionAttributesJoinProcessor = $this->createMock(JoinProcessorInterface::class);
        $this->categorySearchResultsFactory = $this->createPartialMock(
            CategorySearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->collectionProcessorMock = $this->createMock(CollectionProcessorInterface::class);

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

        $categoryFirst = $this->createMock(Category::class);
        $categorySecond = $this->createMock(Category::class);
        $items = [$categoryIdFirst => $categoryFirst, $categoryIdSecond => $categorySecond];

        /** @var SearchCriteriaInterface|MockObject $searchCriteria */
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('addAttributeToSelect')->with('*');
        $collection->expects($this->once())->method('load');
        $collection->expects($this->once())->method('getItems')->willReturn($items);
        $collection->expects($this->once())->method('getSize')->willReturn($totalCount);
        $collection->expects($this->never())->method('getData');

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

        $searchResult = $this->createMock(CategorySearchResultsInterface::class);
        $searchResult->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $searchResult->expects($this->once())->method('setItems')->with($items);
        $searchResult->expects($this->once())->method('setTotalCount')->with($totalCount);

        $this->categoryRepository->expects($this->never())->method('get');

        $this->categorySearchResultsFactory->expects($this->once())->method('create')->willReturn($searchResult);
        $this->categoryCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())->method('process')->with($collection);

        $this->assertEquals($searchResult, $this->model->getList($searchCriteria));
    }
}
