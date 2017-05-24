<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\Framework\DB\Ddl\Table;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    protected function setUp()
    {
        set_error_handler(null);
        $this->resourceConnection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(ResourceConnection::class);
        CacheCleaner::cleanAll();
    }

    protected function tearDown()
    {
        restore_error_handler();
    }

    /**
     * Test lost connection re-initializing
     *
     * @throws \Exception
     */
    public function testWaitTimeout()
    {
        if (!$this->getDbAdapter() instanceof \Magento\Framework\DB\Adapter\Pdo\Mysql) {
            $this->markTestSkipped('This test is for \Magento\Framework\DB\Adapter\Pdo\Mysql');
        }
        try {
            $defaultWaitTimeout = $this->getWaitTimeout();
            $minWaitTimeout = 1;
            $this->setWaitTimeout($minWaitTimeout);
            $this->assertEquals($minWaitTimeout, $this->getWaitTimeout(), 'Wait timeout was not changed');

            // Sleep for time greater than wait_timeout and try to perform query
            sleep($minWaitTimeout + 1);
            $result = $this->executeQuery('SELECT 1');
            $this->assertInstanceOf(\Magento\Framework\DB\Statement\Pdo\Mysql::class, $result);
            // Restore wait_timeout
            $this->setWaitTimeout($defaultWaitTimeout);
            $this->assertEquals(
                $defaultWaitTimeout,
                $this->getWaitTimeout(),
                'Default wait timeout was not restored'
            );
        } catch (\Exception $e) {
            // Reset connection on failure to restore global variables
            $this->getDbAdapter()->closeConnection();
            throw $e;
        }
    }

    /**
     * Get session wait_timeout
     *
     * @return int
     */
    private function getWaitTimeout()
    {
        $result = $this->executeQuery('SELECT @@session.wait_timeout');
        return (int)$result->fetchColumn();
    }

    /**
     * Set session wait_timeout
     *
     * @param int $waitTimeout
     */
    private function setWaitTimeout($waitTimeout)
    {
        $this->executeQuery("SET @@session.wait_timeout = {$waitTimeout}");
    }

    /**
     * Execute SQL query and return result statement instance
     *
     * @param string $sql
     * @return \Zend_Db_Statement_Interface
     * @throws \Exception
     */
    private function executeQuery($sql)
    {
        /**
         * Suppress PDO warnings to work around the bug https://bugs.php.net/bug.php?id=63812
         */
        $phpErrorReporting = error_reporting();
        /** @var $pdoConnection \PDO */
        $pdoConnection = $this->getDbAdapter()->getConnection();
        $pdoWarningsEnabled = $pdoConnection->getAttribute(\PDO::ATTR_ERRMODE) & \PDO::ERRMODE_WARNING;
        if (!$pdoWarningsEnabled) {
            error_reporting($phpErrorReporting & ~E_WARNING);
        }
        try {
            $result = $this->getDbAdapter()->query($sql);
            error_reporting($phpErrorReporting);
        } catch (\Exception $e) {
            error_reporting($phpErrorReporting);
            throw $e;
        }
        return $result;
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

    public function testGetCreateTable()
    {
        $tableName = $this->resourceConnection->getTableName('core_config_data');
        $this->assertEquals(
            $this->getDbAdapter()->getCreateTable($tableName),
            $this->getDbAdapter()->getCreateTable($tableName)
        );
    }

    public function testGetForeignKeys()
    {
        $tableName = $this->resourceConnection->getTableName('core_config_data');
        $this->assertEquals(
            $this->getDbAdapter()->getForeignKeys($tableName),
            $this->getDbAdapter()->getForeignKeys($tableName)
        );
    }

    public function testGetIndexList()
    {
        $tableName = $this->resourceConnection->getTableName('core_config_data');
        $this->assertEquals(
            $this->getDbAdapter()->getIndexList($tableName),
            $this->getDbAdapter()->getIndexList($tableName)
        );
    }

    public function testDescribeTable()
    {
        $tableName = $this->resourceConnection->getTableName('core_config_data');
        $this->assertEquals(
            $this->getDbAdapter()->describeTable($tableName),
            $this->getDbAdapter()->describeTable($tableName)
        );
    }

    /**
     * Test that Zend_Db_Expr can be used as a column default value.
     * @see https://github.com/magento/magento2/pull/9131
     */
    public function testCreateTableColumnWithExpressionAsColumnDefaultValue()
    {
        $adapter = $this->getDbAdapter();
        $tableName = 'table_column_with_expression_as_column_default_value';

        $table = $adapter
            ->newTable($tableName)
            ->addColumn(
                'row_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
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
            ->setComment('Test table column with expression as column default value');
        $adapter->createTable($table);

        $tableDescription = $adapter->describeTable($tableName);

        //clean up database from test table
        $adapter->dropTable($tableName);

        $this->assertArrayHasKey('created_at', $tableDescription, 'Column created_at does not exists');
        $this->assertArrayHasKey('integer_column', $tableDescription, 'Column integer_column does not exists');
        $this->assertArrayHasKey('string_column', $tableDescription, 'Column string_column does not exists');
        $dateColumn = $tableDescription['created_at'];
        $intColumn = $tableDescription['integer_column'];
        $stringColumn = $tableDescription['string_column'];

        //Test default value with expression
        $this->assertEquals('created_at', $dateColumn['COLUMN_NAME'], 'Incorrect column name');
        $this->assertEquals(Table::TYPE_DATETIME, $dateColumn['DATA_TYPE'], 'Incorrect column type');
        $this->assertEquals('CURRENT_TIMESTAMP', $dateColumn['DEFAULT'], 'Incorrect column default expression value');

        //Test default value with integer value
        $this->assertEquals('integer_column', $intColumn['COLUMN_NAME'], 'Incorrect column name');
        $this->assertEquals('int', $intColumn['DATA_TYPE'], 'Incorrect column type');
        $this->assertEquals(123456, $intColumn['DEFAULT'], 'Incorrect column default integer value');

        //Test default value with string value
        $this->assertEquals('string_column', $stringColumn['COLUMN_NAME'], 'Incorrect column name');
        $this->assertEquals('varchar', $stringColumn['DATA_TYPE'], 'Incorrect column type');
        $this->assertEquals('default test text', $stringColumn['DEFAULT'], 'Incorrect column default string value');
    }
}
