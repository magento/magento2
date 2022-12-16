<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\TemporaryTableStrategy;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\Table\Strategy;
use Magento\Framework\Indexer\Table\StrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TemporaryTableStrategyTest extends TestCase
{
    /**
     * @var TemporaryTableStrategy
     */
    private $model;

    /**
     * @var MockObject|Strategy
     */
    private $tableStrategyMock;

    /**
     * @var MockObject|ResourceConnection
     */
    private $resourceMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->tableStrategyMock = $this->createMock(Strategy::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);

        $this->model = new TemporaryTableStrategy(
            $this->tableStrategyMock,
            $this->resourceMock
        );
    }

    /**
     * @return void
     */
    public function testGetUseIdxTable(): void
    {
        $this->tableStrategyMock->expects($this->once())->method('getUseIdxTable')->willReturn(true);
        $this->assertTrue($this->model->getUseIdxTable());
    }

    /**
     * @return void
     */
    public function testSetUseIdxTable(): void
    {
        $this->tableStrategyMock->expects($this->once())->method('setUseIdxTable')->with(true)->willReturnSelf();
        $this->assertEquals($this->tableStrategyMock, $this->model->setUseIdxTable(true));
    }

    /**
     * @return void
     */
    public function testGetTableName(): void
    {
        $tablePrefix = 'prefix';
        $expectedResult = $tablePrefix . StrategyInterface::IDX_SUFFIX;
        $this->tableStrategyMock->expects($this->once())->method('getUseIdxTable')->willReturn(true);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with($expectedResult)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->getTableName($tablePrefix));
    }

    /**
     * @return void
     */
    public function testPrepareTableName(): void
    {
        $tablePrefix = 'prefix';
        $expectedResult = $tablePrefix . TemporaryTableStrategy::TEMP_SUFFIX;
        $tempTableName = $tablePrefix . StrategyInterface::TMP_SUFFIX;

        $this->tableStrategyMock->expects($this->once())->method('getUseIdxTable')->willReturn(false);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->with('indexer')
            ->willReturn($connectionMock);
        $this->resourceMock
            ->method('getTableName')
            ->withConsecutive([$expectedResult], [$tempTableName])
            ->willReturnOnConsecutiveCalls($expectedResult, $tempTableName);
        $connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedResult, $tempTableName, true);

        $this->assertEquals($expectedResult, $this->model->prepareTableName($tablePrefix));
    }
}
