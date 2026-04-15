<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Grid;
use Magento\Sales\Model\ResourceModel\Provider\Query\IdListBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdListBuilderTest extends TestCase
{
    /**
     * @var ResourceConnection&MockObject
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Select&MockObject
     */
    private $select;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->connection = $this->createStub(AdapterInterface::class);
        $this->select = $this->createMock(Select::class);

        $this->resourceConnection->method('getConnection')
            ->with('sales')
            ->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')
            ->willReturnCallback(static fn(string $table): string => $table);

        $this->connection->method('select')
            ->willReturn($this->select);
        $this->select->method('from')->willReturnSelf();
        $this->select->method('joinLeft')->willReturnSelf();
        $this->select->method('limit')->willReturnSelf();
    }

    public function testBuildWithEntityIdRangeAddsBoundaries(): void
    {
        $expectedWhereCalls = [
            ['main_table.entity_id > ?', 100],
            ['main_table.entity_id <= ?', 200],
            ['grid_table.entity_id IS NULL', null],
        ];
        $whereCallIndex = 0;
        $this->select->expects($this->exactly(3))
            ->method('where')
            ->willReturnCallback(
                function (string $condition, $value = null) use (&$whereCallIndex, $expectedWhereCalls) {
                    self::assertSame($expectedWhereCalls[$whereCallIndex][0], $condition);
                    self::assertSame($expectedWhereCalls[$whereCallIndex][1], $value);
                    $whereCallIndex++;

                    return $this->select;
                }
            );
        $this->select->expects($this->once())
            ->method('limit')
            ->with(Grid::BATCH_SIZE)
            ->willReturnSelf();

        $builder = new IdListBuilder($this->resourceConnection);
        $result = $builder->build('sales_order', 'sales_order_grid', 100, 200);

        self::assertSame($this->select, $result);
    }

    public function testBuildWithoutEntityIdRangeKeepsOriginalQuery(): void
    {
        $this->select->expects($this->once())
            ->method('where')
            ->with('grid_table.entity_id IS NULL')
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('limit')
            ->with(Grid::BATCH_SIZE)
            ->willReturnSelf();

        $builder = new IdListBuilder($this->resourceConnection);
        $result = $builder->build('sales_order', 'sales_order_grid');

        self::assertSame($this->select, $result);
    }
}
