<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Adapter;

use Magento\Framework\DB\Ddl\Table;

/**
 * Magento Database Adapter Interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
interface AdapterInterface
{
    const INDEX_TYPE_PRIMARY = 'primary';

    const INDEX_TYPE_UNIQUE = 'unique';

    const INDEX_TYPE_INDEX = 'index';

    const INDEX_TYPE_FULLTEXT = 'fulltext';

    const FK_ACTION_CASCADE = 'CASCADE';

    const FK_ACTION_SET_NULL = 'SET NULL';

    const FK_ACTION_NO_ACTION = 'NO ACTION';

    const FK_ACTION_RESTRICT = 'RESTRICT';

    const FK_ACTION_SET_DEFAULT = 'SET DEFAULT';

    const INSERT_ON_DUPLICATE = 1;

    const INSERT_IGNORE = 2;

    const ISO_DATE_FORMAT = 'yyyy-MM-dd';

    const ISO_DATETIME_FORMAT = 'yyyy-MM-dd HH-mm-ss';

    const INTERVAL_SECOND = 'SECOND';

    const INTERVAL_MINUTE = 'MINUTES';

    const INTERVAL_HOUR = 'HOURS';

    const INTERVAL_DAY = 'DAYS';

    const INTERVAL_MONTH = 'MONTHS';

    const INTERVAL_YEAR = 'YEARS';

    /**
     * Error message for DDL query in transactions
     */
    const ERROR_DDL_MESSAGE = 'DDL statements are not allowed in transactions';

    /**
     * Error message for unfinished rollBack transaction
     */
    const ERROR_ROLLBACK_INCOMPLETE_MESSAGE = 'Rolled back transaction has not been completed correctly.';

    /**
     * Error message for asymmetric transaction rollback
     */
    const ERROR_ASYMMETRIC_ROLLBACK_MESSAGE = 'Asymmetric transaction rollback.';

    /**
     * Error message for asymmetric transaction commit
     */
    const ERROR_ASYMMETRIC_COMMIT_MESSAGE = 'Asymmetric transaction commit.';

    /**
     * Begin new DB transaction for connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function beginTransaction();

    /**
     * Commit DB transaction
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function commit();

    /**
     * Roll-back DB transaction
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function rollBack();

    /**
     * Retrieve DDL object for new table
     *
     * @param string $tableName the table name
     * @param string $schemaName the database or schema name
     * @return Table
     */
    public function newTable($tableName = null, $schemaName = null);

    /**
     * Create table from DDL object
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Interface
     */
    public function createTable(Table $table);

    /**
     * Drop table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function dropTable($tableName, $schemaName = null);

    /**
     * Create temporary table from DDL object
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Interface
     */
    public function createTemporaryTable(Table $table);

    /**
     * Create temporary table from other table
     *
     * @param string $temporaryTableName
     * @param string $originTableName
     * @param bool $ifNotExists
     * @return \Zend_Db_Statement_Interface
     */
    public function createTemporaryTableLike($temporaryTableName, $originTableName, $ifNotExists = false);

    /**
     * Drop temporary table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function dropTemporaryTable($tableName, $schemaName = null);

    /**
     * Rename several tables
     *
     * @param array $tablePairs array('oldName' => 'Name1', 'newName' => 'Name2')
     *
     * @return boolean
     * @throws \Zend_Db_Exception
     */
    public function renameTablesBatch(array $tablePairs);

    /**
     * Truncate a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function truncateTable($tableName, $schemaName = null);

    /**
     * Checks if table exists
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function isTableExists($tableName, $schemaName = null);

    /**
     * Returns short table status array
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array|false
     */
    public function showTableStatus($tableName, $schemaName = null);

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null);

    /**
     * Create \Magento\Framework\DB\Ddl\Table object by data from describe table
     *
     * @param string $tableName
     * @param string $newTableName
     * @return Table
     */
    public function createTableByDdl($tableName, $newTableName);

    /**
     * Modify the column definition by data from describe table
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function modifyColumnByDdl($tableName, $columnName, $definition, $flushData = false, $schemaName = null);

    /**
     * Rename table
     *
     * @param string $oldTableName
     * @param string $newTableName
     * @param string $schemaName
     * @return boolean
     */
    public function renameTable($oldTableName, $newTableName, $schemaName = null);

    /**
     * Adds new column to the table.
     *
     * Generally $defintion must be array with column data to keep this call cross-DB compatible.
     * Using string as $definition is allowed only for concrete DB adapter.
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition  string specific or universal array DB Server definition
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function addColumn($tableName, $columnName, $definition, $schemaName = null);

    /**
     * Change the column name and definition
     *
     * For change definition of column - use modifyColumn
     *
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array|string $definition
     * @param boolean $flushData        flush table statistic
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function changeColumn(
        $tableName,
        $oldColumnName,
        $newColumnName,
        $definition,
        $flushData = false,
        $schemaName = null
    );

    /**
     * Modify the column definition
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function modifyColumn($tableName, $columnName, $definition, $flushData = false, $schemaName = null);

    /**
     * Drop the column from table
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return boolean
     */
    public function dropColumn($tableName, $columnName, $schemaName = null);

    /**
     * Check is table column exists
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return boolean
     */
    public function tableColumnExists($tableName, $columnName, $schemaName = null);

    /**
     * Add new index to table name
     *
     * @param string $tableName
     * @param string $indexName
     * @param string|array $fields  the table column name or array of ones
     * @param string $indexType     the index type
     * @param string $schemaName
     * @return \Zend_Db_Statement_Interface
     */
    public function addIndex($tableName, $indexName, $fields, $indexType = self::INDEX_TYPE_INDEX, $schemaName = null);

    /**
     * Drop the index from table
     *
     * @param string $tableName
     * @param string $keyName
     * @param string $schemaName
     * @return bool|\Zend_Db_Statement_Interface
     */
    public function dropIndex($tableName, $keyName, $schemaName = null);

    /**
     * Returns the table index information
     *
     * The return value is an associative array keyed by the UPPERCASE index key (except for primary key,
     * that is always stored under 'PRIMARY' key) as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string; name of the table
     * KEY_NAME         => string; the original index name
     * COLUMNS_LIST     => array; array of index column names
     * INDEX_TYPE       => string; lowercase, create index type
     * INDEX_METHOD     => string; index method using
     * type             => string; see INDEX_TYPE
     * fields           => array; see COLUMNS_LIST
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array
     */
    public function getIndexList($tableName, $schemaName = null);

    /**
     * Add new Foreign Key to table
     * If Foreign Key with same name is exist - it will be deleted
     *
     * @param string $fkName
     * @param string $tableName
     * @param string $columnName
     * @param string $refTableName
     * @param string $refColumnName
     * @param string $onDelete
     * @param string $onUpdate
     * @param boolean $purge trying remove invalid data
     * @param string $schemaName
     * @param string $refSchemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function addForeignKey(
        $fkName,
        $tableName,
        $columnName,
        $refTableName,
        $refColumnName,
        $onDelete = self::FK_ACTION_CASCADE,
        $purge = false,
        $schemaName = null,
        $refSchemaName = null
    );

    /**
     * Drop the Foreign Key from table
     *
     * @param string $tableName
     * @param string $fkName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function dropForeignKey($tableName, $fkName, $schemaName = null);

    /**
     * Retrieve the foreign keys descriptions for a table.
     *
     * The return value is an associative array keyed by the UPPERCASE foreign key,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * FK_NAME          => string; original foreign key name
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * REF_SCHEMA_NAME  => string; name of reference database or schema
     * REF_TABLE_NAME   => string; reference table name
     * REF_COLUMN_NAME  => string; reference column name
     * ON_DELETE        => string; action type on delete row
     * ON_UPDATE        => string; action type on update row
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array
     */
    public function getForeignKeys($tableName, $schemaName = null);

    /**
     * Creates and returns a new \Magento\Framework\DB\Select object for this adapter.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function select();

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     */
    public function insertOnDuplicate($table, array $data, array $fields = []);

    /**
     * Inserts a table multiply rows with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $data Column-value pairs or array of Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insertMultiple($table, array $data);

    /**
     * Insert array into a table based on columns definition
     *
     * $data can be represented as:
     * - arrays of values ordered according to columns in $columns array
     *      array(
     *          array('value1', 'value2'),
     *          array('value3', 'value4'),
     *      )
     * - array of values, if $columns contains only one column
     *      array('value1', 'value2')
     *
     * @param   string $table
     * @param   array $columns  the data array column map
     * @param   array $data
     * @return  int
     */
    public function insertArray($table, array $columns, array $data);

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind);

    /**
     * Inserts a table row with specified data
     * Special for Zero values to identity column
     *
     * @param string $table
     * @param array $bind
     * @return int The number of affected rows.
     */
    public function insertForce($table, array $bind);

    /**
     * Updates table rows with specified data based on a WHERE clause.
     *
     * The $where parameter in this instance can be a single WHERE clause or an array containing a multiple.  In all
     * instances, a WHERE clause can be a string or an instance of {@see Zend_Db_Expr}.  In the event you use an array,
     * you may specify the clause as the key and a value to be bound to it as the value. E.g., ['amt > ?' => $amt]
     *
     * If the $where parameter is an array of multiple clauses, they will be joined by AND, with each clause wrapped in
     * parenthesis.  If you wish to use an OR, you must give a single clause that is an instance of {@see Zend_Db_Expr}
     *
     * @param  mixed        $table The table to update.
     * @param  array        $bind  Column-value pairs.
     * @param  mixed        $where UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, array $bind, $where = '');

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  mixed        $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '');

    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed  $sql  The SQL statement with placeholders.
     *                      May be a string or \Magento\Framework\DB\Select.
     * @param  mixed  $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Interface
     */
    public function query($sql, $bind = []);

    /**
     * Fetches all SQL result rows as a sequential array.
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql  An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchAll($sql, $bind = [], $fetchMode = null);

    /**
     * Fetches the first row of the SQL result.
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchRow($sql, $bind = [], $fetchMode = null);

    /**
     * Fetches all SQL result rows as an associative array.
     *
     * The first column is the key, the entire row array is the
     * value.  You should construct the query to be sure that
     * the first column contains unique values, or else
     * rows with duplicate values in the first column will
     * overwrite previous data.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchAssoc($sql, $bind = []);

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * The first column in each row is used as the array key.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchCol($sql, $bind = []);

    /**
     * Fetches all SQL result rows as an array of key-value pairs.
     *
     * The first column is the key, the second column is the
     * value.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchPairs($sql, $bind = []);

    /**
     * Fetches the first column of the first row of the SQL result.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return string
     */
    public function fetchOne($sql, $bind = []);

    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string.
     *
     * @param mixed $value The value to quote.
     * @param mixed $type  OPTIONAL the SQL datatype name, or constant, or null.
     * @return mixed An SQL-safe quoted value (or string of separated values).
     */
    public function quote($value, $type = null);

    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example:
     *
     * <code>
     * $text = "WHERE date < ?";
     * $date = "2005-01-02";
     * $safe = $sql->quoteInto($text, $date);
     * // $safe = "WHERE date < '2005-01-02'"
     * </code>
     *
     * @param string  $text  The text with a placeholder.
     * @param mixed   $value The value to quote.
     * @param string  $type  OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the original text.
     */
    public function quoteInto($text, $value, $type = null, $count = null);

    /**
     * Quotes an identifier.
     *
     * Accepts a string representing a qualified indentifier. For Example:
     * <code>
     * $adapter->quoteIdentifier('myschema.mytable')
     * </code>
     * Returns: "myschema"."mytable"
     *
     * Or, an array of one or more identifiers that may form a qualified identifier:
     * <code>
     * $adapter->quoteIdentifier(array('myschema','my.table'))
     * </code>
     * Returns: "myschema"."my.table"
     *
     * The actual quote character surrounding the identifiers may vary depending on
     * the adapter.
     *
     * @param string|array|\Zend_Db_Expr $ident The identifier.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier.
     */
    public function quoteIdentifier($ident, $auto = false);

    /**
     * Quote a column identifier and alias.
     *
     * @param string|array|\Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An alias for the column.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quoteColumnAs($ident, $alias, $auto = false);

    /**
     * Quote a table identifier and alias.
     *
     * @param string|array|\Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An alias for the table.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quoteTableAs($ident, $alias = null, $auto = false);

    /**
     * Format Date to internal database date format
     *
     * @param int|string|\DateTime $date
     * @param boolean $includeTime
     * @return \Zend_Db_Expr
     */
    public function formatDate($date, $includeTime = true);

    /**
     * Run additional environment before setup
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function startSetup();

    /**
     * Run additional environment after setup
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function endSetup();

    /**
     * Set cache adapter
     *
     * @param \Magento\Framework\Cache\FrontendInterface $cacheAdapter
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function setCacheAdapter(\Magento\Framework\Cache\FrontendInterface $cacheAdapter);

    /**
     * Allow DDL caching
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function allowDdlCache();

    /**
     * Disallow DDL caching
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function disallowDdlCache();

    /**
     * Reset cached DDL data from cache
     * if table name is null - reset all cached DDL data
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function resetDdlCache($tableName = null, $schemaName = null);

    /**
     * Save DDL data into cache
     *
     * @param string $tableCacheKey
     * @param int $ddlType
     * @param mixed $data
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function saveDdlCache($tableCacheKey, $ddlType, $data);

    /**
     * Load DDL data from cache
     * Return false if cache does not exists
     *
     * @param string $tableCacheKey the table cache key
     * @param int $ddlType          the DDL constant
     * @return string|array|int|false
     */
    public function loadDdlCache($tableCacheKey, $ddlType);

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * - array("from" => $fromValue, "to" => $toValue)
     * - array("eq" => $equalValue)
     * - array("neq" => $notEqualValue)
     * - array("like" => $likeValue)
     * - array("in" => array($inValues))
     * - array("nin" => array($notInValues))
     * - array("notnull" => $valueIsNotNull)
     * - array("null" => $valueIsNull)
     * - array("moreq" => $moreOrEqualValue)
     * - array("gt" => $greaterValue)
     * - array("lt" => $lessValue)
     * - array("gteq" => $greaterOrEqualValue)
     * - array("lteq" => $lessOrEqualValue)
     * - array("finset" => $valueInSet)
     * - array("regexp" => $regularExpression)
     * - array("seq" => $stringValue)
     * - array("sneq" => $stringValue)
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     * @return string
     */
    public function prepareSqlCondition($fieldName, $condition);

    /**
     * Prepare value for save in column
     * Return converted to column data type value
     *
     * @param array $column     the column describe array
     * @param mixed $value
     * @return mixed
     */
    public function prepareColumnValue(array $column, $value);

    /**
     * Generate fragment of SQL, that check condition and return true or false value
     *
     * @param string $condition     expression
     * @param string $true          true value
     * @param string $false         false value
     * @return \Zend_Db_Expr
     */
    public function getCheckSql($condition, $true, $false);

    /**
     * Returns valid IFNULL expression
     *
     * @param string $expression
     * @param string|int $value OPTIONAL. Applies when $expression is NULL
     * @return \Zend_Db_Expr
     */
    public function getIfNullSql($expression, $value = 0);

    /**
     * Generate fragment of SQL, that combine together (concatenate) the results from data array
     * All arguments in data must be quoted
     *
     * @param array $data
     * @param string $separator concatenate with separator
     * @return \Zend_Db_Expr
     */
    public function getConcatSql(array $data, $separator = null);

    /**
     * Generate fragment of SQL that returns length of character string
     * The string argument must be quoted
     *
     * @param string $string
     * @return \Zend_Db_Expr
     */
    public function getLengthSql($string);

    /**
     * Generate fragment of SQL, that compare with two or more arguments, and returns the smallest
     * (minimum-valued) argument
     * All arguments in data must be quoted
     *
     * @param array $data
     * @return \Zend_Db_Expr
     */
    public function getLeastSql(array $data);

    /**
     * Generate fragment of SQL, that compare with two or more arguments, and returns the largest
     * (maximum-valued) argument
     * All arguments in data must be quoted
     *
     * @param array $data
     * @return \Zend_Db_Expr
     */
    public function getGreatestSql(array $data);

    /**
     * Add time values (intervals) to a date value
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date   quoted field name or SQL statement
     * @param int $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function getDateAddSql($date, $interval, $unit);

    /**
     * Subtract time values (intervals) to a date value
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date   quoted field name or SQL statement
     * @param int|string $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function getDateSubSql($date, $interval, $unit);

    /**
     * Format date as specified
     *
     * Supported format Specifier
     *
     * %H   Hour (00..23)
     * %i   Minutes, numeric (00..59)
     * %s   Seconds (00..59)
     * %d   Day of the month, numeric (00..31)
     * %m   Month, numeric (00..12)
     * %Y   Year, numeric, four digits
     *
     * @param \Zend_Db_Expr|string $date   quoted field name or SQL statement
     * @param string $format
     * @return \Zend_Db_Expr
     */
    public function getDateFormatSql($date, $format);

    /**
     * Extract the date part of a date or datetime expression
     *
     * @param \Zend_Db_Expr|string $date   quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function getDatePartSql($date);

    /**
     * Prepare substring sql function
     *
     * @param \Zend_Db_Expr|string $stringExpression quoted field name or SQL statement
     * @param int|string|\Zend_Db_Expr $pos
     * @param int|string|\Zend_Db_Expr|null $len
     * @return \Zend_Db_Expr
     */
    public function getSubstringSql($stringExpression, $pos, $len = null);

    /**
     * Prepare standard deviation sql function
     *
     * @param \Zend_Db_Expr|string $expressionField   quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function getStandardDeviationSql($expressionField);

    /**
     * Extract part of a date
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date   quoted field name or SQL statement
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function getDateExtractSql($date, $unit);

    /**
     * Retrieve valid table name
     * Check table name length and allowed symbols
     *
     * @param string $tableName
     * @return string
     */
    public function getTableName($tableName);

    /**
     * Build a trigger name based on table name and trigger details
     *
     * @param string $tableName  The table that is the subject of the trigger
     * @param string $time  Either "before" or "after"
     * @param string $event  The DB level event which activates the trigger, i.e. "update" or "insert"
     * @return string
     */
    public function getTriggerName($tableName, $time, $event);

    /**
     * Retrieve valid index name
     * Check index name length and allowed symbols
     *
     * @param string $tableName
     * @param string|array $fields  the columns list
     * @param string $indexType
     * @return string
     */
    public function getIndexName($tableName, $fields, $indexType = '');

    /**
     * Retrieve valid foreign key name
     * Check foreign key name length and allowed symbols
     *
     * @param string $priTableName
     * @param string $priColumnName
     * @param string $refTableName
     * @param string $refColumnName
     * @return string
     */
    public function getForeignKeyName($priTableName, $priColumnName, $refTableName, $refColumnName);

    /**
     * Stop updating indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function disableTableKeys($tableName, $schemaName = null);

    /**
     * Re-create missing indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function enableTableKeys($tableName, $schemaName = null);

    /**
     * Get insert from Select object query
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $table insert into table
     * @param array $fields
     * @param int|bool $mode
     * @return string
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select, $table, array $fields = [], $mode = false);

    /**
     * Get insert queries in array for insert by range with step parameter
     *
     * @param string $rangeField
     * @param \Magento\Framework\DB\Select $select
     * @param int $stepCount
     * @return \Magento\Framework\DB\Select[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function selectsByRange($rangeField, \Magento\Framework\DB\Select $select, $stepCount = 100);

    /**
     * Get update table query using select object for join and update
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string|array $table
     * @return string
     */
    public function updateFromSelect(\Magento\Framework\DB\Select $select, $table);

    /**
     * Get delete from select object query
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $table the table name or alias used in select
     * @return string|int
     */
    public function deleteFromSelect(\Magento\Framework\DB\Select $select, $table);

    /**
     * Return array of table(s) checksum as table name - checksum pairs
     *
     * @param array|string $tableNames
     * @param string $schemaName
     * @return array
     */
    public function getTablesChecksum($tableNames, $schemaName = null);

    /**
     * Check if the database support STRAIGHT JOIN
     *
     * @return boolean
     */
    public function supportStraightJoin();

    /**
     * Adds order by random to select object
     * Possible using integer field for optimization
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $field
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function orderRand(\Magento\Framework\DB\Select $select, $field = null);

    /**
     * Render SQL FOR UPDATE clause
     *
     * @param string $sql
     * @return string
     */
    public function forUpdate($sql);

    /**
     * Try to find installed primary key name, if not - formate new one.
     *
     * @param string $tableName Table name
     * @param string $schemaName OPTIONAL
     * @return string Primary Key name
     */
    public function getPrimaryKeyName($tableName, $schemaName = null);

    /**
     * Converts fetched blob into raw binary PHP data.
     * Some DB drivers return blobs as hex-coded strings, so we need to process them.
     *
     * @param mixed $value
     * @return mixed
     */
    public function decodeVarbinary($value);

    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public function getTransactionLevel();

    /**
     * Create trigger
     *
     * @param \Magento\Framework\DB\Ddl\Trigger $trigger
     * @return \Zend_Db_Statement_Pdo
     */
    public function createTrigger(\Magento\Framework\DB\Ddl\Trigger $trigger);

    /**
     * Drop trigger from database
     *
     * @param string $triggerName
     * @param string|null $schemaName
     * @return bool
     */
    public function dropTrigger($triggerName, $schemaName = null);

    /**
     * Retrieve tables list
     *
     * @param null|string $likeCondition
     * @return array
     */
    public function getTables($likeCondition = null);

    /**
     * Generate fragment of SQL, that check value against multiple condition cases
     * and return different result depends on them
     *
     * @param string $valueName Name of value to check
     * @param array $casesResults Cases and results
     * @param string $defaultValue value to use if value doesn't confirm to any cases
     * @return \Zend_Db_Expr
     */
    public function getCaseSql($valueName, $casesResults, $defaultValue = null);

    /**
     * Returns auto increment field if exists
     *
     * @param string $tableName
     * @param string|null $schemaName
     * @return string|bool
     */
    public function getAutoIncrementField($tableName, $schemaName = null);
}
