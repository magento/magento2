<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\CatalogUrlRewrite\Model\Map\DataProductHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\HashMapPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\TemporaryTableService;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProductUrlRewriteDatabaseMapTest extends TestCase
{
    /** @var HashMapPool|MockObject */
    private $hashMapPoolMock;

    /** @var DataProductHashMap|MockObject */
    private $dataProductMapMock;

    /** @var TemporaryTableService|MockObject */
    private $temporaryTableServiceMock;

    /** @var ResourceConnection|MockObject */
    private $connectionMock;

    /** @var DataProductUrlRewriteDatabaseMap|MockObject */
    private $model;

    protected function setUp(): void
    {
        $this->hashMapPoolMock = $this->createMock(HashMapPool::class);
        $this->dataProductMapMock = $this->createMock(DataProductHashMap::class);
        $this->temporaryTableServiceMock = $this->createMock(TemporaryTableService::class);
        $this->connectionMock = $this->createMock(ResourceConnection::class);

        $this->hashMapPoolMock->expects($this->any())
            ->method('getDataMap')
            ->willReturn($this->dataProductMapMock);

        $this->model = (new ObjectManager($this))->getObject(
            DataProductUrlRewriteDatabaseMap::class,
            [
                'connection' => $this->connectionMock,
                'hashMapPool' => $this->hashMapPoolMock,
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
            '1' => ['store_id' => 1, 'product_id' => 1],
            '2' => ['store_id' => 2, 'product_id' => 1],
            '3' => ['store_id' => 3, 'product_id' => 1],
            '4' => ['store_id' => 1, 'product_id' => 2],
            '5' => ['store_id' => 2, 'product_id' => 2],
        ];

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

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

        $this->dataProductMapMock->expects($this->any())
            ->method('getAllData')
            ->willReturn([]);

        $this->temporaryTableServiceMock->expects($this->any())
            ->method('createFromSelect')
            ->with(
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
