<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MenuCategoryDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Observer\MenuCategoryData
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

        $layerResolver = $this->getMock('Magento\Catalog\Model\Layer\Resolver', [], [], '', false);
        $layerResolver->expects($this->once())->method('get')->willReturn(null);
        $this->_observer = (new ObjectManager($this))->getObject(
            'Magento\Catalog\Observer\MenuCategoryData',
            [
                'layerResolver' => $layerResolver,
                'catalogCategory' => $this->_catalogCategory,
                'catalogData' => $this->getMock('\Magento\Catalog\Helper\Data', [], [], '', false),
            ]
        );
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
