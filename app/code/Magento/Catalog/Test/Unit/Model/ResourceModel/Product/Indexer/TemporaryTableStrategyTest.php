<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\TemporaryTableStrategy;

class TemporaryTableStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\TemporaryTableStrategy
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\Table\Strategy
     */
    private $tableStrategyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResourceConnection
     */
    private $resourceMock;

    protected function setUp()
    {
        $this->tableStrategyMock = $this->createMock(\Magento\Framework\Indexer\Table\Strategy::class);
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);

        $this->model = new \Magento\Catalog\Model\ResourceModel\Product\Indexer\TemporaryTableStrategy(
            $this->tableStrategyMock,
            $this->resourceMock
        );
    }

    public function testGetUseIdxTable()
    {
        $this->tableStrategyMock->expects($this->once())->method('getUseIdxTable')->willReturn(true);
        $this->assertTrue($this->model->getUseIdxTable());
    }

    public function testSetUseIdxTable()
    {
        $this->tableStrategyMock->expects($this->once())->method('setUseIdxTable')->with(true)->willReturnSelf();
        $this->assertEquals($this->tableStrategyMock, $this->model->setUseIdxTable(true));
    }

    public function testGetTableName()
    {
        $tablePrefix = 'prefix';
        $expectedResult = $tablePrefix . \Magento\Framework\Indexer\Table\StrategyInterface::IDX_SUFFIX;
        $this->tableStrategyMock->expects($this->once())->method('getUseIdxTable')->willReturn(true);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with($expectedResult)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->getTableName($tablePrefix));
    }

    public function testPrepareTableName()
    {
        $tablePrefix = 'prefix';
        $expectedResult = $tablePrefix . TemporaryTableStrategy::TEMP_SUFFIX;
        $tempTableName = $tablePrefix . \Magento\Framework\Indexer\Table\StrategyInterface::TMP_SUFFIX;

        $this->tableStrategyMock->expects($this->once())->method('getUseIdxTable')->willReturn(false);
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->with('indexer')
            ->willReturn($connectionMock);
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with($expectedResult)
            ->willReturn($expectedResult);
        $this->resourceMock->expects($this->at(2))
            ->method('getTableName')
            ->with($tempTableName)
            ->willReturn($tempTableName);
        $connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedResult, $tempTableName, true);

        $this->assertEquals($expectedResult, $this->model->prepareTableName($tablePrefix));
    }
}
