<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Observer
     */
    protected $_observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Category
     */
    protected $_childrenCategory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $_categoryFlatState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    public function setUp()
    {
        $this->_catalogCategory = $this->getMock(
            '\Magento\Catalog\Helper\Category',
            ['getStoreCategories', 'getCategoryUrl'],
            [],
            '',
            false
        );

        $this->_categoryFlatState = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Category\Flat\State',
            ['isFlatEnabled'],
            [],
            '',
            false
        );

        $this->_storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $layerResolver = $this->_getCleanMock('Magento\Catalog\Model\Layer\Resolver');
        $layerResolver->expects($this->once())->method('get')->willReturn(null);
        $this->_observer = (new ObjectManager($this))->getObject(
            'Magento\Catalog\Model\Observer',
            [
                'categoryResource' => $this->_getCleanMock('\Magento\Catalog\Model\Resource\Category'),
                'catalogProduct' => $this->_getCleanMock('\Magento\Catalog\Model\Resource\Product'),
                'storeManager' => $this->_storeManager,
                'layerResolver' => $layerResolver,
                'indexIndexer' => $this->_getCleanMock('\Magento\Index\Model\Indexer'),
                'catalogCategory' => $this->_catalogCategory,
                'catalogData' => $this->_getCleanMock('\Magento\Catalog\Helper\Data'),
                'categoryFlatState' => $this->_categoryFlatState,
                'productResourceFactory' => $this->getMock(
                    'Magento\Catalog\Model\Resource\ProductFactory',
                    ['create'],
                    [],
                    '',
                    false
                )
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
        $this->_childrenCategory->expects($this->once())
            ->method('getIsActive')
            ->will($this->returnValue(false));

        $this->_category = $this->getMock(
            '\Magento\Catalog\Model\Category',
            ['getIsActive', '__wakeup', 'getName', 'getChildren', 'getUseFlatResource', 'getChildrenNodes'],
            [],
            '',
            false
        );
        $this->_category->expects($this->once())
            ->method('getIsActive')
            ->will($this->returnValue(true));
        $this->_category->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Name'));

        $this->_catalogCategory->expects($this->once())
            ->method('getStoreCategories')
            ->will($this->returnValue([$this->_category]));
        $this->_catalogCategory->expects($this->once())
            ->method('getCategoryUrl')
            ->will($this->returnValue('url'));

        $blockMock = $this->_getCleanMock('\Magento\Theme\Block\Html\Topmenu');

        $treeMock = $this->_getCleanMock('\Magento\Framework\Data\Tree');

        $menuMock = $this->getMock('\Magento\Framework\Data\Tree\Node', ['getTree', 'addChild'], [], '', false);
        $menuMock->expects($this->once())
            ->method('getTree')
            ->will($this->returnValue($treeMock));

        $eventMock = $this->getMock('\Magento\Framework\Event', ['getBlock'], [], '', false);
        $eventMock->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($blockMock));

        $observerMock = $this->getMock('\Magento\Framework\Event\Observer', ['getEvent', 'getMenu'], [], '', false);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));
        $observerMock->expects($this->once())
            ->method('getMenu')
            ->will($this->returnValue($menuMock));

        return $observerMock;
    }

    public function testAddCatalogToTopMenuItemsWithoutFlat()
    {
        $observer = $this->_preparationData();

        $this->_category->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue([$this->_childrenCategory]));

        $this->_observer->addCatalogToTopmenuItems($observer);
    }

    public function testAddCatalogToTopMenuItemsWithFlat()
    {
        $observer = $this->_preparationData();

        $this->_category->expects($this->once())
            ->method('getChildrenNodes')
            ->will($this->returnValue([$this->_childrenCategory]));

        $this->_category->expects($this->once())
            ->method('getUseFlatResource')
            ->will($this->returnValue(true));

        $this->_categoryFlatState->expects($this->once())
            ->method('isFlatEnabled')
            ->will($this->returnValue(true));

        $this->_observer->addCatalogToTopmenuItems($observer);
    }

    public function testGetMenuCategoryData()
    {
        $category = $this->getMock('Magento\Catalog\Model\Category', ['getId', 'getName'], [], '', false);
        $category->expects($this->once())->method('getId')->willReturn('id');
        $category->expects($this->once())->method('getName')->willReturn('name');
        $this->_catalogCategory->expects($this->once())->method('getCategoryUrl')->willReturn('url');

        $this->assertEquals(
            [
                'name' => 'name',
                'id' => 'category-node-id',
                'url' => 'url',
                'is_active' => false,
                'has_active' => false,
            ],
            $this->_observer->getMenuCategoryData($category)
        );
    }
}
