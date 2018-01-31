<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Operations;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\DDL\Triggers\MigrateDataFrom;
use Magento\Setup\Model\Declaration\Schema\Db\Statement;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementFactory;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\ElementHistoryFactory;
use Magento\Setup\Model\Declaration\Schema\Operations\AddColumn;
use Magento\Setup\Model\Declaration\Schema\Operations\AddComplexElement;
use Magento\Setup\Model\Declaration\Schema\Operations\DropElement;

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
     * @var DefinitionAggregator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $definitionAggregatorMock;

    /**
     * @var DbSchemaWriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbSchemaWriterMock;

    /**
     * @var ElementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementFactoryMock;

    /**
     * @var ElementHistoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementHistoryFactoryMock;

    /**
     * @var AddComplexElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addComplexElementMock;

    /**
     * @var DropElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dropElementMock;

    /** @var MigrateDataFrom|\PHPUnit_Framework_MockObject_MockObject */
    private $migrateDataTrigger;

    protected function setUp()
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
        $table = new Table('table', 'table', 'table', 'default', 'innodb');
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
        $callback = function () {};
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
            ->with($column)
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
        $index = new Index('index', 'index', $column->getTable(), [$column], 'btree');
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
