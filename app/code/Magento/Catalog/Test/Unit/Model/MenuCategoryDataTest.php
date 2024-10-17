<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\MenuCategoryData;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MenuCategoryDataTest extends TestCase
{
    /**
     * @var MockObject|CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @var MenuCategoryData
     */
    protected $model;

    protected function setUp(): void
    {
        $this->categoryHelper = $this->createPartialMock(
            CategoryHelper::class,
            ['getStoreCategories', 'getCategoryUrl']
        );

        $layer = $this->createMock(Layer::class);
        $layerResolver = $this->createMock(Resolver::class);
        $layerResolver->expects($this->once())
            ->method('get')
            ->willReturn($layer);

        $this->model = (new ObjectManager($this))->getObject(
            MenuCategoryData::class,
            [
                'layerResolver' => $layerResolver,
                'catalogCategory' => $this->categoryHelper,
                'catalogData' => $this->createMock(Data::class),
            ]
        );
    }

    public function testGetMenuCategoryData()
    {
        $category = $this->createPartialMock(Category::class, ['getId', 'getName']);
        $category->expects($this->once())
            ->method('getId')
            ->willReturn('id');
        $category->expects($this->once())
            ->method('getName')
            ->willReturn('name');

        $this->categoryHelper->expects($this->once())
            ->method('getCategoryUrl')
            ->willReturn('url');

        $this->assertEquals(
            [
                'name' => 'name',
                'id' => 'category-node-id',
                'url' => 'url',
                'is_active' => false,
                'has_active' => false,
            ],
            $this->model->getMenuCategoryData($category)
        );
    }
}
