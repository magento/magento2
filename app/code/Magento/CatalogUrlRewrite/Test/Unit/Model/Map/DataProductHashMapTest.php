<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogUrlRewrite\Model\Map\HashMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataProductHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Class DataProductHashMapTest
 */
class DataProductHashMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var HashMapPool|\PHPUnit_Framework_MockObject_MockObject */
    private $hashMapPoolMock;

    /** @var DataCategoryHashMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataCategoryMapMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var ProductCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionMock;

    /** @var DataProductHashMap|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->hashMapPoolMock = $this->getMock(HashMapPool::class, [], [], '', false);
        $this->dataCategoryMapMock = $this->getMock(DataCategoryHashMap::class, [], [], '', false);
        $this->collectionFactoryMock = $this->getMock(CollectionFactory::class, ['create'], [], '', false);
        $this->productCollectionMock = $this->getMock(
            ProductCollection::class,
            ['getSelect', 'getConnection', 'getAllIds'],
            [],
            '',
            false
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

        $connectionMock = $this->getMock(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, [], [], '', false);

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
