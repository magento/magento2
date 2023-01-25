<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductHashMap;
use Magento\CatalogUrlRewrite\Model\Map\HashMapPool;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProductHashMapTest extends TestCase
{
    /** @var HashMapPool|MockObject */
    private $hashMapPoolMock;

    /** @var DataCategoryHashMap|MockObject */
    private $dataCategoryMapMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var ProductCollection|MockObject
     */
    private $productCollectionMock;

    /** @var DataProductHashMap|MockObject */
    private $model;

    protected function setUp(): void
    {
        $this->hashMapPoolMock = $this->createMock(HashMapPool::class);
        $this->dataCategoryMapMock = $this->createMock(DataCategoryHashMap::class);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->productCollectionMock = $this->createPartialMock(
            ProductCollection::class,
            ['getSelect', 'getConnection', 'getAllIds']
        );

        $this->collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productCollectionMock);

        $this->hashMapPoolMock->expects($this->any())
            ->method('getDataMap')
            ->willReturn($this->dataCategoryMapMock);

        $this->model = (new ObjectManager($this))->getObject(
            DataProductHashMap::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'hashMapPool' => $this->hashMapPoolMock
            ]
        );
    }

    /**
     * Tests getAllData, getData and resetData functionality
     */
    public function testGetAllData()
    {
        $productIds = ['1' => [1, 2, 3], '2' => [2, 3], '3' => 3];
        $productIdsOther = ['2' => [2, 3, 4]];

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $this->productCollectionMock->expects($this->exactly(3))
            ->method('getAllIds')
            ->willReturnOnConsecutiveCalls($productIds, $productIdsOther, $productIds);
        $this->productCollectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturn($this->returnValue($this->returnArgument(0)));
        $this->productCollectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($selectMock);
        $selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('joinInner')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->dataCategoryMapMock->expects($this->any())
            ->method('getAllData')
            ->willReturn([]);
        $this->hashMapPoolMock->expects($this->any())
            ->method('resetMap')
            ->with(DataCategoryHashMap::class, 1);
        $this->assertEquals($productIds, $this->model->getAllData(1));
        $this->assertEquals($productIds[2], $this->model->getData(1, 2));
        $this->assertEquals($productIdsOther, $this->model->getAllData(2));
        $this->assertEquals($productIdsOther[2], $this->model->getData(2, 2));
        $this->model->resetData(1);
        $this->assertEquals($productIds[2], $this->model->getData(1, 2));
        $this->assertEquals($productIds, $this->model->getAllData(1));
    }
}
