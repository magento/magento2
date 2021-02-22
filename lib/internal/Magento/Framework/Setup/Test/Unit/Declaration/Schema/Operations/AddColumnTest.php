<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Operations;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers\MigrateDataFrom;
use Magento\Framework\Setup\Declaration\Schema\Db\Statement;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\ElementHistoryFactory;
use Magento\Framework\Setup\Declaration\Schema\Operations\AddColumn;
use Magento\Framework\Setup\Declaration\Schema\Operations\AddComplexElement;
use Magento\Framework\Setup\Declaration\Schema\Operations\DropElement;

/**
 * Test for AddColumn.
 *
 * @package Magento\Framework\Setup\Test\Unit\Declaration\Schema\Operations
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddColumnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AddColumn
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var DefinitionAggregator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $definitionAggregatorMock;

    /**
     * @var DbSchemaWriterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dbSchemaWriterMock;

    /**
     * @var ElementFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $elementFactoryMock;

    /**
     * @var ElementHistoryFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $elementHistoryFactoryMock;

    /**
     * @var AddComplexElement|\PHPUnit\Framework\MockObject\MockObject
     */
    private $addComplexElementMock;

    /**
     * @var DropElement|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dropElementMock;

    /** @var MigrateDataFrom|\PHPUnit\Framework\MockObject\MockObject */
    private $migrateDataTrigger;

    protected function setUp(): void
    {
        $this->definitionAggregatorMock = $this->getMockBuilder(DefinitionAggregator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbSchemaWriterMock = $this->getMockBuilder(DbSchemaWriterInterface::class)
            ->getMockForAbstractClass();
        $this->elementFactoryMock = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->elementHistoryFactoryMock = $this->getMockBuilder(ElementHistoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addComplexElementMock = $this->getMockBuilder(AddComplexElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dropElementMock = $this->getMockBuilder(DropElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->migrateDataTrigger = $this->getMockBuilder(MigrateDataFrom::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            AddColumn::class,
            [
                'definitionAggregator' => $this->definitionAggregatorMock,
                'dbSchemaWriter' => $this->dbSchemaWriterMock,
                'elementFactory' => $this->elementFactoryMock,
                'elementHistoryFactory' => $this->elementHistoryFactoryMock,
                'addComplexElement' => $this->addComplexElementMock,
                'dropElement' => $this->dropElementMock,
                'triggers' => [
                    'migrateDataFrom' => $this->migrateDataTrigger
                ]
            ]
        );
    }

    /**
     * @return Column
     */
    private function prepareColumn()
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
            true,
            0,
            'Azaza',
            'migrateDataFrom(v)'
        );
        $table->addColumns([$column]);
        return $column;
    }

    public function testDoOperation()
    {
        $addComplexStatement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dropComplexElement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $callback = function () {
        };
        $column = $this->prepareColumn();
        $elementHistory = new ElementHistory($column);
        $definition = '`int` INT(11) NOT NULL DEFAULT 0 Comment "Azaza"';
        $this->definitionAggregatorMock->expects(self::once())
            ->method('toDefinition')
            ->with($column)
            ->willReturn($definition);
        $this->migrateDataTrigger->expects(self::once())
            ->method('isApplicable')
            ->with('migrateDataFrom(v)')
            ->willReturn(true);
        $this->migrateDataTrigger->expects(self::once())
            ->method('getCallback')
            ->with($elementHistory)
            ->willReturn($callback);
        $statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement->expects(self::once())
            ->method('addTrigger')
            ->with($callback);
        $this->dbSchemaWriterMock->expects(self::once())
            ->method('addElement')
            ->with('int', 'default', 'table', $definition, 'column')
            ->willReturn($statement);
        $index = new Index('index', 'index', $column->getTable(), [$column], 'btree', 'index');
        $this->elementFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($index);
        $indexHistory = new ElementHistory($index);
        $statement->expects(self::once())
            ->method('getTriggers')
            ->willReturn([$callback]);
        $this->elementHistoryFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($indexHistory);
        $this->addComplexElementMock->expects(self::once())
            ->method('doOperation')
            ->with($indexHistory)
            ->willReturn([$addComplexStatement]);
        $this->dropElementMock->expects(self::once())
            ->method('doOperation')
            ->with($indexHistory)
            ->willReturn([$dropComplexElement]);
        $resetAIStatement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbSchemaWriterMock->expects(self::once())
            ->method('resetAutoIncrement')
            ->willReturn($resetAIStatement);
        self::assertEquals(
            [$addComplexStatement, $statement, $dropComplexElement, $resetAIStatement],
            $this->model->doOperation($elementHistory)
        );
    }
}
