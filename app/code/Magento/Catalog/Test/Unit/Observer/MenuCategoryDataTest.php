<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Helper\Category;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Observer\MenuCategoryData;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MenuCategoryDataTest extends TestCase
{
    /**
     * @var MenuCategoryData
     */
    protected $_observer;

    /**
     * @var MockObject|Category
     */
    protected $_catalogCategory;

    /**
     * @var MockObject|\Magento\Catalog\Model\Category
     */
    protected $_childrenCategory;

    /**
     * @var MockObject|\Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var MockObject|State
     */
    protected $_categoryFlatState;

    protected function setUp(): void
    {
        $this->_catalogCategory = $this->createPartialMock(
            Category::class,
            ['getStoreCategories', 'getCategoryUrl']
        );

        $layerResolver = $this->createMock(Resolver::class);
        $layerResolver->expects($this->once())->method('get')->willReturn(null);
        $this->_observer = (new ObjectManager($this))->getObject(
            MenuCategoryData::class,
            [
                'layerResolver' => $layerResolver,
                'catalogCategory' => $this->_catalogCategory,
                'catalogData' => $this->createMock(Data::class),
            ]
        );
    }

    public function testGetMenuCategoryData()
    {
        $category = $this->createPartialMock(\Magento\Catalog\Model\Category::class, ['getId', 'getName']);
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
