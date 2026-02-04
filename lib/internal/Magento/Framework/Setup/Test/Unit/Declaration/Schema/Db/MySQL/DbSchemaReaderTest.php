<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DbSchemaReader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for DbSchemaReader
 */
class DbSchemaReaderTest extends TestCase
{
    /**
     * @var DbSchemaReader
     */
    private $dbSchemaReader;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var DefinitionAggregator|MockObject
     */
    private $definitionAggregatorMock;

    /**
     * @var SqlVersionProvider|MockObject
     */
    private $sqlVersionProviderMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->definitionAggregatorMock = $this->createMock(DefinitionAggregator::class);
        $this->sqlVersionProviderMock = $this->createMock(SqlVersionProvider::class);
        $this->adapterMock = $this->createMock(AdapterInterface::class);

        $this->dbSchemaReader = $this->objectManager->getObject(
            DbSchemaReader::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'definitionAggregator' => $this->definitionAggregatorMock,
                'sqlVersionProvider' => $this->sqlVersionProviderMock,
            ]
        );
    }

    /**
     * Test that JSON columns are correctly detected in MariaDB
     *
     * MariaDB stores JSON columns as LONGTEXT internally but adds a json_valid CHECK constraint.
     * The readColumns method should detect this and convert the type back to 'json'.
     */
    public function testReadColumnsDetectsJsonColumnsInMariaDb(): void
    {
        $tableName = 'test_table';
        $resource = 'default';
        $dbName = 'test_db';

        // Mock MariaDB engine detection
        $this->sqlVersionProviderMock->expects($this->once())
            ->method('isMariaDbEngine')
            ->willReturn(true);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->with($resource)
            ->willReturn($this->adapterMock);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getSchemaName')
            ->with($resource)
            ->willReturn($dbName);

        // Mock column query results - returns 'longtext' for a JSON column in MariaDB
        $columnsSelect = $this->createMock(Select::class);
        $checkConstraintsSelect = $this->createMock(Select::class);

        $this->adapterMock->expects($this->exactly(2))
            ->method('select')
            ->willReturnOnConsecutiveCalls($columnsSelect, $checkConstraintsSelect);

        $columnsSelect->expects($this->any())->method('from')->willReturnSelf();
        $columnsSelect->expects($this->any())->method('where')->willReturnSelf();
        $columnsSelect->expects($this->any())->method('order')->willReturnSelf();

        $checkConstraintsSelect->expects($this->any())->method('from')->willReturnSelf();
        $checkConstraintsSelect->expects($this->any())->method('where')->willReturnSelf();

        // Simulate column data with a JSON column reported as longtext
        $columnData = [
            'json_data' => [
                'name' => 'json_data',
                'default' => null,
                'type' => 'longtext',
                'nullable' => true,
                'definition' => 'longtext',
                'extra' => '',
                'comment' => null,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ],
            'other_column' => [
                'name' => 'other_column',
                'default' => null,
                'type' => 'varchar',
                'nullable' => true,
                'definition' => 'varchar(255)',
                'extra' => '',
                'comment' => null,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ]
        ];

        // Simulate CHECK constraint for json_valid
        $checkConstraints = ['json_valid(`json_data`)'];

        $this->adapterMock->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn($columnData);

        $this->adapterMock->expects($this->once())
            ->method('fetchCol')
            ->willReturn($checkConstraints);

        // The definitionAggregator should receive 'json' type for the json_data column
        $this->definitionAggregatorMock->expects($this->exactly(2))
            ->method('fromDefinition')
            ->willReturnCallback(function ($data) {
                // Verify that json_data column has type 'json', not 'longtext'
                if ($data['name'] === 'json_data') {
                    $this->assertEquals('json', $data['type'], 'JSON column should have type corrected to json');
                }
                return $data;
            });

        $this->dbSchemaReader->readColumns($tableName, $resource);
    }

    /**
     * Test that MySQL (non-MariaDB) columns are not modified
     */
    public function testReadColumnsDoesNotModifyColumnsForMySql(): void
    {
        $tableName = 'test_table';
        $resource = 'default';
        $dbName = 'test_db';

        // Mock MySQL engine (not MariaDB)
        $this->sqlVersionProviderMock->expects($this->once())
            ->method('isMariaDbEngine')
            ->willReturn(false);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->with($resource)
            ->willReturn($this->adapterMock);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getSchemaName')
            ->with($resource)
            ->willReturn($dbName);

        $columnsSelect = $this->createMock(Select::class);

        // Only one select call for columns (no CHECK constraints query for MySQL)
        $this->adapterMock->expects($this->once())
            ->method('select')
            ->willReturn($columnsSelect);

        $columnsSelect->expects($this->any())->method('from')->willReturnSelf();
        $columnsSelect->expects($this->any())->method('where')->willReturnSelf();
        $columnsSelect->expects($this->any())->method('order')->willReturnSelf();

        $columnData = [
            'json_data' => [
                'name' => 'json_data',
                'default' => null,
                'type' => 'json',
                'nullable' => true,
                'definition' => 'json',
                'extra' => '',
                'comment' => null,
                'charset' => null,
                'collation' => null
            ]
        ];

        $this->adapterMock->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn($columnData);

        // fetchCol should not be called for MySQL (no CHECK constraint query)
        $this->adapterMock->expects($this->never())
            ->method('fetchCol');

        $this->definitionAggregatorMock->expects($this->once())
            ->method('fromDefinition')
            ->with($this->callback(function ($data) {
                // Verify type is unchanged
                return $data['type'] === 'json';
            }))
            ->willReturnArgument(0);

        $this->dbSchemaReader->readColumns($tableName, $resource);
    }

    /**
     * Test that longtext columns without json_valid CHECK constraint remain as longtext
     */
    public function testReadColumnsKeepsLongtextWithoutJsonConstraint(): void
    {
        $tableName = 'test_table';
        $resource = 'default';
        $dbName = 'test_db';

        // Mock MariaDB engine detection
        $this->sqlVersionProviderMock->expects($this->once())
            ->method('isMariaDbEngine')
            ->willReturn(true);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->with($resource)
            ->willReturn($this->adapterMock);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getSchemaName')
            ->with($resource)
            ->willReturn($dbName);

        $columnsSelect = $this->createMock(Select::class);
        $checkConstraintsSelect = $this->createMock(Select::class);

        $this->adapterMock->expects($this->exactly(2))
            ->method('select')
            ->willReturnOnConsecutiveCalls($columnsSelect, $checkConstraintsSelect);

        $columnsSelect->expects($this->any())->method('from')->willReturnSelf();
        $columnsSelect->expects($this->any())->method('where')->willReturnSelf();
        $columnsSelect->expects($this->any())->method('order')->willReturnSelf();

        $checkConstraintsSelect->expects($this->any())->method('from')->willReturnSelf();
        $checkConstraintsSelect->expects($this->any())->method('where')->willReturnSelf();

        // Regular longtext column (not JSON)
        $columnData = [
            'description' => [
                'name' => 'description',
                'default' => null,
                'type' => 'longtext',
                'nullable' => true,
                'definition' => 'longtext',
                'extra' => '',
                'comment' => null,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ]
        ];

        // No json_valid CHECK constraints
        $checkConstraints = [];

        $this->adapterMock->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn($columnData);

        $this->adapterMock->expects($this->once())
            ->method('fetchCol')
            ->willReturn($checkConstraints);

        // Type should remain longtext since there's no json_valid constraint
        $this->definitionAggregatorMock->expects($this->once())
            ->method('fromDefinition')
            ->with($this->callback(function ($data) {
                return $data['type'] === 'longtext';
            }))
            ->willReturnArgument(0);

        $this->dbSchemaReader->readColumns($tableName, $resource);
    }
}
