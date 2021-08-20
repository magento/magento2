<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryFactory = $this->createMock(CategoryFactory::class);
        $this->categoryResourceModel = $this->createMock(Category::class);
        $this->model = new CategoryFilter(
            $this->categoryFactory,
            $this->categoryResourceModel
        );
    }

    /**
     * Test that category filter works correctly wity condition type "eq"
     */
    public function testApplyWithConditionTypeEq(): void
    {
        $filter = new Filter();
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $collection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
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
        $collection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
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
    public function applyWithOtherSupportedConditionTypesDataProvider(): array
    {
        return [['neq'], ['in'], ['nin'],];
    }

    /**
     * @param string $condition
     * @dataProvider applyWithUnsupportedConditionTypesDataProvider
     */
    public function testApplyWithUnsupportedConditionTypes(string $condition): void
    {
        $filter = new Filter();
        $collection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
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
    public function applyWithUnsupportedConditionTypesDataProvider(): array
    {
        return [['gteq'], ['lteq'], ['gt'], ['lt'], ['like'], ['nlike']];
    }
}
