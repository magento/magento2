<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\Declaration\Schema;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Db\StatementAggregator;
use Magento\Setup\Model\Declaration\Schema\Diff\DiffInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\Operations\CreateTable;
use Magento\Setup\Model\Declaration\Schema\Operations\DropElement;

class OperationsExecutorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Setup\Model\Declaration\Schema\OperationsExecutor */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \SafeReflectionClass|\PHPUnit_Framework_MockObject_MockObject */
    protected $safeReflectionClassMock;

    /** @var \Magento\Setup\Model\Declaration\Schema\Sharding|\PHPUnit_Framework_MockObject_MockObject */
    protected $shardingMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceConnectionMock;

    /** @var \Magento\Setup\Model\Declaration\Schema\Db\StatementFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $statementFactoryMock;

    /** @var \Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dbSchemaWriterMock;

    /** @var \Magento\Setup\Model\Declaration\Schema\Db\StatementAggregatorFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $statementAggregatorFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $createTableOperation;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dropElement;

    protected function setUp()
    {
        $this->shardingMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Sharding::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statementFactoryMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Db\StatementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbSchemaWriterMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface::class)
            ->getMockForAbstractClass();
        $this->statementAggregatorFactoryMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Db\StatementAggregatorFactory::class)
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
            \Magento\Setup\Model\Declaration\Schema\OperationsExecutor::class,
            [
                'operations' => [
                    'create_table' => $this->createTableOperation,
                    'drop_element' => $this->dropElement
                ],
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
        $table = new Table('table', 'table', 'table', 'default', 'innodb');
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
        $this->model->execute($diff);
    }
}
