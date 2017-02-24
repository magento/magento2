<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Layer\Search;

class FilterableAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\Search\FilterableAttributeList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->storeManagerMock = $this->getMock(
            \Magento\Store\Model\StoreManagerInterface::class, [], [], '', false
        );

        $this->model = new \Magento\Catalog\Model\Layer\Search\FilterableAttributeList(
            $this->collectionFactoryMock,
            $this->storeManagerMock
        );

    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Search\FilterableAttributeList::_prepareAttributeCollection()
     */
    public function testGetList()
    {
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        $storeId = 4321;
        $storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $collectionMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class, [], [], '', false
        );
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));

        $collectionMock
            ->expects($this->once())
            ->method('setItemObjectClass')
            ->with(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->will($this->returnSelf());
        $collectionMock
            ->expects($this->once())
            ->method('addStoreLabel')
            ->will($this->returnSelf());
        $collectionMock
            ->expects($this->once())
            ->method('setOrder');

        $collectionMock->expects($this->once())->method('addIsFilterableInSearchFilter')->will($this->returnSelf());
        $collectionMock->expects($this->once())->method('addVisibleFilter')->will($this->returnSelf());
        $collectionMock->expects($this->once())->method('load');

        $this->assertEquals($collectionMock, $this->model->getList());
    }
}
