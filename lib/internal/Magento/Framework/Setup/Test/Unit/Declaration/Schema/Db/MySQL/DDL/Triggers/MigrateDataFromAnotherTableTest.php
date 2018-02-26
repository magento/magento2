<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers\MigrateDataFromAnotherTable;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

class MigrateDataFromAnotherTableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var SelectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectFactoryMock;

    /**
     * @var MigrateDataFromAnotherTable
     */
    private $model;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->selectFactoryMock = $this->createMock(SelectFactory::class);
        $this->model = new MigrateDataFromAnotherTable(
            $this->resourceConnectionMock,
            $this->selectFactoryMock
        );
    }

    public function testTriggerTestTable()
    {
        $columnMock = $this->getMockBuilder(Column::class)
            ->setMethods(['getOnCreate', 'getName', 'getTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $columnMock->expects($this->any())
            ->method('getOnCreate')
            ->willReturn('migrateDataFromAnotherTable(source_table,source_column)');
        $columnMock->expects($this->any())->method('getName')->willReturn('target_column');

        $tableMock = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getResource'])
            ->getMock();
        $tableMock->expects($this->any())->method('getName')->willReturn('target_table');
        $tableMock->expects($this->any())->method('getResource')->willReturn('default');
        $columnMock->expects($this->any())->method('getTable')->willReturn($tableMock);
        $selectMock = $this->createMock(Select::class);

        $this->resourceConnectionMock->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $adapterMock = $this->createMock(AdapterInterface::class);
        $adapterMock->expects($this->any())
            ->method('insertFromSelect')
            ->with(
                $selectMock,
                'target_table'
            )->willReturn('INSERT FROM SELECT QUERY STRING');
        $adapterMock->expects($this->once())
            ->method('query')
            ->with('INSERT FROM SELECT QUERY STRING');
        $adapterMock->expects($this->once())
            ->method('isTableExists')
            ->with('source_table')
            ->willReturn(true);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->with('default')
            ->willReturn($adapterMock);
        $selectMock->expects($this->once())
            ->method('from')
            ->with(
                'source_table',
                ['target_column' => 'source_column'],
                null
            );

        $this->selectFactoryMock->expects($this->once())
            ->method('create')
            ->with($adapterMock)
            ->willReturn($selectMock);
        $this->model->getCallback($columnMock)();
    }

    /**
     * @expectedException \Magento\Framework\Setup\Exception
     * @expectedExceptionMessage Table `source_table` does not exist for connection `default`
     */
    public function testTriggerUnexistentTestTable()
    {
        $columnMock = $this->getMockBuilder(Column::class)
            ->setMethods(['getOnCreate', 'getName', 'getTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $columnMock->expects($this->any())
            ->method('getOnCreate')
            ->willReturn('migrateDataFromAnotherTable(source_table,source_column)');
        $columnMock->expects($this->any())->method('getName')->willReturn('target_column');

        $tableMock = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getResource'])
            ->getMock();
        $tableMock->expects($this->any())->method('getName')->willReturn('target_table');
        $tableMock->expects($this->any())->method('getResource')->willReturn('default');
        $columnMock->expects($this->any())->method('getTable')->willReturn($tableMock);
        $selectMock = $this->createMock(Select::class);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        $adapterMock = $this->createMock(AdapterInterface::class);
        $adapterMock->expects($this->never())->method('query');
        $adapterMock->expects($this->once())
            ->method('isTableExists')
            ->with('source_table')
            ->willReturn(false);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->with('default')
            ->willReturn($adapterMock);
        $selectMock->expects($this->once())
            ->method('from')
            ->with(
                'source_table',
                ['target_column' => 'source_column'],
                null
            );
        $this->selectFactoryMock->expects($this->once())
            ->method('create')
            ->with($adapterMock)
            ->willReturn($selectMock);
        $this->model->getCallback($columnMock)();
    }
}