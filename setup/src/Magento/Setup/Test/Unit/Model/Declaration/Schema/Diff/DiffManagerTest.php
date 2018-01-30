<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Diff;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Diff\Diff;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

class DiffManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Setup\Model\Declaration\Schema\Diff\DiffManager */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Setup\Model\Declaration\Schema\Comparator|\PHPUnit_Framework_MockObject_MockObject */
    protected $comparatorMock;

    protected function setUp()
    {
        $this->comparatorMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Comparator::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Setup\Model\Declaration\Schema\Diff\DiffManager::class,
            [
                'comparator' => $this->comparatorMock
            ]
        );
    }

    public function testShouldBeCreated()
    {
        $elements = ['first' => new \stdClass(), 'second' => new \stdClass()];
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $element = new Column('third', 'int', $table);
        $existingElement = new Column('second', 'int', $table);
        self::assertTrue($this->model->shouldBeCreated($elements, $element));
        self::assertFalse($this->model->shouldBeCreated($elements, $existingElement));
    }

    public function testRegisterModification()
    {
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $element = new Column('third', 'int', $table);
        $generatedElement = new Column('third', 'int', $table, 'Previous column');
        $diff->expects(self::once())
            ->method('register')
            ->with($element, 'modify_column', $generatedElement);
        $this->model->registerModification($diff, $element, $generatedElement);
    }

    public function testRegisterIndexModification()
    {
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $column = new Column('third', 'int', $table, 'Previous column');
        $index = new Index('index_type', 'index', $table, [$column], 'btree');
        $generatedIndex = new Index('index_type', 'index', $table, [$column], 'hash');
        $diff->expects(self::exactly(2))
            ->method('register')
            ->withConsecutive([$generatedIndex, 'drop_element', $generatedIndex], [$index, 'add_complex_element']);
        $this->model->registerModification($diff, $index, $generatedIndex);
    }

    public function testRegisterRemovalReference()
    {
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $refTable = new Table(
            'ref_table',
            'ref_table',
            'table',
            'default',
            'innodb'
        );
        $column = new Column('third', 'int', $table, 'Previous column');
        $reference = new Reference('ref', 'foreign', $table, $column, $refTable, $column, 'CASCADE');
        $diff->expects(self::exactly(2))
            ->method('register')
            ->withConsecutive(
                [$reference, 'drop_reference', $reference, 'ref_table'],
                [$table, 'drop_table', $table]
            );
        $this->model->registerRemoval($diff, [$reference, $table]);
    }

    public function testRegisterCreation()
    {
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $column = new Column('third', 'int', $table, 'Previous column');
        $reference = new Reference('ref', 'foreign', $table, $column, $table, $column, 'CASCADE');
        $diff->expects(self::exactly(3))
            ->method('register')
            ->withConsecutive(
                [$table, 'create_table'],
                [$column, 'add_column'],
                [$reference, 'add_complex_element']
            );
        $this->model->registerCreation($diff, $table);
        $this->model->registerCreation($diff, $column);
        $this->model->registerCreation($diff, $reference);
    }

    public function testRegisterTableModificationWhenChangeResource()
    {
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $generateTable = new Table(
            'name',
            'name',
            'table',
            'sales',
            'innodb'
        );
        $diff->expects(self::once())
            ->method('register')
            ->with($table, 'recreate_table', $generateTable);
        $this->model->registerTableModification($table, $generateTable, $diff);
    }

    public function testRegisterTableModificationWhenChangeEngine()
    {
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $generateTable = new Table(
            'name',
            'name',
            'table',
            'default',
            'memory'
        );
        $diff->expects(self::once())
            ->method('register')
            ->with($table, 'modify_table', $generateTable);
        $this->model->registerTableModification($table, $generateTable, $diff);
    }
}
