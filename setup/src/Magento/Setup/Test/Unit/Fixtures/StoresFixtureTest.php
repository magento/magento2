<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\StoresFixture;

class StoresFixtureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\StoresFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock('\Magento\Setup\Fixtures\FixtureModel', [], [], '', false);

        $this->model = new StoresFixture($this->fixtureModelMock);
    }

    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $websiteMock = $this->getMock('\Magento\Store\Model\Website', [], [], '', false);
        $websiteMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('website_id');
        $websiteMock->expects($this->once())
            ->method('save');

        $groupMock = $this->getMock('\Magento\Store\Model\Group', [], [], '', false);
        $groupMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('group_id');
        $groupMock->expects($this->once())
            ->method('save');

        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(1);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn('store_id');
        $storeMock->expects($this->once())
            ->method('save');

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->willReturn($groupMock);
        $storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);
        $storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $categoryMock = $this->getMock(
            'Magento\Catalog\Model\Category',
            [
                'setName',
                'setPath',
                'setLevel',
                'setAvailableSortBy',
                'setDefaultSortBy',
                'setIsActive',
                'getId',
                'save',
                'load'
            ],
            [],
            '',
            false
        );
        $categoryMock->expects($this->once())
            ->method('setName')
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
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn('category_id');

        $valueMap = [
            ['Magento\Store\Model\StoreManager', [], $storeManagerMock],
            ['Magento\Catalog\Model\Category', [], $categoryMock]
        ];

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap($valueMap));

        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValue(1));
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->never())->method('save');

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock->expects($this->never())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo('Magento\Store\Model\StoreManager'))
            ->willReturn($storeManagerMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating websites, stores and store views', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'websites' => 'Websites',
            'store_groups' => 'Store Groups',
            'store_views' => 'Store Views'
        ], $this->model->introduceParamLabels());
    }
}
