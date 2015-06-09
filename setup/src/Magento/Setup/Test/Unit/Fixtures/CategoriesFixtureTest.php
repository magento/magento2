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

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')
            ->disableOriginalConstructor()
            ->getMock();
    }
    public function testExecute()
    {
        $categoryMock = $this->getMockBuilder('\Magento\Catalog\Model\Category')->disableOriginalConstructor()
            ->setMethods([
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
            ])->getMock();
        $categoryMock
            ->expects($this->once())
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
        $groupMock
            ->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue('root_category_id'));

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock
            ->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$groupMock]));

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($storeManagerMock, $categoryMock));

        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->onConsecutiveCalls(1, 3));
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $categoriesFixture = new CategoriesFixture($this->fixtureModelMock);
        $categoriesFixture->execute();
    }

    public function testGetActionTitle()
    {
        $categoriesFixture = new CategoriesFixture($this->fixtureModelMock);
        $this->assertSame('Generating categories', $categoriesFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $categoriesFixture = new CategoriesFixture($this->fixtureModelMock);
        $this->assertSame([
            'categories' => 'Categories'
        ], $categoriesFixture->introduceParamLabels());
    }
}
