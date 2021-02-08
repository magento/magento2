<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Diff;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Diff\Diff;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Test diff manager methods
 */
class DiffManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Setup\Declaration\Schema\Diff\DiffManager
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Setup\Declaration\Schema\Comparator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $comparatorMock;

    protected function setUp(): void
    {
        $this->comparatorMock = $this->getMockBuilder(\Magento\Framework\Setup\Declaration\Schema\Comparator::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Setup\Declaration\Schema\Diff\DiffManager::class,
            [
                'comparator' => $this->comparatorMock
            ]
        );
    }

    public function testShouldBeCreated()
    {
        $elements = ['first' => new \stdClass(), 'second' => new \stdClass()];
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $element = new Column('third', 'int', $table);
        $existingElement = new Column('second', 'int', $table);
        self::assertTrue($this->model->shouldBeCreated($elements, $element));
        self::assertFalse($this->model->shouldBeCreated($elements, $existingElement));
    }

    public function testRegisterModification()
    {
        /** @var Diff|\PHPUnit\Framework\MockObject\MockObject $diff */
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $element = new Column('third', 'int', $table);
        $generatedElement = new Column('third', 'int', $table, 'Previous column');
        $diff->expects(self::once())
            ->method('register')
            ->with($element, 'modify_column', $generatedElement);
        $this->model->registerModification($diff, $element, $generatedElement);
    }

    public function testRegisterIndexModification()
    {
        /** @var Diff|\PHPUnit\Framework\MockObject\MockObject $diff */
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $column = new Column('third', 'int', $table, 'Previous column');
        $index = new Index('index_type', 'index', $table, [$column], 'btree', 'index_type');
        $generatedIndex = new Index('index_type', 'index', $table, [$column], 'hash', 'index_type');
        $diff->expects(self::exactly(2))
            ->method('register')
            ->withConsecutive([$generatedIndex, 'drop_element', $generatedIndex], [$index, 'add_complex_element']);
        $this->model->registerModification($diff, $index, $generatedIndex);
    }

    public function testRegisterRemovalReference()
    {
        /** @var Diff|\PHPUnit\Framework\MockObject\MockObject $diff */
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $refTable = new Table(
            'ref_table',
            'ref_table',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf-8',
            ''
        );
        $column = new Column('third', 'int', $table, 'Previous column');
        $reference = new Reference('ref', 'foreign', $table, 'ref', $column, $refTable, $column, 'CASCADE');
        $diff->expects(self::exactly(2))
            ->method('register')
            ->withConsecutive(
                [$reference, 'drop_reference', $reference],
                [$table, 'drop_table', $table]
            );
        $this->model->registerRemoval($diff, [$reference, $table]);
    }

    public function testRegisterCreation()
    {
        /** @var Diff|\PHPUnit\Framework\MockObject\MockObject $diff */
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $column = new Column('third', 'int', $table, 'Previous column');
        $reference = new Reference('ref', 'foreign', $table, 'ref', $column, $table, $column, 'CASCADE');
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
        /** @var Diff|\PHPUnit\Framework\MockObject\MockObject $diff */
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $generateTable = new Table(
            'name',
            'name',
            'table',
            'sales',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $diff->expects(self::once())
            ->method('register')
            ->with($table, 'recreate_table', $generateTable);
        $this->model->registerRecreation($table, $generateTable, $diff);
    }

    public function testRegisterTableModificationWhenChangeEngine()
    {
        /** @var Diff|\PHPUnit\Framework\MockObject\MockObject $diff */
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table = new Table(
            'name',
            'name',
            'table',
            'default',
            'innodb',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $generateTable = new Table(
            'name',
            'name',
            'table',
            'default',
            'memory',
            'utf-8',
            'utf_8_general_ci',
            ''
        );
        $diff->expects(self::once())
            ->method('register')
            ->with($table, 'modify_table', $generateTable);
        $this->model->registerTableModification($table, $generateTable, $diff);
    }
}
