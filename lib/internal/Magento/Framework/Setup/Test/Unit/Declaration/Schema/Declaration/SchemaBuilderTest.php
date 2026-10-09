<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Declaration;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Declaration\SchemaBuilder;
use Magento\Framework\Setup\Declaration\Schema\Declaration\TableElement\ElementNameResolver;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationComposite;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\Sharding;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * @var BooleanUtils|Stub
     */
    private $booleanUtilsMock;

    /**
     * @var Sharding|Stub
     */
    private $shardingMock;

    /**
     * @var ValidationComposite|Stub
     */
    private $validationCompositeMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var SqlVersionProvider|Stub
     */
    private $sqlVersionProvider;

    protected function setUp(): void
    {
        $this->elementFactoryMock = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->booleanUtilsMock = $this->createStub(BooleanUtils::class);
        $this->shardingMock = $this->createStub(Sharding::class);
        $this->validationCompositeMock = $this->createStub(ValidationComposite::class);
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sqlVersionProvider = $this->createStub(SqlVersionProvider::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            SchemaBuilder::class,
            [
                'elementFactory' => $this->elementFactoryMock,
                'booleanUtils' => new BooleanUtils(),
                'sharding' => $this->shardingMock,
                'validationComposite' => $this->validationCompositeMock,
                'resourceConnection' => $this->resourceConnectionMock,
                'elementNameResolver' => $this->createStub(ElementNameResolver::class),
                'sqlVersionProvider' => $this->sqlVersionProvider
            ]
        );
    }

    /**
     * @return array
     */
    public static function tablesProvider()
    {
        return [
            [
                [
                    'first_table' => [
                        'name' => 'first_table',
                        'engine' => 'innodb',
                        'resource' => 'default',
                        'column' => [
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
                            'some_disabled_column' => [
                                'name' => 'some_disabled_column',
                                'disabled' => 'true',
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
                        'constraint' => [
                            'some_foreign_key' => [
                                'name' => 'some_foreign_key',
                                'type' => 'foreign',
                                'column' => 'foreign_column',
                                'table' => 'first_table',
                                'referenceTable' => 'second_table',
                                'referenceColumn' => 'ref_column'
                            ],
                            'PRIMARY' => [
                                'name' => 'PRIMARY',
                                'type' => 'primary',
                                'column' => [
                                    'first_column'
                                ]
                            ]
                        ]
                    ],
                    'second_table' => [
                        'name' => 'second_table',
                        'engine' => 'innodb',
                        'resource' => 'default',
                        'column' => [
                            'ref_column' => [
                                'name' => 'ref_column',
                                'type' => 'int',
                                'padding' => 10,
                                'nullable' => false
                            ],
                        ],
                        'index' => [
                            'FIRST_INDEX' => [
                                'name' => 'FIRST_INDEX',
                                'column' => [
                                    'ref_column'
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Create table
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
     * Create PK constraint.
     *
     * @param Table $table
     * @param array $columns
     * @param string $nameWithoutPrefix
     * @return Internal
     */
    private function createPrimaryConstraint(Table $table, array $columns, $nameWithoutPrefix = 'PRIMARY')
    {
        return new Internal(
            'PRIMARY',
            'primary',
            $table,
            $nameWithoutPrefix,
            $columns
        );
    }

    /**
     * Create index.
     *
     * @param string $indexName
     * @param Table $table
     * @param array $columns
     * @param string|null $nameWithoutPrefix
     * @return Index
     */
    private function createIndex($indexName, Table $table, array $columns, $nameWithoutPrefix = null)
    {
        return new Index(
            $indexName,
            'index',
            $table,
            $columns,
            'btree',
            $nameWithoutPrefix ?: $indexName
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

    /**     * @param array $tablesData
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws LocalizedException
     */
    #[DataProvider('tablesProvider')]
    public function testBuild(array $tablesData)
    {
        $table = $this->createTable('first_table');
        $refTable = $this->createTable('second_table');
        $refColumn = $this->createIntegerColumn('ref_column', $refTable);
        $index = $this->createIndex('PRE_FIRST_INDEX', $table, [$refColumn], 'FIRST_INDEX');
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
        $firstTableColumns = [$firstColumn, $foreignColumn, $timestampColumn];
        $firstTableConstraints = [$foreignKey, $primaryKey];
        $table->addColumns($firstTableColumns);
        $table->addConstraints($firstTableConstraints);
        $this->elementFactoryMock->expects(self::exactly(9))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $table,
                $firstColumn,
                $foreignColumn,
                $timestampColumn,
                $refTable,
                $refColumn,
                $index,
                $foreignKey,
                $primaryKey
            );
        $resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Schema $schema */
        $schema = $this->objectManagerHelper->getObject(
            Schema::class,
            ['resourceConnection' => $resourceConnectionMock]
        );
        $this->resourceConnectionMock->expects(self::once())
            ->method('getTableName')
            ->willReturn('second_table');
        $resourceConnectionMock->expects(self::exactly(6))
            ->method('getTableName')
            ->willReturnCallback(
                function ($arg1) {
                    if ($arg1 == 'first_table') {
                        return 'first_table';
                    } elseif ($arg1 == 'second_table') {
                        return 'second_table';
                    }
                }
            );
        $this->model->addTablesData($tablesData);
        $this->model->build($schema);
    }

    public function testBuildDropsIndexCoveredByUniqueConstraintWithSameName(): void
    {
        $table = $this->createTable('some_table');
        $firstColumn = $this->createIntegerColumn('first_column', $table);
        $secondColumn = $this->createIntegerColumn('second_column', $table);
        $index = $this->createIndex('SOME_TABLE_FIRST_COLUMN_SECOND_COLUMN', $table, [$firstColumn, $secondColumn]);
        $unique = new Internal(
            'SOME_TABLE_FIRST_COLUMN_SECOND_COLUMN',
            'unique',
            $table,
            'SOME_TABLE_FIRST_COLUMN_SECOND_COLUMN',
            [$firstColumn, $secondColumn]
        );
        $this->elementFactoryMock->expects(self::exactly(5))
            ->method('create')
            ->willReturnOnConsecutiveCalls($table, $firstColumn, $secondColumn, $index, $unique);
        $this->resourceConnectionMock->expects(self::never())->method('getTableName');

        $this->model->addTablesData(
            $this->getCollidingTableData(['first_column', 'second_column'], ['first_column', 'second_column'])
        );
        $this->model->build($this->createSchema());

        self::assertSame([], $table->getIndexes());
        self::assertSame(['SOME_TABLE_FIRST_COLUMN_SECOND_COLUMN' => $unique], $table->getConstraints());
    }

    /**
     * @return array
     */
    public static function unresolvableNameCollisionProvider(): array
    {
        return [
            'fulltext index' => ['fulltext', ['first_column', 'second_column'], ['first_column', 'second_column']],
            'hash index' => ['hash', ['first_column', 'second_column'], ['first_column', 'second_column']],
            'different columns joined to the same name' => [
                'btree',
                ['first_column_second', 'column'],
                ['first_column', 'second_column'],
            ],
        ];
    }

    #[DataProvider('unresolvableNameCollisionProvider')]
    public function testBuildRejectsUnresolvableIndexNameCollision(
        string $indexType,
        array $indexColumnNames,
        array $uniqueColumnNames
    ): void {
        $name = 'SOME_TABLE_FIRST_COLUMN_SECOND_COLUMN';
        $table = $this->createTable('some_table');
        $columns = [];
        foreach (array_unique(array_merge($indexColumnNames, $uniqueColumnNames)) as $columnName) {
            $columns[$columnName] = $this->createIntegerColumn($columnName, $table);
        }
        $pick = static fn (array $names): array => array_map(static fn (string $n) => $columns[$n], $names);
        $index = new Index($name, 'index', $table, $pick($indexColumnNames), $indexType, $name);
        $unique = new Internal($name, 'unique', $table, $name, $pick($uniqueColumnNames));
        $this->elementFactoryMock->expects(self::exactly(count($columns) + 3))
            ->method('create')
            ->willReturnOnConsecutiveCalls(...[$table, ...array_values($columns), $index, $unique]);
        $this->resourceConnectionMock->expects(self::never())->method('getTableName');

        $this->model->addTablesData(
            $this->getCollidingTableData($indexColumnNames, $uniqueColumnNames, $indexType)
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('resolve to the same database name "' . $name . '"');
        $this->model->build($this->createSchema());
    }

    private function getCollidingTableData(
        array $indexColumnNames,
        array $uniqueColumnNames,
        string $indexType = 'btree'
    ): array {
        $columns = [];
        foreach (array_unique(array_merge($indexColumnNames, $uniqueColumnNames)) as $columnName) {
            $columns[$columnName] = ['name' => $columnName, 'type' => 'int', 'padding' => 10];
        }

        return [
            'some_table' => [
                'name' => 'some_table',
                'resource' => 'default',
                'column' => $columns,
                'index' => [
                    'SOME_TABLE_INDEX' => [
                        'referenceId' => 'SOME_TABLE_INDEX',
                        'indexType' => $indexType,
                        'column' => $indexColumnNames,
                    ],
                ],
                'constraint' => [
                    'SOME_TABLE_UNQ' => [
                        'referenceId' => 'SOME_TABLE_UNQ',
                        'type' => 'unique',
                        'column' => $uniqueColumnNames,
                    ],
                ],
            ],
        ];
    }

    private function createSchema(): Schema
    {
        $resourceConnectionMock = $this->createStub(ResourceConnection::class);
        $resourceConnectionMock->method('getTableName')->willReturnArgument(0);

        return $this->objectManagerHelper->getObject(Schema::class, ['resourceConnection' => $resourceConnectionMock]);
    }
}
