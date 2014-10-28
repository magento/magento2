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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DeleteAll.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see PHPUnit_Extensions_Database_Operation_IDatabaseOperation
 */
#require_once "PHPUnit/Extensions/Database/Operation/IDatabaseOperation.php";

/**
 * @see PHPUnit_Extensions_Database_DB_IDatabaseConnection
 */
#require_once "PHPUnit/Extensions/Database/DB/IDatabaseConnection.php";

/**
 * @see PHPUnit_Extensions_Database_DataSet_IDataSet
 */
#require_once "PHPUnit/Extensions/Database/DataSet/IDataSet.php";

/**
 * @see PHPUnit_Extensions_Database_Operation_Exception
 */
#require_once "PHPUnit/Extensions/Database/Operation/Exception.php";

/**
 * @see Zend_Test_PHPUnit_Db_Connection
 */
#require_once "Zend/Test/PHPUnit/Db/Connection.php";

/**
 * Delete All Operation that can be executed on set up or tear down of a database tester.
 *
 * @uses       PHPUnit_Extensions_Database_Operation_IDatabaseOperation
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Db_Operation_DeleteAll implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{
    /**
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     */
    public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        if(!($connection instanceof Zend_Test_PHPUnit_Db_Connection)) {
            #require_once "Zend/Test/PHPUnit/Db/Exception.php";
            throw new Zend_Test_PHPUnit_Db_Exception("Not a valid Zend_Test_PHPUnit_Db_Connection instance, ".get_class($connection)." given!");
        }

        foreach ($dataSet as $table) {
            try {
                $tableName = $table->getTableMetaData()->getTableName();
                $connection->getConnection()->delete($tableName);
            } catch (Exception $e) {
                #require_once "PHPUnit/Extensions/Database/Operation/Exception.php";
                throw new PHPUnit_Extensions_Database_Operation_Exception('DELETEALL', 'DELETE FROM '.$tableName.'', array(), $table, $e->getMessage());
            }
        }
    }
}