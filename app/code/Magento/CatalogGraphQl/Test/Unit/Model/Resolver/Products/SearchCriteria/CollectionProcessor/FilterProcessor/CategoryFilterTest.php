<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Collection\JoinMinimalPosition;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor\CategoryFilter;
use Magento\Framework\Api\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Category filter
 */
class CategoryFilterTest extends TestCase
{
    /**
     * @var CategoryFilter
     */
    private $model;

    /**
     * @var CategoryFactory|MockObject
     */
    private $categoryFactory;

    /**
     * @var Category|MockObject
     */
    private $categoryResourceModel;

    /**
     * @var JoinMinimalPosition
     */
    private $joinMinimalPosition;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryFactory = $this->createMock(CategoryFactory::class);
        $this->categoryResourceModel = $this->createMock(Category::class);
        $this->joinMinimalPosition = $this->createMock(JoinMinimalPosition::class);
        $this->model = new CategoryFilter(
            $this->categoryFactory,
            $this->categoryResourceModel,
            $this->joinMinimalPosition
        );
    }

    /**
     * Test that category filter works correctly with condition type "eq"
     */
    public function testApplyWithConditionTypeEq(): void
    {
        $filter = new Filter();
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $collection = $this->createMock(Collection::class);
        $filter->setConditionType('eq');
        $categoryId = 1;
        $filter->setValue($categoryId);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn($category);
        $this->categoryResourceModel->expects($this->once())
            ->method('load')
            ->with($category, $categoryId);
        $collection->expects($this->once())
            ->method('addCategoryFilter')
            ->with($category);
        $collection->expects($this->once())
            ->method('getFlag')
            ->with('search_resut_applied')
            ->willReturn(true);
        $this->model->apply($filter, $collection);
    }

    /**
     * Test that category filter works correctly with condition type "in" and single category
     */
    public function testApplyWithConditionTypeInAndSingleCategory(): void
    {
        $filter = new Filter();
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $collection = $this->createMock(Collection::class);
        $filter->setConditionType('in');
        $categoryId = 1;
        $filter->setValue($categoryId);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn($category);
        $this->categoryResourceModel->expects($this->once())
            ->method('load')
            ->with($category, $categoryId);
        $collection->expects($this->once())
            ->method('addCategoryFilter')
            ->with($category);
        $collection->expects($this->once())
            ->method('getFlag')
            ->with('search_resut_applied')
            ->willReturn(true);
        $this->model->apply($filter, $collection);
    }

    /**
     * Test that category filter works correctly with condition type "in" and multiple categories
     */
    public function testApplyWithConditionTypeInAndMultipleCategories(): void
    {
        $filter = new Filter();
        $category1 = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren'])
            ->addMethods(['getIsAnchor'])
            ->getMock();
        $category3 = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren'])
            ->addMethods(['getIsAnchor'])
            ->getMock();
        $collection = $this->createMock(Collection::class);
        $filter->setConditionType('in');
        $filter->setValue('1,3');
        $this->categoryFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($category1, $category3);
        $this->categoryResourceModel->expects($this->exactly(2))
            ->method('load')
            ->willReturnCallback(function (...$args) use ($category1, $category3) {
                static $index = 0;
                $expectedArgs = [
                    [$category1, 1],
                    [$category3, 3]
                ];
                $index++;
                if ($args === $expectedArgs[$index - 1]) {
                    return null;
                }
            });
        $collection->expects($this->never())
            ->method('addCategoryFilter');
        $collection->expects($this->once())
            ->method('addCategoriesFilter')
            ->with(['in' => [1, 2, 3]]);
        $collection->expects($this->once())
            ->method('getFlag')
            ->with('search_resut_applied')
            ->willReturn(false);
        $category1->expects($this->once())
            ->method('getIsAnchor')
            ->willReturn(true);
        $category1->expects($this->once())
            ->method('getChildren')
            ->with(true)
            ->willReturn('2');
        $category3->expects($this->once())
            ->method('getIsAnchor')
            ->willReturn(false);
        $this->joinMinimalPosition->expects($this->once())
            ->method('execute')
            ->with($collection, [1, 3]);
        $this->model->apply($filter, $collection);
    }

    /**
     * @param string $condition
     * @dataProvider applyWithOtherSupportedConditionTypesDataProvider
     */
    public function testApplyWithOtherSupportedConditionTypes(string $condition): void
    {
        $filter = new Filter();
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren'])
            ->addMethods(['getIsAnchor'])
            ->getMock();
        $collection = $this->createMock(Collection::class);
        $filter->setConditionType($condition);
        $categoryId = 1;
        $filter->setValue($categoryId);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn($category);
        $this->categoryResourceModel->expects($this->once())
            ->method('load')
            ->with($category, $categoryId);
        $collection->expects($this->never())
            ->method('addCategoryFilter');
        $collection->expects($this->once())
            ->method('addCategoriesFilter')
            ->with([$condition => [1, 2]]);
        $collection->expects($this->once())
            ->method('getFlag')
            ->with('search_resut_applied')
            ->willReturn(false);
        $category->expects($this->once())
            ->method('getIsAnchor')
            ->willReturn(true);
        $category->expects($this->once())
            ->method('getChildren')
            ->with(true)
            ->willReturn('2');
        $this->model->apply($filter, $collection);
    }

    /**
     * @return array
     */
    public static function applyWithOtherSupportedConditionTypesDataProvider(): array
    {
        return [['neq'], ['nin'],];
    }

    /**
     * @param string $condition
     * @dataProvider applyWithUnsupportedConditionTypesDataProvider
     */
    public function testApplyWithUnsupportedConditionTypes(string $condition): void
    {
        $filter = new Filter();
        $collection = $this->createMock(Collection::class);
        $filter->setConditionType($condition);
        $categoryId = 1;
        $filter->setValue($categoryId);
        $this->categoryFactory->expects($this->never())
            ->method('create');
        $this->categoryResourceModel->expects($this->never())
            ->method('load');
        $collection->expects($this->never())
            ->method('addCategoryFilter');
        $collection->expects($this->never())
            ->method('addCategoriesFilter');
        $this->model->apply($filter, $collection);
    }

    /**
     * @return array
     */
    public static function applyWithUnsupportedConditionTypesDataProvider(): array
    {
        return [['gteq'], ['lteq'], ['gt'], ['lt'], ['like'], ['nlike']];
    }
}
