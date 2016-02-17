<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Test_PHPUnit_Db_Connection
 */
#require_once "Zend/Test/PHPUnit/Db/Connection.php";

/**
 * Operation for Truncating on setup or teardown of a database tester.
 *
 * @uses       PHPUnit_Extensions_Database_Operation_IDatabaseOperation
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Db_Operation_Truncate implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{
    /**
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     * @return void
     */
    public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        if(!($connection instanceof Zend_Test_PHPUnit_Db_Connection)) {
            #require_once "Zend/Test/PHPUnit/Db/Exception.php";
            throw new Zend_Test_PHPUnit_Db_Exception("Not a valid Zend_Test_PHPUnit_Db_Connection instance, ".get_class($connection)." given!");
        }

        foreach ($dataSet->getReverseIterator() AS $table) {
            try {
                $tableName = $table->getTableMetaData()->getTableName();
                $this->_truncate($connection->getConnection(), $tableName);
            } catch (Exception $e) {
                throw new PHPUnit_Extensions_Database_Operation_Exception('TRUNCATE', 'TRUNCATE '.$tableName.'', array(), $table, $e->getMessage());
            }
        }
    }

    /**
     * Truncate a given table.
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $tableName
     * @return void
     */
    protected function _truncate(Zend_Db_Adapter_Abstract $db, $tableName)
    {
        $tableName = $db->quoteIdentifier($tableName, true);
        if($db instanceof Zend_Db_Adapter_Pdo_Sqlite) {
            $db->query('DELETE FROM '.$tableName);
        } else if($db instanceof Zend_Db_Adapter_Db2) {
            /*if(strstr(PHP_OS, "WIN")) {
                $file = tempnam(sys_get_temp_dir(), "zendtestdbibm_");
                file_put_contents($file, "");
                $db->query('IMPORT FROM '.$file.' OF DEL REPLACE INTO '.$tableName);
                unlink($file);
            } else {
                $db->query('IMPORT FROM /dev/null OF DEL REPLACE INTO '.$tableName);
            }*/
            #require_once "Zend/Exception.php";
            throw Zend_Exception("IBM Db2 TRUNCATE not supported.");
        } else if($this->_isMssqlOrOracle($db)) {
            $db->query('TRUNCATE TABLE '.$tableName);
        } else if($db instanceof Zend_Db_Adapter_Pdo_Pgsql) {
            $db->query('TRUNCATE '.$tableName.' CASCADE');
        } else {
            $db->query('TRUNCATE '.$tableName);
        }
    }

    /**
     * Detect if an adapter is for Mssql or Oracle Databases.
     *
     * @param  Zend_Db_Adapter_Abstract $db
     * @return bool
     */
    private function _isMssqlOrOracle($db)
    {
        return (
            $db instanceof Zend_Db_Adapter_Pdo_Mssql ||
            $db instanceof Zend_Db_Adapter_Sqlsrv ||
            $db instanceof Zend_Db_Adapter_Pdo_Oci ||
            $db instanceof Zend_Db_Adapter_Oracle
        );
    }
}
