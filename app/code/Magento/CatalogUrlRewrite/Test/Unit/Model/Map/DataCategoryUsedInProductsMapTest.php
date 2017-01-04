<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\DB\Select;
use Magento\CatalogUrlRewrite\Model\Map\DataMapPoolInterface;
use Magento\CatalogUrlRewrite\Model\Map\DataProductMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUsedInProductsMap;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class DataCategoryUsedInProductsMapTest
 */
class DataCategoryUsedInProductsMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var DataMapPoolInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $dataMapPoolMock;

    /** @var DataCategoryMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataCategoryMapMock;

    /** @var DataProductMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataProductMapMock;

    /** @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    private $connectionMock;

    /** @var DataCategoryUsedInProductsMap|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->dataMapPoolMock = $this->getMock(DataMapPoolInterface::class);
        $this->dataCategoryMapMock = $this->getMock(DataCategoryMap::class, [], [], '', false);
        $this->dataProductMapMock = $this->getMock(DataProductMap::class, [], [], '', false);
        $this->connectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);

        $this->dataMapPoolMock->expects($this->any())
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
            DataCategoryUsedInProductsMap::class,
            [
                'connection' => $this->connectionMock,
                'dataMapPool' => $this->dataMapPoolMock,
                'mapData' => [],
            ]
        );
    }

    /**
     * Tests getAllData, getData and resetData functionality
     */
    public function testGetAllData()
    {
        $categoryIds = ['1' => [1, 2, 3], '2' => [2, 3], '3' => 3];
        $categoryIdsOther = ['2' => [2, 3, 4]];

        $connectionMock = $this->getMock(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, [], [], '', false);

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
        $this->dataMapPoolMock->expects($this->at(4))
            ->method('resetDataMap')
            ->with(DataProductMap::class, 1);
        $this->dataMapPoolMock->expects($this->at(5))
            ->method('resetDataMap')
            ->with(DataCategoryMap::class, 1);

        $this->assertEquals($categoryIds, $this->model->getAllData(1));
        $this->assertEquals($categoryIds[2], $this->model->getData(1, 2));
        $this->assertEquals($categoryIdsOther, $this->model->getAllData(2));
        $this->assertEquals($categoryIdsOther[2], $this->model->getData(2, 2));
        $this->model->resetData(1);
        $this->assertEquals($categoryIds[2], $this->model->getData(1, 2));
        $this->assertEquals($categoryIds, $this->model->getAllData(1));
    }
}
