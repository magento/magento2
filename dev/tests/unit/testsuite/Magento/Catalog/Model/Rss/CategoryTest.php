<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Rss;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CategoryTest
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
    protected $category;

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
        $this->category = $this->getMock(
            'Magento\Catalog\Model\Layer\Category',
            ['setStore', '__wakeup'],
            [],
            '',
            false
        );
        $this->collectionFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->visibility = $this->getMock(
            'Magento\Catalog\Model\Product\Visibility',
            ['getVisibleInCatalogIds',
                '__wakeup'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        /** @var \Magento\Catalog\Model\Rss\Category model */
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Rss\Category',
            [
                'catalogLayer' => $this->category,
                'collectionFactory' => $this->collectionFactory,
                'visibility' => $this->visibility
            ]
        );
    }

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
            'Magento\Catalog\Model\Resource\Product\Collection',
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
            'Magento\Catalog\Model\Resource\Collection\AbstractCollection',
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
        $resourceCollection->expects($this->exactly(3))->method('addAttributeToSelect')->will($this->returnSelf());
        $resourceCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $resourceCollection->expects($this->once())
            ->method('addIdFilter')
            ->with($categoryChildren)
            ->will($this->returnSelf());
        $resourceCollection->expects($this->once())->method('load')->will($this->returnSelf());
        $products->expects($this->once())->method('addCountToCategories')->with($resourceCollection);
        $products->expects($this->once())
            ->method('addAttributeToSort')
            ->with('updated_at', 'desc')
            ->will($this->returnSelf());
        $products->expects($this->once())
            ->method('setVisibility')
            ->with($visibleInCatalogIds)
            ->will($this->returnSelf());
        $products->expects($this->once())->method('setCurPage')->with(1)->will($this->returnSelf());
        $products->expects($this->once())->method('setPageSize')->with(50)->will($this->returnSelf());
        $products->expects($this->once())->method('setStoreId')->with($storeId);
        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($products));
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
        $category->expects($this->once())->method('getChildren')->will($this->returnValue($categoryChildren));
        $category->expects($this->once())->method('getProductCollection')->will($this->returnValue($products));
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
        $layer->expects($this->once())->method('setCurrentCategory')->with($category)->will($this->returnSelf());
        $layer->expects($this->once())->method('getProductCollection')->will($this->returnValue($products));
        $this->category->expects($this->once())->method('setStore')->with($storeId)->will($this->returnValue($layer));
        $this->assertEquals($products, $this->model->getProductCollection($category, $storeId));
    }
}
