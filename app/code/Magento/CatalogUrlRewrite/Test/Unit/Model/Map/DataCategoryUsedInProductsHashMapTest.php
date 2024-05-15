<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUsedInProductsHashMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductHashMap;
use Magento\CatalogUrlRewrite\Model\Map\HashMapPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataCategoryUsedInProductsHashMapTest extends TestCase
{
    /**
     * @var HashMapPool|MockObject
     */
    private $hashMapPoolMock;

    /**
     * @var DataCategoryHashMap|MockObject
     */
    private $dataCategoryMapMock;

    /**
     * @var DataProductHashMap|MockObject
     */
    private $dataProductMapMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $connectionMock;

    /**
     * @var DataCategoryUsedInProductsHashMap|MockObject
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->hashMapPoolMock = $this->createMock(HashMapPool::class);
        $this->dataCategoryMapMock = $this->createMock(DataCategoryHashMap::class);
        $this->dataProductMapMock = $this->createMock(DataProductHashMap::class);
        $this->connectionMock = $this->createMock(ResourceConnection::class);

        $this->hashMapPoolMock->expects($this->any())
            ->method('getDataMap')
            ->willReturnOnConsecutiveCalls(
                $this->dataProductMapMock,
                $this->dataCategoryMapMock,
                $this->dataProductMapMock,
                $this->dataCategoryMapMock,
                $this->dataProductMapMock,
                $this->dataCategoryMapMock
            );

        $this->model = (new ObjectManager($this))->getObject(
            DataCategoryUsedInProductsHashMap::class,
            [
                'connection' => $this->connectionMock,
                'hashMapPool' => $this->hashMapPoolMock
            ]
        );
    }

    /**
     * Tests getAllData, getData and resetData functionality.
     *
     * @return void
     */
    public function testGetAllData(): void
    {
        $categoryIds = ['1' => [1, 2, 3], '2' => [2, 3], '3' => 3];
        $categoryIdsOther = ['2' => [2, 3, 4]];

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $this->connectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $connectionMock->expects($this->any())
            ->method('fetchCol')
            ->willReturnOnConsecutiveCalls($categoryIds, $categoryIdsOther, $categoryIds);
        $selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('joinInner')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->hashMapPoolMock
            ->method('resetMap')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == DataProductHashMap::class || $arg2 == 1) {
                    return null;
                }
            });

        $this->assertEquals($categoryIds, $this->model->getAllData(1));
        $this->assertEquals($categoryIds[2], $this->model->getData(1, 2));
        $this->assertEquals($categoryIdsOther, $this->model->getAllData(2));
        $this->assertEquals($categoryIdsOther[2], $this->model->getData(2, 2));
        $this->model->resetData(1);
        $this->assertEquals($categoryIds[2], $this->model->getData(1, 2));
        $this->assertEquals($categoryIds, $this->model->getAllData(1));
    }
}
