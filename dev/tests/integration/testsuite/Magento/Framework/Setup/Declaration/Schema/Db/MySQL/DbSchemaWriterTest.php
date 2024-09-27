<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test DB schema writer
 *
 * @magentoDbIsolation disabled
 */
class DbSchemaWriterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DbSchemaWriter
     */
    private $dbSchemaWriter;

    protected function setUp(): void
    {
        set_error_handler(null);
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->dbSchemaWriter = Bootstrap::getObjectManager()->get(DbSchemaWriter::class);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    /**
     * Retrieve database adapter instance
     *
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    private function getDbAdapter()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Test reset auto increment
     *
     * @param array $options
     * @param string|bool $expected
     * @throws \Zend_Db_Exception
     * @dataProvider getAutoIncrementFieldDataProvider
     */
    public function testResetAutoIncrement(array $options, $expected)
    {
        $adapter = $this->getDbAdapter();
        $tableName = 'table_auto_increment_field';

        $table = $adapter
            ->newTable($tableName)
            ->addColumn(
                'row_id',
                Table::TYPE_INTEGER,
                null,
                $options,
                'Row Id'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['default' => new \Zend_Db_Expr('CURRENT_TIMESTAMP')]
            )
            ->addColumn(
                'integer_column',
                Table::TYPE_INTEGER,
                11,
                ['default' => 123456]
            )->addColumn(
                'string_column',
                Table::TYPE_TEXT,
                255,
                ['default' => 'default test text']
            )
            ->setComment('Test table with auto increment column');
        $adapter->createTable($table);

        $dbStatement = $this->dbSchemaWriter->resetAutoIncrement($tableName, 'default');
        $this->assertEquals($expected, $dbStatement->getStatement());

        //clean up database from test table
        $adapter->dropTable($tableName);
    }

    public static function getAutoIncrementFieldDataProvider()
    {
        return [
            'auto increment field' => [
                'options' => ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'expected' => 'AUTO_INCREMENT = 0',
            ],
            'non auto increment field' => [
                'options' => ['unsigned' => true, 'nullable' => false,],
                'expected' => 'AUTO_INCREMENT = 1',
            ]
        ];
    }
}
