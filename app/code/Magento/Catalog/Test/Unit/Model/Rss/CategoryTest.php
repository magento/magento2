<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Rss;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Rss\Category;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @var Category
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Category|MockObject
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
        $this->categoryLayer = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Category::class)
            ->addMethods(['setStore'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $layerResolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
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
        $resourceCollection = $this->getMockBuilder(AbstractCollection::class)
            ->addMethods(['addIdFilter'])
            ->onlyMethods(['addAttributeToSelect', 'addAttributeToFilter', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
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
            \Magento\Catalog\Model\Category::class,
            [
                'getResourceCollection',
                'getChildren',
                'getProductCollection'
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
        $layer = $this->createPartialMock(
            Layer::class,
            [
                'setCurrentCategory',
                'prepareProductCollection',
                'getProductCollection',
            ]
        );
        $layer->expects($this->once())
            ->method('setCurrentCategory')
            ->with($category)->willReturnSelf();
        $layer->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($products);

        /** @var MockObject|Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($layer);

        $this->categoryLayer->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($layer);
        $this->assertEquals($products, $this->model->getProductCollection($category, $storeId));
    }
}
