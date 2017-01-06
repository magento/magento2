<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\DB\Select;
use Magento\CatalogUrlRewrite\Model\Map\HashMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataProductHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUsedInProductsHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\TemporaryTableService;

/**
 * Class DataCategoryUrlRewriteDatabaseMapTest
 */
class DataCategoryUrlRewriteDatabaseMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var HashMapPool|\PHPUnit_Framework_MockObject_MockObject */
    private $dataMapPoolMock;

    /** @var DataCategoryHashMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataCategoryMapMock;

    /** @var DataCategoryUsedInProductsHashMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataCategoryUsedInProductsMapMock;

    /** @var TemporaryTableService|\PHPUnit_Framework_MockObject_MockObject */
    private $temporaryTableServiceMock;

    /** @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    private $connectionMock;

    /** @var DataCategoryUrlRewriteDatabaseMap|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->dataMapPoolMock = $this->getMock(HashMapPool::class, [], [], '', false);
        $this->dataCategoryMapMock = $this->getMock(DataProductHashMap::class, [], [], '', false);
        $this->dataCategoryUsedInProductsMapMock = $this->getMock(
            DataCategoryUsedInProductsHashMap::class,
            [],
            [],
            '',
            false
        );
        $this->temporaryTableServiceMock = $this->getMock(TemporaryTableService::class, [], [], '', false);
        $this->connectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);

        $this->dataMapPoolMock->expects($this->any())
            ->method('getDataMap')
            ->willReturnOnConsecutiveCalls($this->dataCategoryUsedInProductsMapMock, $this->dataCategoryMapMock);

        $this->model = (new ObjectManager($this))->getObject(
            DataCategoryUrlRewriteDatabaseMap::class,
            [
                'connection' => $this->connectionMock,
                'dataMapPool' => $this->dataMapPoolMock,
                'temporaryTableService' => $this->temporaryTableServiceMock
            ]
        );
    }

    /**
     * Tests getAllData, getData and resetData functionality
     */
    public function testGetAllData()
    {
        $productStoreIds = [
            '1' => ['store_id' => 1, 'category_id' => 1],
            '2' => ['store_id' => 2, 'category_id' => 1],
            '3' => ['store_id' => 3, 'category_id' => 1],
            '4' => ['store_id' => 1, 'category_id' => 2],
            '5' => ['store_id' => 2, 'category_id' => 2],
        ];

        $connectionMock = $this->getMock(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, [], [], '', false);

        $this->connectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturn($productStoreIds[3]);
        $selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('joinInner')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->dataCategoryMapMock->expects($this->once())
            ->method('getAllData')
            ->willReturn([]);
        $this->dataCategoryUsedInProductsMapMock->expects($this->once())
            ->method('getAllData')
            ->willReturn([]);
        $this->temporaryTableServiceMock->expects($this->any())
            ->method('createFromSelect')
            ->withConsecutive(
                $selectMock,
                $connectionMock,
                [
                    'PRIMARY' => ['url_rewrite_id'],
                    'HASHKEY_ENTITY_STORE' => ['hash_key'],
                    'ENTITY_STORE' => ['entity_id', 'store_id']
                ]
            )
            ->willReturn('tempTableName');

        $this->assertEquals($productStoreIds[3], $this->model->getData(1, '3_1'));
    }
}
