<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Fixtures\CategoriesFixture;
use Magento\Setup\Fixtures\FixtureModel;

class CategoriesFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\CategoriesFixture
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryFactoryMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->createMock(FixtureModel::class);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->collectionMock = $this->createMock(Collection::class);
        $this->categoryFactoryMock = $this->createPartialMock(CategoryFactory::class, ['create']);

        $this->model = (new ObjectManager($this))->getObject(CategoriesFixture::class, [
            'fixtureModel' => $this->fixtureModelMock,
            'collectionFactory' => $this->collectionFactoryMock,
            'rootCategoriesIds' => [2],
            'categoryFactory' => $this->categoryFactoryMock,
            'firstLevelCategoryIndex' => 1,
        ]);
    }

    public function testDoNoExecuteIfCategoriesAlreadyGenerated()
    {
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getSize')->willReturn(32);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(30);
        $this->categoryFactoryMock->expects($this->never())->method('create');

        $this->model->execute();
    }

    public function testExecute()
    {
        $valueMap = [
            ['categories', 0, 1],
            ['categories_nesting_level', 3, 3]
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getSize')->willReturn(2);

        $parentCategoryMock = $this->createPartialMock(\Magento\Catalog\Model\Category::class, [
                'getName',
                'setId',
                'getId',
                'setUrlKey',
                'setUrlPath',
                'setName',
                'setParentId',
                'setPath',
                'setLevel',
                'getLevel',
                'setAvailableSortBy',
                'setDefaultSortBy',
                'setIsActive',
                'setIsAnchor',
                'save',
                'setStoreId',
                'load',
            ]);
        $parentCategoryMock->expects($this->once())->method('getId')->willReturn(5);
        $parentCategoryMock->expects($this->once())->method('getLevel')->willReturn(3);
        $categoryMock = clone $parentCategoryMock;
        $categoryMock->expects($this->once())
            ->method('getName')
            ->with('Category 1')
            ->will($this->returnValue('category_name'));
        $categoryMock->expects($this->once())
            ->method('setId')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setUrlKey')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setUrlPath')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setName')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setParentId')
            ->with(5)
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setIsAnchor')
            ->with(true)
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setLevel')
            ->with(4)
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setAvailableSortBy')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setDefaultSortBy')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setIsActive')
            ->willReturnSelf();

        $this->categoryFactoryMock->expects($this->once())->method('create')->willReturn($categoryMock);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating categories', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'categories' => 'Categories'
        ], $this->model->introduceParamLabels());
    }
}
