<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CategoriesFixture;

class CategoriesFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\CategoriesFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock('\Magento\Setup\Fixtures\FixtureModel', [], [], '', false);

        $this->model = new CategoriesFixture($this->fixtureModelMock);
    }
    public function testExecute()
    {
        $categoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [
                'getName',
                'setId',
                'setUrlKey',
                'setUrlPath',
                'setName',
                'setParentId',
                'setPath',
                'setLevel',
                'setAvailableSortBy',
                'setDefaultSortBy',
                'setIsActive',
                'save'
            ],
            [],
            '',
            false
        );
        $categoryMock->expects($this->once())
            ->method('getName')
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
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();
        $categoryMock->expects($this->once())
            ->method('setLevel')
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

        $groupMock = $this->getMock('\Magento\Store\Model\Group', [], [], '', false);
        $groupMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue('root_category_id'));

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$groupMock]));

        $objectValueMock = [
            ['Magento\Store\Model\StoreManager', [], $storeManagerMock],
            ['Magento\Catalog\Model\Category', [], $categoryMock]
        ];

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap($objectValueMock));

        $valueMap = [
            ['categories', 0, 1],
            ['categories_nesting_level', 3, 3]
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock->expects($this->never())->method('save');

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo('Magento\Catalog\Model\Category'))
            ->willReturn($categoryMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

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
