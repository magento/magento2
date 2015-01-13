<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Layer\Search;

class FilterableAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\Search\FilterableAttributeList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory', ['create'], [], '', false);

        $this->storeManagerMock = $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface', [], [], '', false
        );

        $this->layerMock = $this->getMock(
            '\Magento\Catalog\Model\Layer\Search', [], [], '', false
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Resolver')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($this->layerMock));

        $this->model = new \Magento\Catalog\Model\Layer\Search\FilterableAttributeList(
            $this->collectionFactoryMock,
            $this->storeManagerMock,
            $layerResolver
        );

    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Search\FilterableAttributeList::_prepareAttributeCollection()
     */
    public function testGetList()
    {
        $productCollectionMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Collection', [], [], '', false
        );
        $this->layerMock->expects($this->once())->method('getProductCollection')
            ->will($this->returnValue($productCollectionMock));
        $setIds = [2, 3, 5];
        $productCollectionMock->expects($this->once())->method('getSetIds')->will($this->returnValue($setIds));

        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        $storeId = 4321;
        $storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $collectionMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Attribute\Collection', [], [], '', false
        );
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));

        $collectionMock
            ->expects($this->once())
            ->method('setItemObjectClass')
            ->with('Magento\Catalog\Model\Resource\Eav\Attribute')
            ->will($this->returnSelf());
        $collectionMock
            ->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with($setIds)
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
