<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CategoryTest
 *
 * @package Magento\Catalog\Model\Rss
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Rss\Category
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryLayer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visibility;

    protected function setUp()
    {
        $this->categoryLayer = $this->getMock(
            'Magento\Catalog\Model\Layer\Category',
            ['setStore', '__wakeup'],
            [],
            '',
            false
        );
        $this->collectionFactory = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->visibility = $this->getMock(
            'Magento\Catalog\Model\Product\Visibility',
            [
                'getVisibleInCatalogIds',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Resolver')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($this->categoryLayer));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        /** @var \Magento\Catalog\Model\Rss\Category model */
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Rss\Category',
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
            ->will($this->returnValue($visibleInCatalogIds));
        $products = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Collection',
            [
                'setStoreId',
                'addAttributeToSort',
                'setVisibility',
                'setCurPage',
                'setPageSize',
                'addCountToCategories',
            ],
            [],
            '',
            false
        );
        $resourceCollection = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection',
            [
                'addAttributeToSelect',
                'addAttributeToFilter',
                'addIdFilter',
                'load'
            ],
            [],
            '',
            false
        );
        $resourceCollection->expects($this->exactly(3))
            ->method('addAttributeToSelect')
            ->will($this->returnSelf());
        $resourceCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->will($this->returnSelf());
        $resourceCollection->expects($this->once())
            ->method('addIdFilter')
            ->with($categoryChildren)
            ->will($this->returnSelf());
        $resourceCollection->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $products->expects($this->once())
            ->method('addCountToCategories')
            ->with($resourceCollection);
        $products->expects($this->once())
            ->method('addAttributeToSort')
            ->with('updated_at', 'desc')
            ->will($this->returnSelf());
        $products->expects($this->once())
            ->method('setVisibility')
            ->with($visibleInCatalogIds)
            ->will($this->returnSelf());
        $products->expects($this->once())
            ->method('setCurPage')
            ->with(1)
            ->will($this->returnSelf());
        $products->expects($this->once())
            ->method('setPageSize')
            ->with(50)
            ->will($this->returnSelf());
        $products->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($products));
        $category = $this->getMock(
            'Magento\Catalog\Model\Category',
            [
                'getResourceCollection',
                'getChildren',
                'getProductCollection',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $category->expects($this->once())
            ->method('getResourceCollection')
            ->will($this->returnValue($resourceCollection));
        $category->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue($categoryChildren));
        $category->expects($this->once())
            ->method('getProductCollection')
            ->will($this->returnValue($products));
        $layer = $this->getMock(
            'Magento\Catalog\Model\Layer',
            [
                'setCurrentCategory',
                'prepareProductCollection',
                'getProductCollection',
                '__wakeup',
            ],
            [],
            '',
            false
        );
        $layer->expects($this->once())
            ->method('setCurrentCategory')
            ->with($category)
            ->will($this->returnSelf());
        $layer->expects($this->once())
            ->method('getProductCollection')
            ->will($this->returnValue($products));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Resolver')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($layer));

        $this->categoryLayer->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->will($this->returnValue($layer));
        $this->assertEquals($products, $this->model->getProductCollection($category, $storeId));
    }
}
