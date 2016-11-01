<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test for an PDO MySQL adapter
 */
namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\App\ResourceConnection;
use Zend_Db_Statement_Exception;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Database adapter instance
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_connection = null;

    public function setUp()
    {
        set_error_handler(null);
    }

    public function tearDown()
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
        if (!$this->_getConnection() instanceof \Magento\Framework\DB\Adapter\Pdo\Mysql) {
            $this->markTestSkipped('This test is for \Magento\Framework\DB\Adapter\Pdo\Mysql');
        }
        try {
            $defaultWaitTimeout = $this->_getWaitTimeout();
            $minWaitTimeout = 1;
            $this->_setWaitTimeout($minWaitTimeout);
            $this->assertEquals($minWaitTimeout, $this->_getWaitTimeout(), 'Wait timeout was not changed');

            // Sleep for time greater than wait_timeout and try to perform query
            sleep($minWaitTimeout + 1);
            $result = $this->_executeQuery('SELECT 1');
            $this->assertInstanceOf(\Magento\Framework\DB\Statement\Pdo\Mysql::class, $result);
            // Restore wait_timeout
            $this->_setWaitTimeout($defaultWaitTimeout);
            $this->assertEquals(
                $defaultWaitTimeout,
                $this->_getWaitTimeout(),
                'Default wait timeout was not restored'
            );
        } catch (\Exception $e) {
            // Reset connection on failure to restore global variables
            $this->_getConnection()->closeConnection();
            throw $e;
        }
    }

    /**
     * Get session wait_timeout
     *
     * @return int
     */
    protected function _getWaitTimeout()
    {
        $result = $this->_executeQuery('SELECT @@session.wait_timeout');
        return (int)$result->fetchColumn();
    }

    /**
     * Set session wait_timeout
     *
     * @param int $waitTimeout
     */
    protected function _setWaitTimeout($waitTimeout)
    {
        $this->_executeQuery("SET @@session.wait_timeout = {$waitTimeout}");
    }

    /**
     * Execute SQL query and return result statement instance
     *
     * @param string $sql
     * @return \Zend_Db_Statement_Interface
     * @throws \Exception
     */
    protected function _executeQuery($sql)
    {
        /**
         * Suppress PDO warnings to work around the bug
         * @link https://bugs.php.net/bug.php?id=63812
         */
        $phpErrorReporting = error_reporting();
        /** @var $pdoConnection \PDO */
        $pdoConnection = $this->_getConnection()->getConnection();
        $pdoWarningsEnabled = $pdoConnection->getAttribute(\PDO::ATTR_ERRMODE) & \PDO::ERRMODE_WARNING;
        if (!$pdoWarningsEnabled) {
            error_reporting($phpErrorReporting & ~E_WARNING);
        }
        try {
            $result = $this->_getConnection()->query($sql);
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
    protected function _getConnection()
    {
        if (is_null($this->_connection)) {
            /** @var $coreResource ResourceConnection */
            $coreResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get(ResourceConnection::class);
            $this->_connection = $coreResource->getConnection();
        }
        return $this->_connection;
    }
}
