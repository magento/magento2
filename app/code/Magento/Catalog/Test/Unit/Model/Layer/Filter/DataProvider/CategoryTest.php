<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
 */
class CategoryTest extends TestCase
{
    /** @var  Category|MockObject */
    private $category;

    /** @var  Store|MockObject */
    private $store;

    /** @var  Layer|MockObject */
    private $layer;

    /** @var  CategoryFactory|MockObject */
    private $categoryFactory;

    /** @var  Registry|MockObject */
    private $coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    private $target;

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp(): void
    {
        /** @var Registry $var */
        $this->coreRegistry = $var = $this->createPartialMock(Registry::class, ['register']);
        $this->category = $this->createPartialMock(Category::class, ['getId', 'setStoreId', 'load', 'getPathIds']);
        $this->categoryFactory = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->categoryFactory->method('create')->willReturn($this->category);
        $this->store = $this->createPartialMock(Store::class, ['getId']);
        $this->layer = $this->createPartialMock(Layer::class, ['getCurrentStore', 'getCurrentCategory']);
        $this->layer->method('getCurrentStore')->willReturn($this->store);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Layer\Filter\DataProvider\Category::class,
            [
                'coreRegistry' => $this->coreRegistry,
                'categoryFactory' => $this->categoryFactory,
                'layer' => $this->layer,
            ]
        );
    }

    /**
     * @return \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    public function testGetCategoryWithAppliedId()
    {
        $storeId = 1234;
        $categoryId = 4321;
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->layer->method('getCurrentCategory')->willReturn($this->category);
        $this->category->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)->willReturnSelf();
        $this->category->expects($this->once())
            ->method('load')
            ->with($categoryId)->willReturnSelf();
        $this->category->method('getId')->willReturn($categoryId);
        $this->category->method('getPathIds')->willReturn([20, 10]);
        $this->coreRegistry->expects($this->once())
            ->method('register')
            ->with(
                'current_category_filter',
                $this->category,
                true
            )->willReturnSelf();
        $this->target->setCategoryId($categoryId);
        $this->assertSame($this->category, $this->target->getCategory());
        $this->assertSame(20, $this->target->getResetValue());

        return $this->target;
    }
}
