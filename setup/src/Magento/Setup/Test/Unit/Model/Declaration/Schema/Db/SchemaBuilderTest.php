<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Db;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaReaderInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementFactory;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Sharding;

class SchemaBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\Declaration\Schema\Db\SchemaBuilder
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ElementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementFactoryMock;

    /**
     * @var DbSchemaReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbSchemaReaderMock;

    /**
     * @var Sharding|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shardingMock;

    protected function setUp()
    {
        $this->elementFactoryMock = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbSchemaReaderMock = $this->getMockBuilder(DbSchemaReaderInterface::class)
            ->getMockForAbstractClass();
        $this->shardingMock = $this->getMockBuilder(Sharding::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Setup\Model\Declaration\Schema\Db\SchemaBuilder::class,
            [
                'elementFactory' => $this->elementFactoryMock,
                'dbSchemaReader' => $this->dbSchemaReaderMock,
                'sharding' => $this->shardingMock
            ]
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProvider()
    {
        return [
            [
                'columns' => [
                    'first_table' => [
                        'first_column' => [
                            'name' => 'first_column',
                            'type' => 'int',
                            'padding' => 10,
                            'identity' => true,
                            'nullable' => false
                        ],
                        'foreign_column' => [
                            'name' => 'foreign_column',
                            'type' => 'int',
                            'padding' => 10,
                            'nullable' => false
                        ],
                        'second_column' => [
                            'name' => 'second_column',
                            'type' => 'timestamp',
                            'default' => 'CURRENT_TIMESTAMP',
                            'on_update' => true
                        ],
                    ],
                    'second_table' => [
                        'ref_column' => [
                            'name' => 'ref_column',
                            'type' => 'int',
                            'padding' => 10,
                            'nullable' => false
                        ],
                    ]
                ],
                'references' => [
                    'first_table' => [
                        'some_foreign_key' => [
                            'name' => 'some_foreign_key',
                            'type' => 'foreign',
                            'column' => 'foreign_column',
                            'table' => 'first_table',
                            'referenceTable' => 'second_table',
                            'referenceColumn' => 'ref_column'
                        ],
                    ]
                ],
                'constraints' => [
                    'first_table' => [
                        'PRIMARY' => [
                            'name' => 'PRIMARY',
                            'type' => 'primary',
                            'column' => [
                                'first_column'
                            ]
                        ]
                    ]
                ],
                'indexes' => [
                    'second_table' => [
                        'FIRST_INDEX' => [
                            'name' => 'FIRST_INDEX',
                            'column' => [
                                'ref_column'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create table.
     *
     * @param string $name
     * @return Table
     */
    private function createTable($name)
    {
        return new Table(
            $name,
            $name,
            'table',
            'default',
            'resource'
        );
    }

    /**
     * Create integer column with autoincrement.
     *
     * @param string $name
     * @param Table $table
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer
     */
    private function createIntegerAIColumn($name, Table $table)
    {
        return new Integer(
            $name,
            'int',
            $table,
            10,
            true,
            false,
            true
        );
    }

    /**
     * Create integer column.
     *
     * @param string $name
     * @param Table $table
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer
     */
    private function createIntegerColumn($name, Table $table)
    {
        return new Integer(
            $name,
            'int',
            $table,
            10
        );
    }

    /**
     * Create primary key constraint.
     *
     * @param Table $table
     * @param array $columns
     * @return Internal
     */
    private function createPrimaryConstraint(Table $table, array $columns)
    {
        return new Internal(
            'PRIMARY',
            'primary',
            $table,
            $columns
        );
    }

    /**
     * Create index.
     *
     * @param string $indexName
     * @param Table $table
     * @param array $columns
     * @return Index
     */
    private function createIndex($indexName, Table $table, array $columns)
    {
        return new Index(
            $indexName,
            'index',
            $table,
            $columns,
            'btree'
        );
    }


    /**
     * Create timestamp column.
     *
     * @param string $name
     * @param Table $table
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp
     */
    private function createTimestampColumn($name, Table $table)
    {
        return new Timestamp(
            $name,
            'timestamp',
            $table,
            'CURRENT_TIMESTAMP',
            false,
            true
        );
    }


    /**
     * @dataProvider dataProvider
     * @param array $columns
     * @param array $references
     * @param array $constraints
     * @param array $indexes
     */
    public function testBuild(array $columns,  array $references, array $constraints, array $indexes)
    {
        $withContext = [['first_table', 'default'], ['second_table', 'default']];
        $this->shardingMock->expects(self::once())
            ->method('getResources')
            ->willReturn(['default']);
        $this->dbSchemaReaderMock->expects(self::once())
            ->method('readTables')
            ->with('default')
            ->willReturn(['first_table', 'second_table']);
        $this->dbSchemaReaderMock->expects(self::exactly(2))
            ->method('getTableOptions')
            ->withConsecutive(...array_values($withContext))
            ->willReturnOnConsecutiveCalls(
                ['Engine' => 'innodb', 'Comment' => ''],
                ['Engine' => 'innodb', 'Comment' => 'Not null comment']
            );
        $this->dbSchemaReaderMock->expects(self::exactly(2))
            ->method('readColumns')
            ->withConsecutive(...array_values($withContext))
            ->willReturnOnConsecutiveCalls($columns['first_table'], $columns['second_table']);
        $this->dbSchemaReaderMock->expects(self::exactly(2))
            ->method('readIndexes')
            ->withConsecutive(...array_values($withContext))
            ->willReturnOnConsecutiveCalls([], $indexes['second_table']);
        $this->dbSchemaReaderMock->expects(self::exactly(2))
            ->method('readConstraints')
            ->withConsecutive(...array_values($withContext))
            ->willReturnOnConsecutiveCalls($constraints['first_table'], []);
        $this->dbSchemaReaderMock->expects(self::exactly(2))
            ->method('readReferences')
            ->withConsecutive(...array_values($withContext))
            ->willReturnOnConsecutiveCalls($references['first_table'], []);
        $table = $this->createTable('first_table');
        $refTable = $this->createTable('second_table');
        $refColumn = $this->createIntegerColumn('ref_column', $refTable);
        $index = $this->createIndex('FIRST_INDEX', $table, [$refColumn]);
        $refTable->addColumns([$refColumn]);
        $refTable->addIndexes([$index]);
        $firstColumn = $this->createIntegerAIColumn('first_column', $table);
        $foreignColumn = $this->createIntegerColumn('foreign_column', $table);
        $timestampColumn = $this->createTimestampColumn('second_column', $table);
        $primaryKey = $this->createPrimaryConstraint($table, [$firstColumn]);
        $foreignKey = new Reference(
            'some_foreign_key',
            'foreign',
            $table,
            $foreignColumn,
            $refTable,
            $refColumn,
            'CASCADE'
        );
        $table->addColumns([$firstColumn, $foreignColumn, $timestampColumn]);
        $table->addConstraints([$foreignKey, $primaryKey]);
        $this->elementFactoryMock->expects(self::exactly(9))
            ->method('create')
            ->withConsecutive(
                [
                    'table',
                    [
                        'name' =>'first_table',
                        'resource' => 'default',
                        'engine' => 'innodb',
                        'comment' => null
                    ]
                ],
                [
                    'int',
                    [
                        'name' => 'first_column',
                        'type' => 'int',
                        'table' => $table,
                        'padding' => 10,
                        'identity' => true,
                        'nullable' => false,
                    ]
                ],
                [
                    'int',
                    [
                        'name' => 'foreign_column',
                        'type' => 'int',
                        'table' => $table,
                        'padding' => 10,
                        'nullable' => false,
                    ]
                ],
                [
                    'timestamp',
                    [
                        'name' => 'second_column',
                        'type' => 'timestamp',
                        'table' => $table,
                        'default' => 'CURRENT_TIMESTAMP',
                        'on_update' => true,
                    ]
                ],
                [
                    'primary',
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [$firstColumn],
                        'table' => $table,
                        'column' => ['first_column'],
                    ]
                ],
                [
                    'table',
                    [
                        'name' =>'second_table',
                        'resource' => 'default',
                        'engine' => 'innodb',
                        'comment' => 'Not null comment'
                    ]
                ],
                [
                    'int',
                    [
                        'name' => 'ref_column',
                        'type' => 'int',
                        'table' => $refTable,
                        'padding' => 10,
                        'nullable' => false,
                    ]
                ],
                [
                    'index',
                    [
                        'name' => 'FIRST_INDEX',
                        'table' => $refTable,
                        'column' => ['ref_column'],
                        'columns' => [$refColumn],
                    ]
                ],
                [
                    'foreign',
                    [
                        'name' => 'some_foreign_key',
                        'type' => 'foreign',
                        'column' => $foreignColumn,
                        'table' => $table,
                        'referenceTable' => $refTable,
                        'referenceColumn' => $refColumn,
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $table,
                $firstColumn,
                $foreignColumn,
                $timestampColumn,
                $primaryKey,
                $refTable,
                $refColumn,
                $index,
                $foreignKey
            );
        $resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Schema $schema */
        $schema = $this->objectManagerHelper->getObject(
            Schema::class,
            ['resourceConnection' => $resourceConnectionMock]
        );
        $this->model->build($schema);
    }
}
