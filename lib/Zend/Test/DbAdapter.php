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
 * @version    $Id: DbAdapter.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Db_Adapter_Abstract
 */
#require_once "Zend/Db/Adapter/Abstract.php";

/**
 * @see Zend_Test_DbStatement
 */
#require_once "Zend/Test/DbStatement.php";

/**
 * @see Zend_Db_Profiler
 */
#require_once 'Zend/Db/Profiler.php';

/**
 * Testing Database Adapter which acts as a stack for SQL Results
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_DbAdapter extends Zend_Db_Adapter_Abstract
{
    /**
     * @var array
     */
    protected $_statementStack = array();

    /**
     * @var boolean
     */
    protected $_connected = false;

    /**
     * @var array
     */
    protected $_listTables = array();

    /**
     * @var array
     */
    protected $_lastInsertIdStack = array();

    /**
     * @var array
     */
    protected $_describeTables = array();

    /**
     * @var string
     */ 
    protected $_quoteIdentifierSymbol = '';

    /**
     * Empty constructor to make it parameterless.
     */
    public function __construct()
    {
        $profiler = new Zend_Db_Profiler();
        $profiler->setEnabled(true);
        $this->setProfiler($profiler);
    }

    /**
     * Append a new Statement to the SQL Result Stack.
     *
     * @param  Zend_Test_DbStatement $stmt
     * @return Zend_Test_DbAdapter
     */
    public function appendStatementToStack(Zend_Test_DbStatement $stmt)
    {
        array_push($this->_statementStack, $stmt);
        return $this;
    }

    /**
     * Append a new Insert Id to the {@see lastInsertId}.
     *
     * @param  int|string $id
     * @return Zend_Test_DbAdapter
     */
    public function appendLastInsertIdToStack($id)
    {
        array_push($this->_lastInsertIdStack, $id);
        return $this;
    }

    /**
     * @var string
     */ 
    public function setQuoteIdentifierSymbol($symbol)
    {
        $this->_quoteIdentifierSymbol = $symbol;
    }

    /**
     * Returns the symbol the adapter uses for delimited identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return $this->_quoteIdentifierSymbol;
    }

    /**
     * Set the result from {@see listTables()}.
     *
     * @param array $listTables
     */
    public function setListTables(array $listTables)
    {
        $this->_listTables = $listTables;
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
       return $this->_listTables;
    }

    /**
     *
     * @param  string $table
     * @param  array $tableInfo
     * @return Zend_Test_DbAdapter
     */
    public function setDescribeTable($table, $tableInfo)
    {
        $this->_describeTables[$table] = $tableInfo;
        return $this;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME => string; name of database or schema
     * TABLE_NAME  => string;
     * COLUMN_NAME => string; column name
     * COLUMN_POSITION => number; ordinal position of column in table
     * DATA_TYPE   => string; SQL datatype name of column
     * DEFAULT     => string; default expression of column, null if none
     * NULLABLE    => boolean; true if column can have nulls
     * LENGTH      => number; length of CHAR/VARCHAR
     * SCALE       => number; scale of NUMERIC/DECIMAL
     * PRECISION   => number; precision of NUMERIC/DECIMAL
     * UNSIGNED    => boolean; unsigned property of an integer type
     * PRIMARY     => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        if(isset($this->_describeTables[$tableName])) {
            return $this->_describeTables[$tableName];
        } else {
            return array();
        }
    }

    /**
     * Creates a connection to the database.
     *
     * @return void
     */
    protected function _connect()
    {
        $this->_connected = true;
    }

    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->_connected;
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        $this->_connected = false;
    }

    /**
     * Prepare a statement and return a PDOStatement-like object.
     *
     * @param string|Zend_Db_Select $sql SQL query
     * @return Zend_Db_Statment|PDOStatement
     */
    public function prepare($sql)
    {
        $queryId = $this->getProfiler()->queryStart($sql);

        if(count($this->_statementStack)) {
            $stmt = array_pop($this->_statementStack);
        } else {
            $stmt = new Zend_Test_DbStatement();
        }

        if($this->getProfiler()->getEnabled() == true) {
            $qp = $this->getProfiler()->getQueryProfile($queryId);
            $stmt->setQueryProfile($qp);
        }

        return $stmt;
    }

    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        if(count($this->_lastInsertIdStack)) {
            return array_pop($this->_lastInsertIdStack);
        } else {
            return false;
        }
    }

    /**
     * Begin a transaction.
     */
    protected function _beginTransaction()
    {
        return;
    }

    /**
     * Commit a transaction.
     */
    protected function _commit()
    {
        return;
    }

    /**
     * Roll-back a transaction.
     */
    protected function _rollBack()
    {

    }

    /**
     * Set the fetch mode.
     *
     * @param integer $mode
     * @return void
     * @throws Zend_Db_Adapter_Exception
     */
    public function setFetchMode($mode)
    {
        return;
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param mixed $sql
     * @param integer $count
     * @param integer $offset
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        return sprintf('%s LIMIT %d,%d', $sql, $offset, $count);
    }

    /**
     * Check if the adapter supports real SQL parameters.
     *
     * @param string $type 'positional' or 'named'
     * @return bool
     */
    public function supportsParameters($type)
    {
        return true;
    }

    /**
     * Retrieve server version in PHP style
     *
     * @return string
     */
    function getServerVersion()
    {
        return "1.0.0";
    }
}