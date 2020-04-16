<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Search;

class FilterableAttributeListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\Search\FilterableAttributeList
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create']
        );

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

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
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $storeId = 4321;
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);

        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class);
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock
            ->expects($this->once())
            ->method('setItemObjectClass')
            ->with(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->willReturnSelf();
        $collectionMock
            ->expects($this->once())
            ->method('addStoreLabel')
            ->willReturnSelf();
        $collectionMock
            ->expects($this->once())
            ->method('setOrder');

        $collectionMock->expects($this->once())->method('addIsFilterableInSearchFilter')->willReturnSelf();
        $collectionMock->expects($this->once())->method('addVisibleFilter')->willReturnSelf();
        $collectionMock->expects($this->once())->method('load');

        $this->assertEquals($collectionMock, $this->model->getList());
    }
}
