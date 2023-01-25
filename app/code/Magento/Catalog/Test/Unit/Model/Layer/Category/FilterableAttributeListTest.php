<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Category;

use Magento\Catalog\Model\Layer\Search\FilterableAttributeList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterableAttributeListTest extends TestCase
{
    /**
     * @var FilterableAttributeList
     */
    protected $model;

    /**
     * @var MockObject|CollectionFactory
     */
    protected $collectionFactoryMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->model = new FilterableAttributeList(
            $this->collectionFactoryMock,
            $this->storeManagerMock
        );
    }

    public function testGetList()
    {
        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $storeId = 4321;
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);

        $collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock
            ->expects($this->once())
            ->method('setItemObjectClass')
            ->with(Attribute::class)->willReturnSelf();
        $collectionMock
            ->expects($this->once())
            ->method('addStoreLabel')
            ->with($storeId)->willReturnSelf();
        $collectionMock
            ->expects($this->once())
            ->method('setOrder')
            ->with('position', 'ASC');
        $collectionMock->expects($this->once())->method('addIsFilterableInSearchFilter')->willReturnSelf();
        $collectionMock->expects($this->once())->method('load');

        $this->assertEquals($collectionMock, $this->model->getList());
    }
}
