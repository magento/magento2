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
 * @version    $Id: QueryDataSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see PHPUnit_Extensions_Database_DataSet_QueryDataSet
 */
#require_once "PHPUnit/Extensions/Database/DataSet/QueryDataSet.php";

/**
 * @see PHPUnit_Extensions_Database_DB_IDatabaseConnection
 */
#require_once "PHPUnit/Extensions/Database/DB/IDatabaseConnection.php";

/**
 * @see Zend_Test_PHPUnit_Db_DataSet_QueryTable
 */
#require_once "Zend/Test/PHPUnit/Db/DataSet/QueryTable.php";

/**
 * @see Zend_Db_Select
 */
#require_once "Zend/Db/Select.php";

/**
 * Uses several query strings or Zend_Db_Select objects to form a dataset of tables for assertion with other datasets.
 *
 * @uses       PHPUnit_Extensions_Database_DataSet_QueryDataSet
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Db_DataSet_QueryDataSet extends PHPUnit_Extensions_Database_DataSet_QueryDataSet
{
    /**
     * Creates a new dataset using the given database connection.
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection
     */
    public function __construct(PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection)
    {
        if( !($databaseConnection instanceof Zend_Test_PHPUnit_Db_Connection) ) {
            #require_once "Zend/Test/PHPUnit/Db/Exception.php";
            throw new Zend_Test_PHPUnit_Db_Exception("Zend_Test_PHPUnit_Db_DataSet_QueryDataSet only works with Zend_Test_PHPUnit_Db_Connection connections-");
        }
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * Add a Table dataset representation by specifiying an arbitrary select query.
     *
     * By default a select * will be done on the given tablename.
     *
     * @param string                $tableName
     * @param string|Zend_Db_Select $query
     */
    public function addTable($tableName, $query = NULL)
    {
        if ($query === NULL) {
            $query = $this->databaseConnection->getConnection()->select();
            $query->from($tableName, Zend_Db_Select::SQL_WILDCARD);
        }

        if($query instanceof Zend_Db_Select) {
            $query = $query->__toString();
        }

        $this->tables[$tableName] = new Zend_Test_PHPUnit_Db_DataSet_QueryTable($tableName, $query, $this->databaseConnection);
    }
}