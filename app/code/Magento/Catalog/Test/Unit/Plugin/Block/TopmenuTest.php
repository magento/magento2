<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Plugin\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TopmenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Plugin\Block\Topmenu
     */
    protected $block;

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

    protected function setUp()
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
            ->setMethods(
                [
                    'addIsActiveFilter',
                    'addAttributeToSelect',
                    'addFieldToFilter',
                    'addAttributeToFilter',
                    'addUrlRewriteToResult',
                    'getIterator',
                    'setStoreId'
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())->method('addIsActiveFilter');
        $collectionFactory->expects($this->once())->method('create')
            ->willReturn($collection);

        $collection->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $this->block = (new ObjectManager($this))->getObject(
            \Magento\Catalog\Plugin\Block\Topmenu::class,
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
        return $blockMock;
    }

    public function testAddCatalogToTopMenuItems()
    {
        $this->block->beforeGetHtml($this->_preparationData());
    }
}
