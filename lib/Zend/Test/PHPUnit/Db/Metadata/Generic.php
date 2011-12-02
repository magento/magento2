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
 * @version    $Id: Generic.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Db_Adapter_Abstract
 */
#require_once "Zend/Db/Adapter/Abstract.php";

/**
 * @see PHPUnit_Extensions_Database_DB_IMetaData
 */
#require_once "PHPUnit/Extensions/Database/DB/IMetaData.php";

/**
 * Generic Metadata accessor for the Zend_Db adapters
 *
 * @uses       PHPUnit_Extensions_Database_DB_IMetaData
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Db_Metadata_Generic implements PHPUnit_Extensions_Database_DB_IMetaData
{
    /**
     * Zend_Db Connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_connection;

    /**
     * Schemaname
     *
     * @var string
     */
    protected $_schema;

    /**
     * Cached Table metadata
     *
     * @var array
     */
    protected $_tableMetadata = array();

    /**
     * Creates a new database meta data object using the given pdo connection
     * and schema name.
     *
     * @param PDO $pdo
     * @param string $schema
     */
    public final function __construct(Zend_Db_Adapter_Abstract $db, $schema)
    {
        $this->_connection = $db;
        $this->_schema     = $schema;
    }

    /**
     * List Tables
     *
     * @return array
     */
    public function getTableNames()
    {
        return $this->_connection->listTables();
    }

    /**
     * Get Table information
     *
     * @param  string $tableName
     * @return array
     */
    protected function getTableDescription($tableName)
    {
        if(!isset($this->_tableMetadata[$tableName])) {
            $this->_tableMetadata[$tableName] = $this->_connection->describeTable($tableName);
        }
        return $this->_tableMetadata[$tableName];
    }

    /**
     * Returns an array containing the names of all the columns in the
     * $tableName table,
     *
     * @param string $tableName
     * @return array
     */
    public function getTableColumns($tableName)
    {
        $tableMeta = $this->getTableDescription($tableName);
        $columns = array_keys($tableMeta);
        return $columns;
    }

    /**
     * Returns an array containing the names of all the primary key columns in
     * the $tableName table.
     *
     * @param string $tableName
     * @return array
     */
    public function getTablePrimaryKeys($tableName)
    {
        $tableMeta = $this->getTableDescription($tableName);

        $primaryColumnNames = array();
        foreach($tableMeta AS $column) {
            if($column['PRIMARY'] == true) {
                $primaryColumnNames[] = $column['COLUMN_NAME'];
            }
        }
        return $primaryColumnNames;
    }

    /**
     * Returns the name of the default schema.
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Returns a quoted schema object. (table name, column name, etc)
     *
     * @param string $object
     * @return string
     */
    public function quoteSchemaObject($object)
    {
        return $this->_connection->quoteIdentifier($object);
    }

    /**
     * Returns true if the rdbms allows cascading
     *
     * @return bool
     */
    public function allowsCascading()
    {
        return false;
    }
}