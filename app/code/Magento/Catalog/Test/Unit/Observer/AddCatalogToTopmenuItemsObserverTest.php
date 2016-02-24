<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AddCatalogToTopmenuItemsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Observer\AddCatalogToTopmenuItemsObserver
     */
    protected $_observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Category
     */
    protected $_childrenCategory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $menuCategoryData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $_categoryFlatState;

    public function setUp()
    {
        $this->_catalogCategory = $this->getMock(
            '\Magento\Catalog\Helper\Category',
            ['getStoreCategories', 'getCategoryUrl'],
            [],
            '',
            false
        );

        $this->menuCategoryData = $this->getMock(
            'Magento\Catalog\Observer\MenuCategoryData',
            ['getMenuCategoryData'],
            [],
            '',
            false
        );

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getRootCategoryId', 'getFilters', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())->method('getStore')
            ->will($this->returnValue($this->store));

        $this->store->expects($this->any())->method('getRootCategoryId')
            ->will($this->returnValue(1));

        $collectionFactory = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection =  $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Category\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactory->expects($this->once())->method('create')
            ->willReturn($collection);

        $collection->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $this->_observer = (new ObjectManager($this))->getObject(
            'Magento\Catalog\Observer\AddCatalogToTopmenuItemsObserver',
            [
                'catalogCategory' => $this->_catalogCategory,
                'menuCategoryData' => $this->menuCategoryData,
                'storeManager' => $this->storeManager,
                'categoryCollectionFactory' => $collectionFactory,
            ]
        );
    }

    /**
     * Get clean mock by class name
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCleanMock($className)
    {
        return $this->getMock($className, [], [], '', false);
    }

    protected function _preparationData()
    {
        $this->_childrenCategory = $this->getMock(
            '\Magento\Catalog\Model\Category',
            ['getIsActive', '__wakeup'],
            [],
            '',
            false
        );


        $this->_category = $this->getMock(
            '\Magento\Catalog\Model\Category',
            ['getIsActive', '__wakeup', 'getName', 'getChildren', 'getUseFlatResource', 'getChildrenNodes'],
            [],
            '',
            false
        );

        $blockMock = $this->_getCleanMock('\Magento\Theme\Block\Html\Topmenu');

        $eventMock = $this->getMock('\Magento\Framework\Event', ['getBlock'], [], '', false);
        $eventMock->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($blockMock));

        $observerMock = $this->getMock('\Magento\Framework\Event\Observer', ['getEvent', 'getMenu'], [], '', false);
        $observerMock->expects($this->any())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));

        return $observerMock;
    }

    public function testAddCatalogToTopMenuItems()
    {
        $observer = $this->_preparationData();
        $this->_observer->execute($observer);
    }


}
