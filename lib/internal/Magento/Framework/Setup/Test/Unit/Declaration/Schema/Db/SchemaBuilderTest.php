<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\SchemaBuilder;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\Sharding;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for SchemaBuilder.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SchemaBuilderTest extends TestCase
{
    /**
     * @var SchemaBuilder
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ElementFactory|MockObject
     */
    private $elementFactoryMock;

    /**
     * @var DbSchemaReaderInterface|MockObject
     */
    private $dbSchemaReaderMock;

    /**
     * @var Sharding|MockObject
     */
    private $shardingMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DB\Adapter\SqlVersionProvider
     */
    private $sqlVersionProvider;

    protected function setUp(): void
    {
        $this->elementFactoryMock = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbSchemaReaderMock = $this->getMockBuilder(DbSchemaReaderInterface::class)
            ->getMockForAbstractClass();
        $this->shardingMock = $this->getMockBuilder(Sharding::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sqlVersionProvider = $this->getMockBuilder(SqlVersionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            SchemaBuilder::class,
            [
                'elementFactory' => $this->elementFactoryMock,
                'dbSchemaReader' => $this->dbSchemaReaderMock,
                'sharding' => $this->shardingMock,
                'getDbVersion' => $this->sqlVersionProvider
            ]
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function dataProvider()
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
                            'default' => 'CURRENT_TIMESTAMP'
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
                            ],
                            'nameWithoutPrefix' => 'PRIMARY',
                        ]
                    ]
                ],
                'indexes' => [
                    'second_table' => [
                        'FIRST_INDEX' => [
                            'name' => 'FIRST_INDEX',
                            'nameWithoutPrefix' => 'FIRST_INDEX',
                            'column' => [
                                'ref_column'
                            ],
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
            'resource',
            'utf-8',
            'utf-8',
            ''
        );
    }

    /**
     * Create integer column with autoincrement.
     *
     * @param string $name
     * @param Table $table
     * @return Integer
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
     * @return Integer
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
            'PRIMARY',
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
            'btree',
            $indexName
        );
    }

    /**
     * Create timestamp column.
     *
     * @param string $name
     * @param Table $table
     * @return Timestamp
     */
    private function createTimestampColumn($name, Table $table)
    {
        return new Timestamp(
            $name,
            'timestamp',
            $table,
            'CURRENT_TIMESTAMP',
            false
        );
    }

    /**
     * @dataProvider dataProvider
     * @param array $columns
     * @param array $references
     * @param array $constraints
     * @param array $indexes
     */
    public function testBuild(array $columns, array $references, array $constraints, array $indexes)
    {
        $this->prepareSchemaMocks($columns, $references, $constraints, $indexes);
        $resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceConnectionMock->expects(self::any())
            ->method('getTableName')
            ->willReturnArgument(0);
        /** @var Schema $schema */
        $schema = $this->objectManagerHelper->getObject(
            Schema::class,
            ['resourceConnection' => $resourceConnectionMock]
        );
        $this->model->build($schema);
    }

    /**
     * WARNING! The expected exception type may differ depending on PHPUnit version.
     *
     * @dataProvider dataProvider
     * @param array $columns
     * @param array $references
     * @param array $constraints
     * @param array $indexes
     */
    public function testBuildUnknownIndexColumn(array $columns, array $references, array $constraints, array $indexes)
    {
        $indexes['second_table']['FIRST_INDEX']['column'][] = 'unknown_column';
        $this->prepareSchemaMocks($columns, $references, $constraints, $indexes);
        $resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Schema $schema */
        $schema = $this->objectManagerHelper->getObject(
            Schema::class,
            ['resourceConnection' => $resourceConnectionMock]
        );
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'User Warning: Column unknown_column does not exist for index/constraint FIRST_INDEX in table second_table.'
        );
        $this->model->build($schema);
    }

    /**
     * Prepare mocks for test.
     *
     * @param array $columns
     * @param array $references
     * @param array $constraints
     * @param array $indexes
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function prepareSchemaMocks(array $columns, array $references, array $constraints, array $indexes)
    {
        $withContext = [['first_table', 'default'], ['second_table', 'default']];
        $this->shardingMock->expects(self::once())
            ->method('getResources')
            ->willReturn(['default']);
        $this->dbSchemaReaderMock->expects(self::once())
            ->method('readTables')
            ->with('default')
            ->willReturn(['first_table', 'second_table']);
        $this->dbSchemaReaderMock->expects($this->any())
            ->method('getTableOptions')
            ->willReturnCallback(function ($withContext) {
                if (!empty($withContext)) {
                    static $callCount = 0;
                    if ($callCount == 0) {
                        $callCount++;
                        return ['engine' => 'innodb', 'comment' => '', 'charset' => 'utf-8', 'collation' => 'utf-8'];
                    } elseif ($callCount == 1) {
                        $callCount++;
                        return ['engine' => 'innodb', 'comment' => 'Not null comment',
                            'charset' => 'utf-8', 'collation' => 'utf-8'];
                    }
                }
            });
        $this->dbSchemaReaderMock->expects($this->any())
            ->method('readColumns')
            ->willReturnCallback(function ($withContext) use ($columns) {
                if (!empty($withContext)) {
                    static $callCount = 0;
                    if ($callCount == 0) {
                        $callCount++;
                        return $columns['first_table'];
                    } elseif ($callCount == 1) {
                        $callCount++;
                        return $columns['second_table'];
                    }
                }
            });
        $this->dbSchemaReaderMock->expects($this->any())
            ->method('readIndexes')
            ->willReturnCallback(function ($withContext) use ($indexes) {
                if (!empty($withContext)) {
                    static $callCount = 0;
                    if ($callCount == 0) {
                        $callCount++;
                        return [];
                    } elseif ($callCount == 1) {
                        $callCount++;
                        return $indexes['second_table'];
                    }
                }
            });
        $this->dbSchemaReaderMock->expects($this->any())
            ->method('readConstraints')
            ->willReturnCallback(function ($withContext) use ($constraints) {
                if (!empty($withContext)) {
                    static $callCount = 0;
                    if ($callCount == 0) {
                        $callCount++;
                        return $constraints['first_table'];
                    } elseif ($callCount == 1) {
                        $callCount++;
                        return [];
                    }
                }
            });
        $this->dbSchemaReaderMock->expects($this->any())
            ->method('readReferences')
            ->willReturnCallback(function ($withContext) use ($references) {
                if (!empty($withContext)) {
                    static $callCount = 0;
                    if ($callCount == 0) {
                        $callCount++;
                        return $references['first_table'];
                    } elseif ($callCount == 1) {
                        $callCount++;
                        return [];
                    }
                }
            });
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
            'some_foreign_key',
            $foreignColumn,
            $refTable,
            $refColumn,
            'CASCADE'
        );
        $table->addColumns([$firstColumn, $foreignColumn, $timestampColumn]);
        $table->addConstraints([$foreignKey, $primaryKey]);
        $this->elementFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function (
                $arg1,
                $arg2
            ) use (
                $table,
                $firstColumn,
                $foreignColumn,
                $timestampColumn,
                $primaryKey,
                $refTable,
                $refColumn,
                $index,
                $foreignKey
            ) {
                if ($arg1 == 'table' && $arg2['name'] == 'first_table') {
                    return $table;
                } elseif ($arg1 == 'int' && $arg2['name'] == 'first_column') {
                    return $firstColumn;
                } elseif ($arg1 == 'int' && $arg2['name'] == 'foreign_column') {
                    return $foreignColumn;
                } elseif ($arg1 == 'timestamp' && $arg2['name'] == 'second_column') {
                    return $timestampColumn;
                } elseif ($arg1 == 'primary' && $arg2['name'] == 'PRIMARY') {
                    return $primaryKey;
                } elseif ($arg1 == 'table' && $arg2['name'] == 'second_table') {
                    return $refTable;
                } elseif ($arg1 == 'int' && $arg2['name'] == 'ref_column') {
                    return $refColumn;
                } elseif ($arg1 == 'index' && $arg2['name'] == 'FIRST_INDEX') {
                    return $index;
                } elseif ($arg1 == 'foreign' && $arg2['name'] == 'some_foreign_key') {
                    return $foreignKey;
                }
            });
    }
}
