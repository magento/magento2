<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Rss;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Category as LayerCategory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Rss\Category;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Category
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LayerCategory|MockObject
     */
    protected $categoryLayer;

    /**
     * @var MockObject
     */
    protected $collectionFactory;

    /**
     * @var Visibility|MockObject
     */
    protected $visibility;

    protected function setUp(): void
    {
        $this->categoryLayer = $this->createPartialMockWithReflection(
            LayerCategory::class,
            ['setCurrentCategory', 'prepareProductCollection', 'getProductCollection', 'getCurrentCategory', 'setStore']
        );
        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->visibility = $this->createPartialMock(
            Visibility::class,
            [
                'getVisibleInCatalogIds'
            ]
        );

        /** @var MockObject|Resolver $layerResolver */
        $layerResolver = $this->createPartialMock(Resolver::class, ['get', 'create']);
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($this->categoryLayer);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        /** @var Category model */
        $this->model = $this->objectManagerHelper->getObject(
            Category::class,
            [
                'layerResolver' => $layerResolver,
                'collectionFactory' => $this->collectionFactory,
                'visibility' => $this->visibility
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetProductCollection()
    {
        $storeId = 1;
        $categoryChildren = 'children';
        $visibleInCatalogIds = 1;

        $this->visibility
            ->expects($this->once())
            ->method('getVisibleInCatalogIds')
            ->willReturn($visibleInCatalogIds);
        $products = $this->createPartialMock(
            Collection::class,
            [
                'setStoreId',
                'addAttributeToSort',
                'setVisibility',
                'setCurPage',
                'setPageSize',
                'addCountToCategories',
            ]
        );
        $resourceCollection = $this->createPartialMockWithReflection(
            Collection::class,
            ['addAttributeToSelect', 'addAttributeToFilter', 'addIdFilter', 'load']
        );
        $resourceCollection->expects($this->exactly(3))
            ->method('addAttributeToSelect')->willReturnSelf();
        $resourceCollection->expects($this->once())
            ->method('addAttributeToFilter')->willReturnSelf();
        $resourceCollection->expects($this->once())
            ->method('addIdFilter')
            ->with($categoryChildren)->willReturnSelf();
        $resourceCollection->expects($this->once())
            ->method('load')->willReturnSelf();
        $products->expects($this->once())
            ->method('addCountToCategories')
            ->with($resourceCollection);
        $products->expects($this->once())
            ->method('addAttributeToSort')
            ->with('updated_at', 'desc')->willReturnSelf();
        $products->expects($this->once())
            ->method('setVisibility')
            ->with($visibleInCatalogIds)->willReturnSelf();
        $products->expects($this->once())
            ->method('setCurPage')
            ->with(1)->willReturnSelf();
        $products->expects($this->once())
            ->method('setPageSize')
            ->with(50)->willReturnSelf();
        $products->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($products);
        $category = $this->createPartialMock(
            CategoryModel::class,
            [
                'getResourceCollection',
                'getChildren',
                'getProductCollection',
                'getId'
            ]
        );
        $category->expects($this->once())
            ->method('getResourceCollection')
            ->willReturn($resourceCollection);
        $category->expects($this->once())
            ->method('getChildren')
            ->willReturn($categoryChildren);
        $category->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($products);
        $category->method('getId')
            ->willReturn(1);
        
        $this->categoryLayer->expects($this->once())
            ->method('setCurrentCategory')
            ->with($category)
            ->willReturn($this->categoryLayer);
        $this->categoryLayer->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($products);
        $this->categoryLayer->method('getCurrentCategory')
            ->willReturn($category);

        $this->categoryLayer->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->categoryLayer);
        $this->assertEquals($products, $this->model->getProductCollection($category, $storeId));
    }
}
