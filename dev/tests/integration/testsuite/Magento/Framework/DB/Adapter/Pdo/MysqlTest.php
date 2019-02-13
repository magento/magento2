<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    protected function setUp()
    {
        set_error_handler(null);
        $this->resourceConnection = Bootstrap::getObjectManager()
            ->get(ResourceConnection::class);
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
            $minWaitTimeout = 1;
            $this->setWaitTimeout($minWaitTimeout);
            $this->assertEquals($minWaitTimeout, $this->getWaitTimeout(), 'Wait timeout was not changed');

            // Sleep for time greater than wait_timeout and try to perform query
            sleep($minWaitTimeout + 1);
            $result = $this->executeQuery('SELECT 1');
            $this->assertInstanceOf(\Magento\Framework\DB\Statement\Pdo\Mysql::class, $result);
        } finally {
            $this->getDbAdapter()->closeConnection();
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
     * @param $sql
     * @return void|\Zend_Db_Statement_Pdo
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Adapter_Exception
     */
    private function executeQuery($sql)
    {
        return $this->getDbAdapter()->query($sql);
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
}
