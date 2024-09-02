<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Diff;

use Magento\Framework\Setup\Declaration\Schema\Comparator;
use Magento\Framework\Setup\Declaration\Schema\Diff\Diff;
use Magento\Framework\Setup\Declaration\Schema\Diff\DiffManager;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test diff manager methods
 */
class DiffManagerTest extends TestCase
{
    /**
     * @var DiffManager
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Comparator|MockObject
     */
    private $comparatorMock;

    protected function setUp(): void
    {
        $this->comparatorMock = $this->getMockBuilder(Comparator::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            DiffManager::class,
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
        /** @var Diff|MockObject $diff */
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
        /** @var Diff|MockObject $diff */
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
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($generatedIndex, $index) {
                    if ($arg1 === $generatedIndex && $arg2 === 'drop_element' && $arg3 === $generatedIndex) {
                        return null;
                    } elseif ($arg1 === $index && $arg2 === 'add_complex_element') {
                        return null;
                    }
                }
            );
        $this->model->registerModification($diff, $index, $generatedIndex);
    }

    public function testRegisterRemovalReference()
    {
        /** @var Diff|MockObject $diff */
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
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($reference, $table) {
                    if ($arg1 == $reference && $arg2 == 'drop_reference' && $arg3 == $reference) {
                        return null;
                    } elseif ($arg1 == $table && $arg2 == 'drop_table' && $arg3 == $table) {
                        return null;
                    }
                }
            );
        $this->model->registerRemoval($diff, [$reference, $table]);
    }

    public function testRegisterCreation()
    {
        /** @var Diff|MockObject $diff */
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
            ->willReturnCallback(
                function ($arg1, $arg2) use ($table, $column, $reference) {
                    if ($arg1 == $table && $arg2 == 'create_table') {
                        return null;
                    } elseif ($arg1 == $column && $arg2 == 'add_column') {
                        return null;
                    } elseif ($arg1 == $reference && $arg2 == 'add_complex_element') {
                        return null;
                    }
                }
            );
        $this->model->registerCreation($diff, $table);
        $this->model->registerCreation($diff, $column);
        $this->model->registerCreation($diff, $reference);
    }

    public function testRegisterTableModificationWhenChangeResource()
    {
        /** @var Diff|MockObject $diff */
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
        /** @var Diff|MockObject $diff */
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
