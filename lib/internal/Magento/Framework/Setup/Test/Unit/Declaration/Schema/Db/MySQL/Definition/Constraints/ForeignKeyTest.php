<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Constraints;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Constraints\ForeignKey;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Foreign Key constraint definition.
 *
 */
class ForeignKeyTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ForeignKey
     */
    private $foreignKey;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->foreignKey = $this->objectManager->getObject(
            ForeignKey::class,
            [
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinition()
    {
        /** @var Reference|MockObject $constraint */
        $constraint = $this->getMockBuilder(Reference::class)
            ->disableOriginalConstructor()
            ->getMock();
        $columnMock = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        $refColumnMock = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $tableMock = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $refTableMock = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        $columnMock->expects($this->any())->method('getName')->willReturn('col_name');
        $refColumnMock->expects($this->any())->method('getName')->willReturn('ref_col_name');
        $constraint->expects($this->any())->method('getName')->willReturn('fk_name');
        $constraint->expects($this->any())->method('getOnDelete')->willReturn('CASCADE');
        $tableMock->expects($this->any())->method('getResource')->willReturn('default');
        $constraint->expects($this->any())->method('getTable')->willReturn($tableMock);
        $refTableMock->expects($this->any())->method('getName')->willReturn('ref_table');
        $constraint->expects($this->any())->method('getReferenceTable')->willReturn($refTableMock);
        $constraint->expects($this->any())->method('getColumn')->willReturn($columnMock);
        $constraint->expects($this->any())->method('getReferenceColumn')->willReturn($refColumnMock);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('default')
            ->willReturn($adapterMock);
        $this->resourceConnectionMock->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $adapterMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnMap(
                [
                    ['fk_name', false, '`fk_name`'],
                    ['col_name', false, '`col_name`'],
                    ['ref_table', false, '`ref_table`'],
                    ['`ref_table`', false, '`ref_table`'],
                    ['ref_col_name', false, '`ref_col_name`'],
                ]
            );

        $this->assertEquals(
            'CONSTRAINT `fk_name` FOREIGN KEY (`col_name`) REFERENCES `ref_table` (`ref_col_name`)  ON DELETE CASCADE',
            $this->foreignKey->toDefinition($constraint)
        );
    }

    /**
     * Test from definition conversion.
     *
     * @param array $definition
     * @param array $expectedDefinition
     * @dataProvider definitionDataProvider()
     */
    public function testFromDefinition($definition, $expectedDefinition)
    {
        $result = $this->foreignKey->fromDefinition(['Create Table' => $definition]);
        $this->assertEquals($expectedDefinition, $result);
    }

    /**
     * @return array
     */
    public function definitionDataProvider()
    {
        return [
            [
                'definition' => 'CREATE TABLE `table_name` ('
                    . '`col_name` INT(10) UNSIGNED NOT NULL COMMENT \'column name\','
                    . 'INDEX `TEST_INDEX` (`col_name`),'
                    . 'CONSTRAINT `fk_name` FOREIGN KEY (`col_name`) '
                    . 'REFERENCES `ref_table` (`ref_col_name`)  ON DELETE CASCADE',
                'excpectedDefiniton' => [
                    'fk_name' => [
                        'type' => Reference::TYPE,
                        'name' => 'fk_name',
                        'column' => 'col_name',
                        'referenceTable' => 'ref_table',
                        'referenceColumn' => 'ref_col_name',
                        'onDelete' => 'CASCADE'
                    ]
                ],
            ],
            [
                'definition' => 'CREATE TABLE `table_name` ('
                    . '`col_name` INT(10) UNSIGNED NOT NULL COMMENT \'column name\','
                    . 'INDEX `TEST_INDEX` (`col_name`),'
                    . 'CONSTRAINT `fk_name` FOREIGN KEY(`col_name`)'
                    . 'REFERENCES `ref_table`(`ref_col_name`)ON DELETE NO ACTION',
                'excpectedDefiniton' => [
                    'fk_name' => [
                        'type' => Reference::TYPE,
                        'name' => 'fk_name',
                        'column' => 'col_name',
                        'referenceTable' => 'ref_table',
                        'referenceColumn' => 'ref_col_name',
                        'onDelete' => 'NO ACTION'
                    ]
                ]
            ],
            [
                'definition' => 'CREATE TABLE `table_name` ('
                    . '`column_name` INT(10) UNSIGNED NOT NULL COMMENT \'column name\','
                    . 'INDEX `TEST_INDEX` (`col_name`),'
                    . 'CONSTRAINT `fk_name` FOREIGN KEY(`column_name`)'
                    . 'REFERENCES `ref_table`(`ref_col_name`)ON DELETE SET DEFAULT',
                'excpectedDefiniton' => [
                    'fk_name' => [
                        'type' => Reference::TYPE,
                        'name' => 'fk_name',
                        'column' => 'column_name',
                        'referenceTable' => 'ref_table',
                        'referenceColumn' => 'ref_col_name',
                        'onDelete' => 'SET DEFAULT'
                    ]
                ]
            ],
            [
                'definition' => 'CREATE TABLE `table_name` ('
                    . '`column_name` INT(10) UNSIGNED NOT NULL COMMENT \'column name\','
                    . 'INDEX `TEST_INDEX` (`col_name`),'
                    . 'CONSTRAINT `fk_name` FOREIGN KEY(`column_name`)'
                    . 'REFERENCES `ref_table`(`ref_col_name`)ON DELETE SET NULL',
                'excpectedDefiniton' => [
                    'fk_name' => [
                        'type' => Reference::TYPE,
                        'name' => 'fk_name',
                        'column' => 'column_name',
                        'referenceTable' => 'ref_table',
                        'referenceColumn' => 'ref_col_name',
                        'onDelete' => 'SET NULL'
                    ]
                ]
            ],
            [
                'definition' => 'CREATE TABLE `table_name` ('
                    . '`column_name` INT(10) UNSIGNED NOT NULL COMMENT \'column name\','
                    . 'INDEX `TEST_INDEX` (`col_name`),'
                    . 'CONSTRAINT `fk_name` FOREIGN KEY(`column_name`)'
                    . 'REFERENCES `ref_table`(`ref_col_name`)ON DELETE RESTRICT ON UPDATE RESTRICT',
                'excpectedDefiniton' => [
                    'fk_name' => [
                        'type' => Reference::TYPE,
                        'name' => 'fk_name',
                        'column' => 'column_name',
                        'referenceTable' => 'ref_table',
                        'referenceColumn' => 'ref_col_name',
                        'onDelete' => 'RESTRICT'
                    ]
                ]
            ],
        ];
    }
}
