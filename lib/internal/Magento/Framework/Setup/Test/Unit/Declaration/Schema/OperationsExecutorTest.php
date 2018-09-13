<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementAggregator;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementAggregatorFactory;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementFactory;
use Magento\Framework\Setup\Declaration\Schema\Diff\DiffInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\Operations\CreateTable;
use Magento\Framework\Setup\Declaration\Schema\Operations\DropElement;
use Magento\Framework\Setup\Declaration\Schema\Sharding;

/**
 * Test for OperationsExecutor.
 *
 * @package Magento\Framework\Setup\Test\Unit\Declaration\Schema
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OperationsExecutorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Setup\Declaration\Schema\OperationsExecutor
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Sharding|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shardingMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var StatementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statementFactoryMock;

    /**
     * @var DbSchemaWriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbSchemaWriterMock;

    /**
     * @var StatementAggregatorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statementAggregatorFactoryMock;

    /**
     * @var CreateTable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $createTableOperation;

    /**
     * @var DropElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dropElement;

    protected function setUp()
    {
        $this->shardingMock = $this->getMockBuilder(Sharding::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statementFactoryMock = $this->getMockBuilder(StatementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbSchemaWriterMock = $this->getMockBuilder(DbSchemaWriterInterface::class)
            ->getMockForAbstractClass();
        $this->statementAggregatorFactoryMock = $this->getMockBuilder(StatementAggregatorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createTableOperation = $this->getMockBuilder(CreateTable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createTableOperation->expects(self::exactly(2))
            ->method('getOperationName')
            ->willReturn('create_table');
        $this->dropElement = $this->getMockBuilder(DropElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Setup\Declaration\Schema\OperationsExecutor::class,
            [
                'operations' => [
                    'create_table' => $this->createTableOperation,
                    'drop_element' => $this->dropElement
                ],
                'dataSaviorsCollection' => [],
                'sharding' => $this->shardingMock,
                'resourceConnection' => $this->resourceConnectionMock,
                'statementFactory' => $this->statementFactoryMock,
                'dbSchemaWriter' => $this->dbSchemaWriterMock,
                'statementAggregatorFactory' => $this->statementAggregatorFactoryMock
            ]
        );
    }

    /**
     * @return Table
     */
    private function prepareTable()
    {
        $table = new Table(
            'table',
            'table',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf-8',
            ''
        );
        $column = new Integer(
            'int',
            'int',
            $table,
            11,
            false,
            false,
            false
        );
        $table->addColumns([$column]);
        return $table;
    }

    public function testExecute()
    {
        /** @var DiffInterface|\PHPUnit_Framework_MockObject_MockObject $diff */
        $diff = $this->getMockBuilder(DiffInterface::class)
            ->getMock();
        $this->shardingMock->expects(self::exactly(2))
            ->method('getResources')
            ->willReturn(['default']);
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock->expects(self::exactly(3))
            ->method('getConnection')
            ->with('default')
            ->willReturn($connectionMock);
        $statementAggregator = $this->getMockBuilder(StatementAggregator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statementAggregatorFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($statementAggregator);
        $elementHistory = new ElementHistory($this->prepareTable());
        $tablesHistories = [
            'table' => [
                'create_table' => [$elementHistory]
            ]
        ];
        $this->createTableOperation->expects(self::once())
            ->method('doOperation')
            ->with($elementHistory)
            ->willReturn(['TABLE table (`int` INT(11))']);
        $statementAggregator->expects(self::once())
            ->method('addStatements')
            ->with(['TABLE table (`int` INT(11))']);
        $this->dbSchemaWriterMock->expects(self::once())
            ->method('compile')
            ->with($statementAggregator);
        $diff->expects(self::once())
            ->method('getAll')
            ->willReturn($tablesHistories);
        $this->dropElement->expects(self::at(0))
            ->method('doOperation');
        $this->model->execute($diff, []);
    }
}
