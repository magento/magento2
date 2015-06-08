<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')->disableOriginalConstructor()->getMock();
    }

    public function testExecute()
    {
        $websiteMock = $this->getMockBuilder('\Magento\Store\Model\Website')->disableOriginalConstructor()->getMock();
        $websiteMock->expects($this->any())
            ->method('getId')
            ->willReturn('website_id');
        $websiteMock->expects($this->once())
            ->method('save');

        $groupMock = $this->getMockBuilder('\Magento\Store\Model\Group')->disableOriginalConstructor()->getMock();
        $groupMock->expects($this->any())
            ->method('getId')
            ->willReturn('group_id');
        $groupMock->expects($this->once())
            ->method('save');

        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(1);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn('store_id');
        $storeMock->expects($this->once())
            ->method('save');

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')->disableOriginalConstructor()->getMock();
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

        $categoryMock = $this->getMockBuilder('Magento\Catalog\Model\Category')->disableOriginalConstructor()->setMethods(array(
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
            'getId',
            'save'
        ))->getMock();
        $categoryMock->expects($this->once())
            ->method('setId')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setUrlKey')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setUrlPath')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setName')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setParentId')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setPath')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setLevel')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setAvailableSortBy')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setDefaultSortBy')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('setIsActive')
            ->willReturnSelf();
        $categoryMock->expects($this->any())
            ->method('getId')
            ->willReturn('category_id');

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')->disableOriginalConstructor()->getMock();
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($storeManagerMock, $categoryMock));

        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->onConsecutiveCalls(1, 1, 1));
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $storesFixture = new StoresFixture($this->fixtureModelMock);
        $storesFixture->execute();
    }

    public function testGetActionTitle()
    {
        $storesFixture = new StoresFixture($this->fixtureModelMock);
        $this->assertSame('Generating websites, stores and store views', $storesFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $storesFixture = new StoresFixture($this->fixtureModelMock);
        $this->assertSame([
            'websites' => 'Websites',
            'store_groups' => 'Store Groups',
            'store_views' => 'Store Views'
        ], $storesFixture->introduceParamLabels());
    }
}
