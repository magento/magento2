<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Setup\Framework\DB\Adapter\Pdo;

use Magento\Filesystem\Filesystem;
use Magento\Setup\Framework\DB\Adapter\AdapterInterface;
use Magento\Setup\Framework\DB\Ddl\Table;
use \Magento\Framework\DB\ExpressionConverter;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Driver;
use Zend\Db\Exception\ErrorException;
use Zend\Db\Exception\InvalidArgumentException;
use Zend\Db\Adapter\Exception;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform;
use Zend\Db\Adapter\Profiler;
use Zend\Db\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class Mysql extends Adapter implements AdapterInterface
{
    const DEBUG_CONNECT         = 0;
    const DEBUG_TRANSACTION     = 1;
    const DEBUG_QUERY           = 2;


    const LENGTH_TABLE_NAME     = 64;
    const LENGTH_INDEX_NAME     = 64;
    const LENGTH_FOREIGN_NAME   = 64;

    /**
     * MEMORY engine type for MySQL tables
     */
    const ENGINE_MEMORY = 'MEMORY';

    /**
     * Maximum number of connection retries
     */
    const MAX_CONNECTION_RETRIES = 10;

    /**
     * Current Transaction Level
     *
     * @var int
     */
    protected $_transactionLevel = 0;

    /**
     * Whether transaction was rolled back or not
     *
     * @var bool
     */
    protected $_isRolledBack = false;

    /**
     * Set attribute to connection flag
     *
     * @var bool
     */
    protected $_connectionFlagsSet  = false;



    /**
     * SQL bind params. Used temporarily by regexp callback.
     *
     * @var array
     */
    protected $_bindParams = [];

    /**
     * Autoincrement for bind value. Used by regexp callback.
     *
     * @var int
     */
    protected $_bindIncrement = 0;

    /**
     * Write SQL debug data to file
     *
     * @var bool
     */
    protected $_debug = false;

    /**
     * Minimum query duration time to be logged
     *
     * @var float
     */
    protected $_logQueryTime = 0.05;

    /**
     * Log all queries (ignored minimum query duration time)
     *
     * @var bool
     */
    protected $_logAllQueries = false;

    /**
     * Add to log call stack data (backtrace)
     *
     * @var bool
     */
    protected $_logCallStack = false;

    /**
     * Path to SQL debug data log
     *
     * @var string
     */
    protected $_debugFile = '/debug/pdo_mysql.log';

    /**
     * Filesystem class
     *
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * Debug timer start value
     *
     * @var float
     */
    protected $_debugTimer = 0;

    /**
     * MySQL column - Table DDL type pairs
     *
     * @var array
     */
    protected $_ddlColumnTypes      = array(
        Table::TYPE_BOOLEAN       => 'bool',
        Table::TYPE_SMALLINT      => 'smallint',
        Table::TYPE_INTEGER       => 'int',
        Table::TYPE_BIGINT        => 'bigint',
        Table::TYPE_FLOAT         => 'float',
        Table::TYPE_DECIMAL       => 'decimal',
        Table::TYPE_NUMERIC       => 'decimal',
        Table::TYPE_DATE          => 'date',
        Table::TYPE_TIMESTAMP     => 'timestamp',
        Table::TYPE_DATETIME      => 'datetime',
        Table::TYPE_TEXT          => 'text',
        Table::TYPE_BLOB          => 'blob',
        Table::TYPE_VARBINARY     => 'blob'
    );

    /**
     * All possible DDL statements
     * First 3 symbols for each statement
     *
     * @var string[]
     */
    protected $_ddlRoutines = array('alt', 'cre', 'ren', 'dro', 'tru');

    /**
     * Contructor to create
     *
     * @param array|Driver\DriverInterface $driver
     * @param Platform\PlatformInterface $platform
     * @param ResultSet\ResultSetInterface $queryResultPrototype
     * @param Profiler\ProfilerInterface $profiler
     * @param Filesystem $filesystem
     */
    public function __construct(
        $driver,
        Platform\PlatformInterface $platform = null,
        ResultSet\ResultSetInterface $queryResultPrototype = null,
        Profiler\ProfilerInterface $profiler = null,
        Filesystem $filesystem = null
    ) {
        $this->_filesystem = $filesystem;
        parent::__construct($driver, $platform, $queryResultPrototype, $profiler);
    }

    /**
     * Begin new DB transaction for connection
     *
     * @return $this
     * @throws \Exception
     */
    public function beginTransaction()
    {
        if ($this->_isRolledBack) {
            throw new \Exception(AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE);
        }
        if ($this->_transactionLevel === 0) {
            $this->_debugTimer();
            $this->getDriver()->getConnection()->beginTransaction();
            $this->_debugStat(self::DEBUG_TRANSACTION, 'BEGIN');
        }
        ++$this->_transactionLevel;
        return $this;
    }

    /**
     * Commit DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function commit()
    {
        if ($this->_transactionLevel === 1 && !$this->_isRolledBack) {
            $this->_debugTimer();
            $this->getDriver()->getConnection()->commit();
            $this->_debugStat(self::DEBUG_TRANSACTION, 'COMMIT');
        } elseif ($this->_transactionLevel === 0) {
            throw new \Exception(AdapterInterface::ERROR_ASYMMETRIC_COMMIT_MESSAGE);
        } elseif ($this->_isRolledBack) {
            throw new \Exception(AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE);
        }
        --$this->_transactionLevel;
        return $this;
    }

    /**
     * Rollback DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function rollBack()
    {
        if ($this->_transactionLevel === 1) {
            $this->_debugTimer();
            $this->getDriver()->getConnection()->rollBack();
            $this->_isRolledBack = false;
            $this->_debugStat(self::DEBUG_TRANSACTION, 'ROLLBACK');
        } elseif ($this->_transactionLevel === 0) {
            throw new \Exception(AdapterInterface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE);
        } else {
            $this->_isRolledBack = true;
        }
        --$this->_transactionLevel;
        return $this;
    }

    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public function getTransactionLevel()
    {
        return $this->_transactionLevel;
    }

    /**
     * Creates a PDO object and connects to the database.
     *
     * @return void
     * @throws ErrorException
     */
    public function connect()
    {
        if ($this->getDriver()->getConnection()->isConnected()) {
            return;
        }

        $this->_debugTimer();
        $this->getDriver()->getConnection()->connect();
        $this->_debugStat(self::DEBUG_CONNECT, '');
    }

    /**
     * Run RAW Query
     *
     * @param string $sql
     * @return StatementInterface
     * @throws \PDOException
     */
    public function rawQuery($sql)
    {
        try {
            $result = $this->query($sql);
        } catch (InvalidArgumentException $e) {
            // Convert to \PDOException to maintain backwards compatibility with usage of MySQL adapter
            $e = $e->getPrevious();
            if (!($e instanceof \PDOException)) {
                $e = new \PDOException($e->getMessage(), $e->getCode());
            }
            throw $e;
        }

        return $result;
    }

    /**
     * Fetch all queries
     *
     * @param \Zend\Db\Sql\Select $sql
     * @param array $bind
     * @param null $fetchMode
     * @return StatementInterface|ResultSet\ResultSetInterface
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        if ($sql instanceof \Zend\Db\Sql\Select) {
            $sql = $sql->getSqlString($this->getPlatform());
        }
        return $this->query($sql);
    }

    /**
     * Run RAW query and Fetch First row
     *
     * @param string $sql
     * @param string|int $field
     * @return mixed|null
     */
    public function rawFetchRow($sql, $field = null)
    {
        $result = $this->rawQuery($sql);

        if ($result instanceof ResultSet\ResultSetInterface && $result->count()) {
            $resultSet = new ResultSet\ResultSet;
            $resultSet->initialize($result);

            $row = $resultSet->toArray();

            if (empty($field)) {
                return $row;
            } else {
                return isset($row[0][$field]) ? $row[0][$field] : false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check transaction level in case of DDL query
     *
     * @param string $sql
     * @return void
     * @throws \Zend_Db_Adapter_Exception
     */
    protected function _checkDdlTransaction($sql)
    {
        if (is_string($sql) && $this->getTransactionLevel() > 0) {
            $startSql = strtolower(substr(ltrim($sql), 0, 3));
            if (in_array($startSql, $this->_ddlRoutines)) {
                trigger_error(AdapterInterface::ERROR_DDL_MESSAGE, E_USER_ERROR);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function query(
        $sql,
        $parametersOrQueryMode = self::QUERY_MODE_EXECUTE,
        ResultSet\ResultSetInterface $resultPrototype = null
    ) {
        return parent::query($sql, $parametersOrQueryMode, $resultPrototype);
    }

    /**
     * Drop the Foreign Key from table
     *
     * @param string $tableName
     * @param string $fkName
     * @param string $schemaName
     * @return $this
     */
    public function dropForeignKey($tableName, $fkName, $schemaName = null)
    {
        $foreignKeys = $this->getForeignKeys($tableName, $schemaName);
        $fkName = strtoupper($fkName);
        if (substr($fkName, 0, 3) == 'FK_') {
            $fkName = substr($fkName, 3);
        }
        foreach (array($fkName, 'FK_' . $fkName) as $key) {
            if (isset($foreignKeys[$key])) {
                $sql = sprintf(
                    'ALTER TABLE %s DROP FOREIGN KEY %s',
                    $this->getPlatform()->quoteIdentifierChain($this->_getTableName($tableName)),
                    $this->quoteIdentifier($foreignKeys[$key]['FK_NAME'])
                );
                $this->rawQuery($sql);
            }
        }
        return $this;
    }

    /**
     * Prepare table before add constraint foreign key
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $refTableName
     * @param string $refColumnName
     * @param string $onDelete
     * @return $this
     */
    public function purgeOrphanRecords(
        $tableName,
        $columnName,
        $refTableName,
        $refColumnName,
        $onDelete = AdapterInterface::FK_ACTION_CASCADE
    ) {
        $onDelete = strtoupper($onDelete);
        if ($onDelete == AdapterInterface::FK_ACTION_CASCADE
            || $onDelete == AdapterInterface::FK_ACTION_RESTRICT
        ) {
            $sql = sprintf(
                "DELETE p.* FROM %s AS p LEFT JOIN %s AS r ON p.%s = r.%s WHERE r.%s IS NULL",
                $this->quoteIdentifier($tableName),
                $this->quoteIdentifier($refTableName),
                $this->quoteIdentifier($columnName),
                $this->quoteIdentifier($refColumnName),
                $this->quoteIdentifier($refColumnName)
            );
            $this->rawQuery($sql);
        } elseif ($onDelete == AdapterInterface::FK_ACTION_SET_NULL) {
            $sql = sprintf(
                "UPDATE %s AS p LEFT JOIN %s AS r ON p.%s = r.%s SET p.%s = NULL WHERE r.%s IS NULL",
                $this->quoteIdentifier($tableName),
                $this->quoteIdentifier($refTableName),
                $this->quoteIdentifier($columnName),
                $this->quoteIdentifier($refColumnName),
                $this->quoteIdentifier($columnName),
                $this->quoteIdentifier($refColumnName)
            );
            $this->query($sql);
        }

        return $this;
    }

    /**
     * Check does table column exist
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return bool
     */
    public function tableColumnExists($tableName, $columnName, $schemaName = null)
    {
        $describe = $this->describeTable($tableName, $schemaName);
        foreach ($describe as $column) {
            if ($column['COLUMN_NAME'] == $columnName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds new column to table.
     *
     * Generally $defintion must be array with column data to keep this call cross-DB compatible.
     * Using string as $definition is allowed only for concrete DB adapter.
     * Adds primary key if needed
     *
     * @param   string $tableName
     * @param   string $columnName
     * @param   array|string $definition  string specific or universal array DB Server definition
     * @param   string $schemaName
     * @return  true|StatementInterface
     * @throws  ErrorException
     */
    public function addColumn($tableName, $columnName, $definition, $schemaName = null)
    {
        if ($this->tableColumnExists($tableName, $columnName, $schemaName)) {
            return true;
        }

        $primaryKey = '';
        if (is_array($definition)) {
            $definition = array_change_key_case($definition, CASE_UPPER);
            if (empty($definition['COMMENT'])) {
                throw new ErrorException("Impossible to create a column without comment.");
            }
            if (!empty($definition['PRIMARY'])) {
                $primaryKey = sprintf(', ADD PRIMARY KEY (%s)', $this->quoteIdentifier($columnName));
            }
            $definition = $this->_getColumnDefinition($definition);
        }

        $sql = sprintf(
            'ALTER TABLE %s ADD COLUMN %s %s %s',
            $this->getPlatform()->quoteIdentifierChain($tableName),
            $this->quoteIdentifier($columnName),
            $definition,
            $primaryKey
        );

        $result = $this->query($sql);

        return $result;
    }

    /**
     * Delete table column
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return true|StatementInterface
     */
    public function dropColumn($tableName, $columnName, $schemaName = null)
    {
        if (!$this->tableColumnExists($tableName, $columnName, $schemaName)) {
            return true;
        }

        $alterDrop = array();

        $foreignKeys = $this->getForeignKeys($tableName, $schemaName);
        foreach ($foreignKeys as $fkProp) {
            if ($fkProp['COLUMN_NAME'] == $columnName) {
                $alterDrop[] = 'DROP FOREIGN KEY ' . $this->quoteIdentifier($fkProp['FK_NAME']);
            }
        }

        /* drop index that after column removal would coincide with the existing index by indexed columns */
        foreach ($this->getIndexList($tableName, $schemaName) as $idxData) {
            $idxColumns = $idxData['COLUMNS_LIST'];
            $idxColumnKey = array_search($columnName, $idxColumns);
            if ($idxColumnKey !== false) {
                unset($idxColumns[$idxColumnKey]);
                if ($idxColumns && $this->_getIndexByColumns($tableName, $idxColumns, $schemaName)) {
                    $this->dropIndex($tableName, $idxData['KEY_NAME'], $schemaName);
                }
            }
        }

        $alterDrop[] = 'DROP COLUMN ' . $this->quoteIdentifier($columnName);
        $sql = sprintf(
            'ALTER TABLE %s %s',
            $this->getPlatform()->quoteIdentifierChain($tableName),
            implode(', ', $alterDrop)
        );

        $result = $this->query($sql);

        return $result;
    }

    /**
     * Retrieve index information by indexed columns or return NULL, if there is no index for a column list
     *
     * @param string $tableName
     * @param array $columns
     * @param string|null $schemaName
     * @return array|null
     */
    protected function _getIndexByColumns($tableName, array $columns, $schemaName)
    {
        foreach ($this->getIndexList($tableName, $schemaName) as $idxData) {
            if ($idxData['COLUMNS_LIST'] === $columns) {
                return $idxData;
            }
        }
        return null;
    }

    /**
     * Change the column name and definition
     *
     * For change definition of column - use modifyColumn
     *
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array $definition
     * @param boolean $flushData        flush table statistic
     * @param string $schemaName
     * @return StatementInterface
     * @throws ErrorException
     */
    public function changeColumn(
        $tableName,
        $oldColumnName,
        $newColumnName,
        $definition,
        $flushData = false,
        $schemaName = null
    ) {
        if (!$this->tableColumnExists($tableName, $oldColumnName, $schemaName)) {
            throw new ErrorException(
                sprintf(
                    'Column "%s" does not exist in table "%s".',
                    $oldColumnName,
                    $tableName
                )
            );
        }

        if (is_array($definition)) {
            $definition = $this->_getColumnDefinition($definition);
        }

        $sql = sprintf(
            'ALTER TABLE %s CHANGE COLUMN %s %s %s',
            $this->getPlatform()->quoteIdentifierChain($tableName),
            $this->quoteIdentifier($oldColumnName),
            $this->quoteIdentifier($newColumnName),
            $definition
        );

        $result = $this->query($sql);

        if ($flushData) {
            $this->showTableStatus($tableName, $schemaName);
        }

        return $result;
    }

    /**
     * Modify the column definition
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return $this
     * @throws ErrorException
     */
    public function modifyColumn($tableName, $columnName, $definition, $flushData = false, $schemaName = null)
    {
        if (!$this->tableColumnExists($tableName, $columnName, $schemaName)) {
            throw new ErrorException(sprintf('Column "%s" does not exist in table "%s".', $columnName, $tableName));
        }
        if (is_array($definition)) {
            $definition = $this->_getColumnDefinition($definition);
        }

        $sql = sprintf(
            'ALTER TABLE %s MODIFY COLUMN %s %s',
            $this->getPlatform()->quoteIdentifierChain($tableName),
            $this->quoteIdentifier($columnName),
            $definition
        );

        $this->query($sql);
        if ($flushData) {
            $this->showTableStatus($tableName, $schemaName);
        }

        return $this;
    }

    /**
     * Show table status
     *
     * @param string $tableName
     * @param string $schemaName
     * @return mixed
     */
    public function showTableStatus($tableName, $schemaName = null)
    {
        $fromDbName = null;
        if ($schemaName !== null) {
            $fromDbName = ' FROM ' . $this->quoteIdentifier($schemaName);
        }
        $query = sprintf('SHOW TABLE STATUS%s LIKE %s', $fromDbName, $this->quote($tableName));

        return $this->rawFetchRow($query);
    }

    /**
     * Retrieve Create Table SQL
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     */
    public function getCreateTable($tableName, $schemaName = null)
    {
        if ($schemaName) {
            $table = array($schemaName, $tableName);
        } else {
            $table = $tableName;
        }
        $sql = 'SHOW CREATE TABLE ' . $this->getPlatform()->quoteIdentifierChain($table);
        $ddl = $this->rawFetchRow($sql, 'Create Table');

        return $ddl;
    }

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
    public function getForeignKeys($tableName, $schemaName = null)
    {
        $ddl = array();
        $createSql = $this->getCreateTable($tableName, $schemaName);

        // collect CONSTRAINT
        $regExp  = '#,\s+CONSTRAINT `([^`]*)` FOREIGN KEY \(`([^`]*)`\) '
            . 'REFERENCES (`([^`]*)`\.)?`([^`]*)` \(`([^`]*)`\)'
            . '( ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?'
            . '( ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?#';
        $matches = array();
        preg_match_all($regExp, $createSql, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $ddl[strtoupper($match[1])] = array(
                'FK_NAME'           => $match[1],
                'SCHEMA_NAME'       => $schemaName,
                'TABLE_NAME'        => $tableName,
                'COLUMN_NAME'       => $match[2],
                'REF_SHEMA_NAME'    => isset($match[4]) ? $match[4] : $schemaName,
                'REF_TABLE_NAME'    => $match[5],
                'REF_COLUMN_NAME'   => $match[6],
                'ON_DELETE'         => isset($match[7]) ? $match[8] : '',
                'ON_UPDATE'         => isset($match[9]) ? $match[10] : ''
            );
        }

        return $ddl;
    }

    /**
     * Retrieve the foreign keys tree for all tables
     *
     * @return array
     */
    public function getForeignKeysTree()
    {
        $tree = array();
        foreach ($this->listTables() as $table) {
            foreach ($this->getForeignKeys($table) as $key) {
                $tree[$table][$key['COLUMN_NAME']] = $key;
            }
        }

        return $tree;
    }

    /**
     * Modify tables, used for upgrade process
     * Change columns definitions, reset foreign keys, change tables comments and engines.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * columns => array; list of columns definitions
     * comment => string; table comment
     * engine  => string; table engine
     *
     * @param array $tables
     * @return $this
     */
    public function modifyTables($tables)
    {
        $foreignKeys = $this->getForeignKeysTree();
        foreach ($tables as $table => $tableData) {
            if (!$this->isTableExists($table)) {
                continue;
            }
            foreach ($tableData['columns'] as $column => $columnDefinition) {
                if (!$this->tableColumnExists($table, $column)) {
                    continue;
                }
                $droppedKeys = array();
                foreach ($foreignKeys as $keyTable => $columns) {
                    foreach ($columns as $columnName => $keyOptions) {
                        if ($table == $keyOptions['REF_TABLE_NAME'] && $column == $keyOptions['REF_COLUMN_NAME']) {
                            $this->dropForeignKey($keyTable, $keyOptions['FK_NAME']);
                            $droppedKeys[] = $keyOptions;
                        }
                    }
                }

                $this->modifyColumn($table, $column, $columnDefinition);

                foreach ($droppedKeys as $options) {
                    unset($columnDefinition['identity'], $columnDefinition['primary'], $columnDefinition['comment']);

                    $onDelete = $options['ON_DELETE'];
                    $onUpdate = $options['ON_UPDATE'];

                    if ($onDelete == AdapterInterface::FK_ACTION_SET_NULL
                        || $onUpdate == AdapterInterface::FK_ACTION_SET_NULL) {
                           $columnDefinition['nullable'] = true;
                    }
                    $this->modifyColumn($options['TABLE_NAME'], $options['COLUMN_NAME'], $columnDefinition);
                    $this->addForeignKey(
                        $options['FK_NAME'],
                        $options['TABLE_NAME'],
                        $options['COLUMN_NAME'],
                        $options['REF_TABLE_NAME'],
                        $options['REF_COLUMN_NAME'],
                        ($onDelete) ? $onDelete : AdapterInterface::FK_ACTION_NO_ACTION,
                        ($onUpdate) ? $onUpdate : AdapterInterface::FK_ACTION_NO_ACTION
                    );
                }
            }
            if (!empty($tableData['comment'])) {
                $this->changeTableComment($table, $tableData['comment']);
            }
            if (!empty($tableData['engine'])) {
                $this->changeTableEngine($table, $tableData['engine']);
            }
        }

        return $this;
    }

    /**
     * Retrieve table index information
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
     * @return array|string|int
     */
    public function getIndexList($tableName, $schemaName = null)
    {
        $ddl = array();

        $sql = sprintf(
            'SHOW INDEX FROM %s',
            $this->getPlatform()->quoteIdentifierChain($tableName)
        );

        foreach ($this->fetchAll($sql) as $row) {
            $fieldKeyName   = 'Key_name';
            $fieldNonUnique = 'Non_unique';
            $fieldColumn    = 'Column_name';
            $fieldIndexType = 'Index_type';

            if (strtolower($row[$fieldKeyName]) == AdapterInterface::INDEX_TYPE_PRIMARY) {
                $indexType  = AdapterInterface::INDEX_TYPE_PRIMARY;
            } elseif ($row[$fieldNonUnique] == 0) {
                $indexType  = AdapterInterface::INDEX_TYPE_UNIQUE;
            } elseif (strtolower($row[$fieldIndexType]) == AdapterInterface::INDEX_TYPE_FULLTEXT) {
                $indexType  = AdapterInterface::INDEX_TYPE_FULLTEXT;
            } else {
                $indexType  = AdapterInterface::INDEX_TYPE_INDEX;
            }

            $upperKeyName = strtoupper($row[$fieldKeyName]);
            if (isset($ddl[$upperKeyName])) {
                $ddl[$upperKeyName]['fields'][] = $row[$fieldColumn]; // for compatible
                $ddl[$upperKeyName]['COLUMNS_LIST'][] = $row[$fieldColumn];
            } else {
                $ddl[$upperKeyName] = array(
                    'SCHEMA_NAME'   => $schemaName,
                    'TABLE_NAME'    => $tableName,
                    'KEY_NAME'      => $row[$fieldKeyName],
                    'COLUMNS_LIST'  => array($row[$fieldColumn]),
                    'INDEX_TYPE'    => $indexType,
                    'INDEX_METHOD'  => $row[$fieldIndexType],
                    'type'          => strtolower($indexType), // for compatibility
                    'fields'        => array($row[$fieldColumn]) // for compatibility
                );
            }
        }

        return $ddl;
    }

    /**
     * Remove duplicate entry for create key
     *
     * @param string $table
     * @param array $fields
     * @param string[] $ids
     * @return $this
     */
    protected function _removeDuplicateEntry($table, $fields, $ids)
    {
        $where = array();
        $i = 0;
        foreach ($fields as $field) {
            $where[] = $this->quoteInto($field . '=?', $ids[$i++]);
        }

        if (!$where) {
            return $this;
        }
        $whereCond = implode(' AND ', $where);
        $sql = sprintf('SELECT COUNT(*) as `cnt` FROM `%s` WHERE %s', $table, $whereCond);

        $cnt = $this->rawFetchRow($sql, 'cnt');
        if ($cnt > 1) {
            $sql = sprintf(
                'DELETE FROM `%s` WHERE %s LIMIT %d',
                $table,
                $whereCond,
                $cnt - 1
            );
            $this->rawQuery($sql);
        }

        return $this;
    }

    /**
     * Creates and returns a new \Zend\Db\Sql\Select object for this adapter.
     *
     * @return Sql/Select
     */
    public function select()
    {
        $sql = new Sql($this);
        return $sql->select();
    }

    /**
     * Start debug timer
     *
     * @return $this
     */
    protected function _debugTimer()
    {
        if ($this->_debug) {
            $this->_debugTimer = microtime(true);
        }

        return $this;
    }

    /**
     * Logging debug information
     *
     * @param int $type
     * @param string $sql
     * @param array $bind
     * @param StatementInterface $result
     * @return $this
     */
    protected function _debugStat($type, $sql, $bind = array(), $result = null)
    {
        if (!$this->_debug) {
            return $this;
        }

        $code = '## ' . getmypid() . ' ## ';
        $nl   = "\n";
        $time = sprintf('%.4f', microtime(true) - $this->_debugTimer);

        if (!$this->_logAllQueries && $time < $this->_logQueryTime) {
            return $this;
        }
        switch ($type) {
            case self::DEBUG_CONNECT:
                $code .= 'CONNECT' . $nl;
                break;
            case self::DEBUG_TRANSACTION:
                $code .= 'TRANSACTION ' . $sql . $nl;
                break;
            case self::DEBUG_QUERY:
                $code .= 'QUERY' . $nl;
                $code .= 'SQL: ' . $sql . $nl;
                if ($bind) {
                    $code .= 'BIND: ' . var_export($bind, true) . $nl;
                }
                if ($result instanceof StatementInterface) {
                    $code .= 'AFF: ' . $result->rowCount() . $nl;
                }
                break;
        }
        $code .= 'TIME: ' . $time . $nl;

        if ($this->_logCallStack) {
            $code .= 'TRACE: ' . Debug::backtrace(true, false) . $nl;
        }

        $code .= $nl;

        $this->_debugWriteToFile($code);

        return $this;
    }

    /**
     * Write exception and thow
     *
     * @param \Exception $e
     * @return void
     * @throws \Exception
     */
    protected function _debugException(\Exception $e)
    {
        if (!$this->_debug) {
            throw $e;
        }

        $nl   = "\n";
        $code = 'EXCEPTION ' . $nl . $e . $nl . $nl;
        $this->_debugWriteToFile($code);

        throw $e;
    }

    /**
     * Debug write to file process
     *
     * @param string $str
     * @return void
     */
    protected function _debugWriteToFile($str)
    {
        $str = '## ' . date('Y-m-d H:i:s') . "\r\n" . $str;
        $this->_filesystem->getDirectoryWrite('var')->writeFile($this->_debugFile, $str, FILE_APPEND | LOCK_EX);
    }

    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * Method revrited for handle empty arrays in value param
     *
     * @param string  $text  The text with a placeholder.
     * @param mixed   $value The value to quote.
     * @param string  $type  OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the orignal text.
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        if ($value instanceof \Zend\Db\Sql\Expression) {
            $value = $value->getExpression();
        }
        if ($count === null) {
            return str_replace('?', $this->quote($value, $type), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $this->quote($value, $type), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }

    /**
     * Retrieve ddl cache name
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     */
    protected function _getTableName($tableName, $schemaName = null)
    {
        return ($schemaName ? $schemaName . '.' : '') . $tableName;
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
    public function describeTable($tableName, $schemaName = null)
    {
        $desc = [];
        $ddl = new \Zend\Db\Metadata\Metadata($this);
        $ddlTable = $ddl->getTable($tableName, $schemaName);
        $constraints = $ddlTable->getConstraints();

        foreach ($ddlTable->getColumns() as $ddlColumn) {
            $primary = false;
            foreach ($constraints as $constraint) {
                $primary = in_array($ddlColumn->getName(), $constraint->getColumns()) && $constraint->isPrimaryKey();
            }
            $desc[$ddlColumn->getName()] = array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $ddlColumn->getName(),
                'COLUMN_POSITION'  => $ddlColumn->getOrdinalPosition(),
                'DATA_TYPE'        => $ddlColumn->getDataType(),
                'DEFAULT'          => $ddlColumn->getColumnDefault(),
                'NULLABLE'         => $ddlColumn->isNullable(),
                'LENGTH'           => $ddlColumn->getCharacterMaximumLength(),
                'SCALE'            => $ddlColumn->getNumericScale(),
                'PRECISION'        => $ddlColumn->getNumericPrecision(),
                'UNSIGNED'         => $ddlColumn->isNumericUnsigned(),
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => 0,
                'IDENTITY'         => false,
            );

        }

        return $desc;
    }

    /**
     * Format described column to definition, ready to be added to ddl table.
     * Return array with keys: name, type, length, options, comment
     *
     * @param  array $columnData
     * @return array
     */
    public function getColumnCreateByDescribe($columnData)
    {
        $type = $this->_getColumnTypeByDdl($columnData);
        $options = array();

        if ($columnData['IDENTITY'] === true) {
            $options['identity'] = true;
        }
        if ($columnData['UNSIGNED'] === true) {
            $options['unsigned'] = true;
        }
        if ($columnData['NULLABLE'] === false
            && !($type == Table::TYPE_TEXT && strlen($columnData['DEFAULT']) != 0)
        ) {
            $options['nullable'] = false;
        }
        if ($columnData['PRIMARY'] === true) {
            $options['primary'] = true;
        }
        if (!is_null($columnData['DEFAULT'])
            && $type != Table::TYPE_TEXT
        ) {
            $options['default'] = $this->quote($columnData['DEFAULT']);
        }
        if (strlen($columnData['SCALE']) > 0) {
            $options['scale'] = $columnData['SCALE'];
        }
        if (strlen($columnData['PRECISION']) > 0) {
            $options['precision'] = $columnData['PRECISION'];
        }

        $comment = ucwords(str_replace('_', ' ', $columnData['COLUMN_NAME']));

        $result = array(
            'name'      => $columnData['COLUMN_NAME'],
            'type'      => $type,
            'length'    => $columnData['LENGTH'],
            'options'   => $options,
            'comment'   => $comment
        );

        return $result;
    }

    /**
     * Create \Magento\Setup\Framework\DB\Ddl\Table object by data from describe table
     *
     * @param string $tableName
     * @param string $newTableName
     * @return Table
     */
    public function createTableByDdl($tableName, $newTableName)
    {
        $describe = $this->describeTable($tableName);
        $table = $this->newTable($newTableName)
            ->setComment(ucwords(str_replace('_', ' ', $newTableName)));

        foreach ($describe as $columnData) {
            $columnInfo = $this->getColumnCreateByDescribe($columnData);

            $table->addColumn(
                $columnInfo['name'],
                $columnInfo['type'],
                $columnInfo['length'],
                $columnInfo['options'],
                $columnInfo['comment']
            );
        }

        $indexes = $this->getIndexList($tableName);
        foreach ($indexes as $indexData) {
            /**
             * Do not create primary index - it is created with identity column.
             * For reliability check both name and type, because these values can start to differ in future.
             */
            if (($indexData['KEY_NAME'] == 'PRIMARY')
                || ($indexData['INDEX_TYPE'] == AdapterInterface::INDEX_TYPE_PRIMARY)
            ) {
                continue;
            }

            $fields = $indexData['COLUMNS_LIST'];
            $options = array('type' => $indexData['INDEX_TYPE']);
            $table->addIndex($this->getIndexName($newTableName, $fields, $indexData['INDEX_TYPE']), $fields, $options);
        }

        $foreignKeys = $this->getForeignKeys($tableName);
        foreach ($foreignKeys as $keyData) {
            $fkName = $this->getForeignKeyName(
                $newTableName,
                $keyData['COLUMN_NAME'],
                $keyData['REF_TABLE_NAME'],
                $keyData['REF_COLUMN_NAME']
            );
            $onDelete = $this->_getDdlAction($keyData['ON_DELETE']);
            $onUpdate = $this->_getDdlAction($keyData['ON_UPDATE']);

            $table->addForeignKey(
                $fkName,
                $keyData['COLUMN_NAME'],
                $keyData['REF_TABLE_NAME'],
                $keyData['REF_COLUMN_NAME'],
                $onDelete,
                $onUpdate
            );
        }

        // Set additional options
        $tableData = $this->showTableStatus($tableName);
        if (isset($tableData['Engine'])) {
            $table->setOption('type', $tableData['Engine']);
        }

        return $table;
    }

    /**
     * Modify the column definition by data from describe table
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return $this
     */
    public function modifyColumnByDdl($tableName, $columnName, $definition, $flushData = false, $schemaName = null)
    {
        $definition = array_change_key_case($definition, CASE_UPPER);
        $definition['COLUMN_TYPE'] = $this->_getColumnTypeByDdl($definition);
        if (array_key_exists('DEFAULT', $definition) && is_null($definition['DEFAULT'])) {
            unset($definition['DEFAULT']);
        }

        return $this->modifyColumn($tableName, $columnName, $definition, $flushData, $schemaName);
    }

    /**
     * Retrieve column data type by data from describe table
     *
     * @param array $column
     * @return string
     */
    protected function _getColumnTypeByDdl($column)
    {
        switch ($column['DATA_TYPE']) {
            case 'bool':
                return Table::TYPE_BOOLEAN;
            case 'tinytext':
            case 'char':
            case 'varchar':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return Table::TYPE_TEXT;
            case 'blob':
            case 'mediumblob':
            case 'longblob':
                return Table::TYPE_BLOB;
            case 'tinyint':
            case 'smallint':
                return Table::TYPE_SMALLINT;
            case 'mediumint':
            case 'int':
                return Table::TYPE_INTEGER;
            case 'bigint':
                return Table::TYPE_BIGINT;
            case 'datetime':
                return Table::TYPE_DATETIME;
            case 'timestamp':
                return Table::TYPE_TIMESTAMP;
            case 'date':
                return Table::TYPE_DATE;
            case 'float':
                return Table::TYPE_FLOAT;
            case 'decimal':
            case 'numeric':
                return Table::TYPE_DECIMAL;
        }
    }

    /**
     * Change table storage engine
     *
     * @param string $tableName
     * @param string $engine
     * @param string $schemaName
     * @return StatementInterface
     */
    public function changeTableEngine($tableName, $engine, $schemaName = null)
    {
        $table = $this->getPlatform()->quoteIdentifierChain($tableName);
        $sql   = sprintf('ALTER TABLE %s ENGINE=%s', $table, $engine);

        return $this->query($sql);
    }

    /**
     * Change table comment
     *
     * @param string $tableName
     * @param string $comment
     * @param string $schemaName
     * @return StatementInterface
     */
    public function changeTableComment($tableName, $comment, $schemaName = null)
    {
        $table = $this->getPlatform()->quoteIdentifierChain($tableName);
        $sql   = sprintf("ALTER TABLE %s COMMENT='%s'", $table, $comment);

        return $this->query($sql);
    }

    /**
     * Inserts a table row with specified data
     * Special for Zero values to identity column
     *
     * @param string $table
     * @param array $bind
     * @return int The number of affected rows.
     */
    public function insertForce($table, array $bind)
    {
        $this->rawQuery("SET @OLD_INSERT_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        $result = $this->insert($table, $bind);
        $this->rawQuery("SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'')");

        return $result;
    }

    /**
     * Return new DDL Table object
     *
     * @param string $tableName the table name
     * @param string $schemaName the database/schema name
     * @return Table
     */
    public function newTable($tableName = null, $schemaName = null)
    {
        $table = new Table();
        if ($tableName !== null) {
            $table->setName($tableName);
        }
        if ($schemaName !== null) {
            $table->setSchema($schemaName);
        }

        return $table;
    }

    /**
     * Create table
     *
     * @param Table $table
     * @throws ErrorException
     * @return ResultSet\ResultSetInterface
     */
    public function createTable(Table $table)
    {
        $columns = $table->getColumns();
        foreach ($columns as $columnEntry) {
            if (empty($columnEntry['COMMENT'])) {
                throw new ErrorException("Cannot create table without columns comments");
            }
        }

        $sqlFragment    = array_merge(
            $this->_getColumnsDefinition($table),
            $this->_getIndexesDefinition($table),
            $this->_getForeignKeysDefinition($table)
        );
        $tableOptions   = $this->_getOptionsDefinition($table);
        $sql = sprintf(
            "CREATE TABLE %s (\n%s\n) %s",
            $this->quoteIdentifier($table->getName()),
            implode(",\n", $sqlFragment),
            implode(" ", $tableOptions)
        );
        return $this->query($sql);
    }

    /**
     * Create temporary table
     *
     * @param Table $table
     * @return ResultSet\ResultSetInterface
     */
    public function createTemporaryTable(Table $table)
    {
        $columns = $table->getColumns();
        $sqlFragment    = array_merge(
            $this->_getColumnsDefinition($table),
            $this->_getIndexesDefinition($table),
            $this->_getForeignKeysDefinition($table)
        );
        $tableOptions   = $this->_getOptionsDefinition($table);
        $sql = sprintf(
            "CREATE TEMPORARY TABLE %s (\n%s\n) %s",
            $this->getPlatform()->quoteIdentifier($table->getName()),
            implode(",\n", $sqlFragment),
            implode(" ", $tableOptions)
        );

        return $this->query($sql);
    }

    /**
     * Rename several tables
     *
     * @param array $tablePairs array('oldName' => 'Name1', 'newName' => 'Name2')
     *
     * @return boolean
     * @throws ErrorException
     */
    public function renameTablesBatch(array $tablePairs)
    {
        if (count($tablePairs) == 0) {
            throw new ErrorException('Please provide tables for rename');
        }

        $renamesList = array();
        $tablesList  = array();
        foreach ($tablePairs as $pair) {
            $oldTableName  = $pair['oldName'];
            $newTableName  = $pair['newName'];
            $renamesList[] = sprintf('%s TO %s', $oldTableName, $newTableName);

            $tablesList[$oldTableName] = $oldTableName;
            $tablesList[$newTableName] = $newTableName;
        }

        $query = sprintf('RENAME TABLE %s', implode(',', $renamesList));
        $this->query($query);

        return true;
    }

    /**
     * Retrieve columns and primary keys definition array for create table
     *
     * @param Table $table
     * @return string[]
     * @throws ErrorException
     */
    protected function _getColumnsDefinition(Table $table)
    {
        $definition = array();
        $primary    = array();
        $columns    = $table->getColumns();
        if (empty($columns)) {
            throw new ErrorException('Table columns are not defined');
        }

        foreach ($columns as $columnData) {
            $columnDefinition = $this->_getColumnDefinition($columnData);
            if ($columnData['PRIMARY']) {
                $primary[$columnData['COLUMN_NAME']] = $columnData['PRIMARY_POSITION'];
            }

            $definition[] = sprintf(
                '  %s %s',
                $this->quoteIdentifier($columnData['COLUMN_NAME']),
                $columnDefinition
            );
        }

        // PRIMARY KEY
        if (!empty($primary)) {
            asort($primary, SORT_NUMERIC);
            $primary      = array_map(array($this->getPlatform(), 'quoteIdentifier'), array_keys($primary));
            $definition[] = sprintf('  PRIMARY KEY (%s)', implode(', ', $primary));
        }

        return $definition;
    }

    /**
     * Retrieve table indexes definition array for create table
     *
     * @param Table $table
     * @return string[]
     */
    protected function _getIndexesDefinition(Table $table)
    {
        $definition = array();
        $indexes    = $table->getIndexes();
        if (!empty($indexes)) {
            foreach ($indexes as $indexData) {
                if (!empty($indexData['TYPE'])) {
                    switch ($indexData['TYPE']) {
                        case 'primary':
                            $indexType = 'PRIMARY KEY';
                            unset($indexData['INDEX_NAME']);
                            break;
                        default:
                            $indexType = strtoupper($indexData['TYPE']);
                            break;
                    }
                } else {
                    $indexType = 'KEY';
                }

                $columns = array();
                foreach ($indexData['COLUMNS'] as $columnData) {
                    $column = $this->quoteIdentifier($columnData['NAME']);
                    if (!empty($columnData['SIZE'])) {
                        $column .= sprintf('(%d)', $columnData['SIZE']);
                    }
                    $columns[] = $column;
                }
                $indexName = isset($indexData['INDEX_NAME'])
                    ? $this->getPlatform()->quoteIdentifier($indexData['INDEX_NAME'])
                    : '';
                $definition[] = sprintf(
                    '  %s %s (%s)',
                    $indexType,
                    $indexName,
                    implode(', ', $columns)
                );
            }
        }

        return $definition;
    }

    /**
     * Retrieve table foreign keys definition array for create table
     *
     * @param Table $table
     * @return string[]
     */
    protected function _getForeignKeysDefinition(Table $table)
    {
        $definition = array();
        $relations  = $table->getForeignKeys();

        if (!empty($relations)) {
            foreach ($relations as $fkData) {
                $onDelete = $this->_getDdlAction($fkData['ON_DELETE']);
                $onUpdate = $this->_getDdlAction($fkData['ON_UPDATE']);

                $definition[] = sprintf(
                    '  CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
                    $this->quoteIdentifier($fkData['FK_NAME']),
                    $this->quoteIdentifier($fkData['COLUMN_NAME']),
                    $this->quoteIdentifier($fkData['REF_TABLE_NAME']),
                    $this->quoteIdentifier($fkData['REF_COLUMN_NAME']),
                    $onDelete,
                    $onUpdate
                );
            }
        }

        return $definition;
    }

    /**
     * Retrieve table options definition array for create table
     *
     * @param Table $table
     * @return string[]
     * @throws ErrorException
     */
    protected function _getOptionsDefinition(Table $table)
    {
        $definition = array();
        $comment    = $table->getComment();
        if (empty($comment)) {
            throw new ErrorException('Comment for table is required and must be defined');
        }
        $definition[] = $this->quoteInto('COMMENT=?', $comment);

        $tableProps = array(
            'type'              => 'ENGINE=%s',
            'checksum'          => 'CHECKSUM=%d',
            'auto_increment'    => 'AUTO_INCREMENT=%d',
            'avg_row_length'    => 'AVG_ROW_LENGTH=%d',
            'max_rows'          => 'MAX_ROWS=%d',
            'min_rows'          => 'MIN_ROWS=%d',
            'delay_key_write'   => 'DELAY_KEY_WRITE=%d',
            'row_format'        => 'row_format=%s',
            'charset'           => 'charset=%s',
            'collate'           => 'COLLATE=%s'
        );
        foreach ($tableProps as $key => $mask) {
            $v = $table->getOption($key);
            if ($v !== null) {
                $definition[] = sprintf($mask, $v);
            }
        }

        return $definition;
    }

    /**
     * Get column definition from description
     *
     * @param  array $options
     * @param  null|string $ddlType
     * @return string
     */
    public function getColumnDefinitionFromDescribe($options, $ddlType = null)
    {
        $columnInfo = $this->getColumnCreateByDescribe($options);
        foreach ($columnInfo['options'] as $key => $value) {
            $columnInfo[$key] = $value;
        }
        return $this->_getColumnDefinition($columnInfo, $ddlType);
    }

    /**
     * Retrieve column definition fragment
     *
     * @param array $options
     * @param string $ddlType Table DDL Column type constant
     * @return string
     * @throws ErrorException
     */
    protected function _getColumnDefinition($options, $ddlType = null)
    {
        // convert keys to uppercase
        $options    = array_change_key_case($options, CASE_UPPER);
        $cType      = null;
        $cUnsigned  = false;
        $cNullable  = true;
        $cDefault   = false;
        $cIdentity  = false;

        // detect and validate column type
        if ($ddlType === null) {
            $ddlType = $this->_getDdlType($options);
        }

        if (empty($ddlType) || !isset($this->_ddlColumnTypes[$ddlType])) {
            throw new ErrorException('Invalid column definition data');
        }

        // column size
        $cType = $this->_ddlColumnTypes[$ddlType];
        switch ($ddlType) {
            case Table::TYPE_SMALLINT:
            case Table::TYPE_INTEGER:
            case Table::TYPE_BIGINT:
                if (!empty($options['UNSIGNED'])) {
                    $cUnsigned = true;
                }
                break;
            case Table::TYPE_DECIMAL:
            case Table::TYPE_NUMERIC:
                $precision  = 10;
                $scale      = 0;
                $match      = array();
                if (!empty($options['LENGTH']) && preg_match('#^\(?(\d+),(\d+)\)?$#', $options['LENGTH'], $match)) {
                    $precision  = $match[1];
                    $scale      = $match[2];
                } else {
                    if (isset($options['SCALE']) && is_numeric($options['SCALE'])) {
                        $scale = $options['SCALE'];
                    }
                    if (isset($options['PRECISION']) && is_numeric($options['PRECISION'])) {
                        $precision = $options['PRECISION'];
                    }
                }
                $cType .= sprintf('(%d,%d)', $precision, $scale);
                break;
            case Table::TYPE_TEXT:
            case Table::TYPE_BLOB:
            case Table::TYPE_VARBINARY:
                if (empty($options['LENGTH'])) {
                    $length = Table::DEFAULT_TEXT_SIZE;
                } else {
                    $length = $this->_parseTextSize($options['LENGTH']);
                }
                if ($length <= 255) {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'varchar' : 'varbinary';
                    $cType = sprintf('%s(%d)', $cType, $length);
                } elseif ($length > 255 && $length <= 65536) {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'text' : 'blob';
                } elseif ($length > 65536 && $length <= 16777216) {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'mediumtext' : 'mediumblob';
                } else {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'longtext' : 'longblob';
                }
                break;
        }

        if (array_key_exists('DEFAULT', $options)) {
            $cDefault = $options['DEFAULT'];
        }
        if (array_key_exists('NULLABLE', $options)) {
            $cNullable = (bool)$options['NULLABLE'];
        }
        if (!empty($options['IDENTITY']) || !empty($options['AUTO_INCREMENT'])) {
            $cIdentity = true;
        }

        /*  For cases when tables created from createTableByDdl()
         *  where default value can be quoted already.
         *  We need to avoid "double-quoting" here
         */
        if ($cDefault !== null && strlen($cDefault)) {
            $cDefault = $this->quote(str_replace("'", '', $cDefault));
        }

        // prepare default value string
        if ($ddlType == Table::TYPE_TIMESTAMP) {
            if ($cDefault === null) {
                $cDefault = new Expression('NULL');
            } elseif (Table::TIMESTAMP_INIT == $cDefault) {
                $cDefault = new Expression('CURRENT_TIMESTAMP');
            } elseif ($cDefault == Table::TIMESTAMP_UPDATE) {
                $cDefault = new Expression('0 ON UPDATE CURRENT_TIMESTAMP');
            } elseif ($cDefault == Table::TIMESTAMP_INIT_UPDATE) {
                $cDefault = new Expression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
            } elseif ($cNullable && !$cDefault) {
                $cDefault = new Expression('NULL');
            } else {
                $cDefault = false;
            }
        } elseif (is_null($cDefault) && $cNullable) {
            $cDefault = new Expression('NULL');
        }

        if (empty($options['COMMENT'])) {
            $comment = '';
        } else {
            $comment = $options['COMMENT'];
        }

        //set column position
        $after = null;
        if (!empty($options['AFTER'])) {
            $after = $options['AFTER'];
        }

        if ($cDefault instanceof \Zend\Db\Sql\Expression) {
            $cDefault = $cDefault->getExpression();
        }

        return sprintf(
            '%s%s%s%s%s COMMENT %s %s',
            $cType,
            $cUnsigned ? ' UNSIGNED' : '',
            $cNullable ? ' NULL' : ' NOT NULL',
            $cDefault !== false ? ' default ' . $cDefault : '',
            $cIdentity ? ' auto_increment' : '',
            $this->quote($comment),
            $after ? 'AFTER ' . $this->quoteIdentifier($after) : ''
        );
    }

    /**
     * Drop table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return true
     */
    public function dropTable($tableName, $schemaName = null)
    {
        $table = $this->getPlatform()->quoteIdentifierChain($tableName);
        $query = 'DROP TABLE IF EXISTS ' . $table;
        $this->query($query);

        return true;
    }

    /**
     * Drop temporary table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function dropTemporaryTable($tableName, $schemaName = null)
    {
        $table = $this->getPlatform()->quoteIdentifierChain($tableName);
        $query = 'DROP TEMPORARY TABLE IF EXISTS ' . $table;
        $this->query($query);

        return true;
    }

    /**
     * Truncate a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     * @throws ErrorException
     */
    public function truncateTable($tableName, $schemaName = null)
    {
        if (!$this->isTableExists($tableName, $schemaName)) {
            throw new ErrorException(sprintf('Table "%s" is not exists', $tableName));
        }

        $table = $this->quoteIdentifier($this->_getTableName($tableName, $schemaName));
        $query = 'TRUNCATE TABLE ' . $table;
        $this->query($query);

        return $this;
    }

    /**
     * Check is a table exists
     *
     * @param string $tableName
     * @param string $schemaName
     * @return bool
     */
    public function isTableExists($tableName, $schemaName = null)
    {
        return $this->showTableStatus($tableName, $schemaName) !== false;
    }

    /**
     * Rename table
     *
     * @param string $oldTableName
     * @param string $newTableName
     * @param string $schemaName
     * @return true
     * @throws ErrorException
     */
    public function renameTable($oldTableName, $newTableName, $schemaName = null)
    {
        if (!$this->isTableExists($oldTableName, $schemaName)) {
            throw new ErrorException(sprintf('Table "%s" is not exists', $oldTableName));
        }
        if ($this->isTableExists($newTableName, $schemaName)) {
            throw new ErrorException(sprintf('Table "%s" already exists', $newTableName));
        }

        $oldTable = $this->_getTableName($oldTableName, $schemaName);
        $newTable = $this->_getTableName($newTableName, $schemaName);

        $query = sprintf('ALTER TABLE %s RENAME TO %s', $oldTable, $newTable);
        $this->query($query);

        return true;
    }

    /**
     * Add new index to table name
     *
     * @param string $tableName
     * @param string $indexName
     * @param string|array $fields  the table column name or array of ones
     * @param string $indexType     the index type
     * @param string $schemaName
     * @return StatementInterface
     * @throws ErrorException
     * @throws \Exception
     */
    public function addIndex(
        $tableName,
        $indexName,
        $fields,
        $indexType = AdapterInterface::INDEX_TYPE_INDEX,
        $schemaName = null
    ) {
        $columns = $this->describeTable($tableName, $schemaName);
        $keyList = $this->getIndexList($tableName, $schemaName);

        $query = sprintf('ALTER TABLE %s', $this->getPlatform()->quoteIdentifierChain($tableName));
        if (isset($keyList[strtoupper($indexName)])) {
            if ($keyList[strtoupper($indexName)]['INDEX_TYPE'] == AdapterInterface::INDEX_TYPE_PRIMARY) {
                $query .= ' DROP PRIMARY KEY,';
            } else {
                $query .= sprintf(' DROP INDEX %s,', $this->quoteIdentifier($indexName));
            }
        }

        if (!is_array($fields)) {
            $fields = array($fields);
        }

        $fieldSql = array();
        foreach ($fields as $field) {
            if (!isset($columns[$field])) {
                $msg = sprintf(
                    'There is no field "%s" that you are trying to create an index on "%s"',
                    $field,
                    $tableName
                );
                throw new ErrorException($msg);
            }
            $fieldSql[] = $this->quoteIdentifier($field);
        }
        $fieldSql = implode(',', $fieldSql);

        switch (strtolower($indexType)) {
            case AdapterInterface::INDEX_TYPE_PRIMARY:
                $condition = 'PRIMARY KEY';
                break;
            case AdapterInterface::INDEX_TYPE_UNIQUE:
                $condition = 'UNIQUE ' . $this->quoteIdentifier($indexName);
                break;
            case AdapterInterface::INDEX_TYPE_FULLTEXT:
                $condition = 'FULLTEXT ' . $this->quoteIdentifier($indexName);
                break;
            default:
                $condition = 'INDEX ' . $this->quoteIdentifier($indexName);
                break;
        }

        $query .= sprintf(' ADD %s (%s)', $condition, $fieldSql);

        $cycle = true;
        while ($cycle === true) {
            try {
                $result = $this->rawQuery($query);
                $cycle  = false;
            } catch (\Exception $e) {
                if (in_array(strtolower($indexType), array('primary', 'unique'))) {
                    $match = array();
                    if (preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\']+\'([\d-\.]+)\'#', $e->getMessage(), $match)) {
                        $ids = explode('-', $match[1]);
                        $this->_removeDuplicateEntry($tableName, $fields, $ids);
                        continue;
                    }
                }
                throw $e;
            }
        }

        return $result;
    }

    /**
     * Drop the index from table
     *
     * @param string $tableName
     * @param string $keyName
     * @param string $schemaName
     * @return true|StatementInterface
     */
    public function dropIndex($tableName, $keyName, $schemaName = null)
    {
        $indexList = $this->getIndexList($tableName, $schemaName);
        $keyName = strtoupper($keyName);
        if (!isset($indexList[$keyName])) {
            return true;
        }

        if ($keyName == 'PRIMARY') {
            $cond = 'DROP PRIMARY KEY';
        } else {
            $cond = 'DROP KEY ' . $this->quoteIdentifier($indexList[$keyName]['KEY_NAME']);
        }
        $sql = sprintf(
            'ALTER TABLE %s %s',
            $this->getPlatform()->quoteIdentifierChain($tableName),
            $cond
        );

        return $this->rawQuery($sql);
    }

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
     * @param bool $purge            trying remove invalid data
     * @param string $schemaName
     * @param string $refSchemaName
     * @return StatementInterface
     */
    public function addForeignKey(
        $fkName,
        $tableName,
        $columnName,
        $refTableName,
        $refColumnName,
        $onDelete = AdapterInterface::FK_ACTION_CASCADE,
        $onUpdate = AdapterInterface::FK_ACTION_CASCADE,
        $purge = false,
        $schemaName = null,
        $refSchemaName = null
    ) {
        $this->dropForeignKey($tableName, $fkName, $schemaName);

        if ($purge) {
            $this->purgeOrphanRecords($tableName, $columnName, $refTableName, $refColumnName, $onDelete);
        }

        $query = sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)',
            $this->getPlatform()->quoteIdentifierChain($tableName),
            $this->quoteIdentifier($fkName),
            $this->quoteIdentifier($columnName),
            $this->getPlatform()->quoteIdentifierChain($refTableName),
            $this->quoteIdentifier($refColumnName)
        );

        if ($onDelete !== null) {
            $query .= ' ON DELETE ' . strtoupper($onDelete);
        }
        if ($onUpdate  !== null) {
            $query .= ' ON UPDATE ' . strtoupper($onUpdate);
        }

        $result = $this->rawQuery($query);
        return $result;
    }

    /**
     * Run additional environment before setup
     *
     * @return $this
     */
    public function startSetup()
    {
        $this->rawQuery("SET SQL_MODE=''");
        $this->rawQuery("SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0");
        $this->rawQuery("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

        return $this;
    }

    /**
     * Run additional environment after setup
     *
     * @return $this
     */
    public function endSetup()
    {
        $this->rawQuery("SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'')");
        $this->rawQuery("SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS=0, 0, 1)");

        return $this;
    }

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array is - one of the following structures is expected:
     * - array("from" => $fromValue, "to" => $toValue)
     * - array("eq" => $equalValue)
     * - array("neq" => $notEqualValue)
     * - array("like" => $likeValue)
     * - array("in" => array($inValues))
     * - array("nin" => array($notInValues))
     * - array("notnull" => $valueIsNotNull)
     * - array("null" => $valueIsNull)
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
    public function prepareSqlCondition($fieldName, $condition)
    {
        $conditionKeyMap = array(
            'eq'            => "{{fieldName}} = ?",
            'neq'           => "{{fieldName}} != ?",
            'like'          => "{{fieldName}} LIKE ?",
            'nlike'         => "{{fieldName}} NOT LIKE ?",
            'in'            => "{{fieldName}} IN(?)",
            'nin'           => "{{fieldName}} NOT IN(?)",
            'is'            => "{{fieldName}} IS ?",
            'notnull'       => "{{fieldName}} IS NOT NULL",
            'null'          => "{{fieldName}} IS NULL",
            'gt'            => "{{fieldName}} > ?",
            'lt'            => "{{fieldName}} < ?",
            'gteq'          => "{{fieldName}} >= ?",
            'lteq'          => "{{fieldName}} <= ?",
            'finset'        => "FIND_IN_SET(?, {{fieldName}})",
            'regexp'        => "{{fieldName}} REGEXP ?",
            'from'          => "{{fieldName}} >= ?",
            'to'            => "{{fieldName}} <= ?",
            'seq'           => null,
            'sneq'          => null
        );

        $query = '';
        if (is_array($condition)) {
            if (isset($condition['field_expr'])) {
                $fieldName = str_replace('#?', $this->quoteIdentifier($fieldName), $condition['field_expr']);
                unset($condition['field_expr']);
            }
            $key = key(array_intersect_key($condition, $conditionKeyMap));

            if (isset($condition['from']) || isset($condition['to'])) {
                if (isset($condition['from'])) {
                    $from  = $this->_prepareSqlDateCondition($condition, 'from');
                    $query = $this->_prepareQuotedSqlCondition($conditionKeyMap['from'], $from, $fieldName);
                }

                if (isset($condition['to'])) {
                    $query .= empty($query) ? '' : ' AND ';
                    $to     = $this->_prepareSqlDateCondition($condition, 'to');
                    $query = $this->_prepareQuotedSqlCondition($query . $conditionKeyMap['to'], $to, $fieldName);
                }
            } elseif (array_key_exists($key, $conditionKeyMap)) {
                $value = $condition[$key];
                if (($key == 'seq') || ($key == 'sneq')) {
                    $key = $this->_transformStringSqlCondition($key, $value);
                }
                $query = $this->_prepareQuotedSqlCondition($conditionKeyMap[$key], $value, $fieldName);
            } else {
                $queries = array();
                foreach ($condition as $orCondition) {
                    $queries[] = sprintf('(%s)', $this->prepareSqlCondition($fieldName, $orCondition));
                }

                $query = sprintf('(%s)', implode(' OR ', $queries));
            }
        } else {
            $query = $this->_prepareQuotedSqlCondition($conditionKeyMap['eq'], (string)$condition, $fieldName);
        }

        return $query;
    }

    /**
     * Prepare Sql condition
     *
     * @param  string $text Condition value
     * @param  mixed $value
     * @param  string $fieldName
     * @return string
     */
    protected function _prepareQuotedSqlCondition($text, $value, $fieldName)
    {
        $sql = $this->quoteInto($text, $value);
        $sql = str_replace('{{fieldName}}', $fieldName, $sql);
        return $sql;
    }

    /**
     * Transforms sql condition key 'seq' / 'sneq' that is used for comparing string values to its analog:
     * - 'null' / 'notnull' for empty strings
     * - 'eq' / 'neq' for non-empty strings
     *
     * @param string $conditionKey
     * @param mixed $value
     * @return string
     */
    protected function _transformStringSqlCondition($conditionKey, $value)
    {
        $value = (string) $value;
        if ($value == '') {
            return ($conditionKey == 'seq') ? 'null' : 'notnull';
        } else {
            return ($conditionKey == 'seq') ? 'eq' : 'neq';
        }
    }

    /**
     * Get Interval Unit SQL fragment
     *
     * @param int $interval
     * @param string $unit
     * @return string
     * @throws ErrorException
     */
    protected function _getIntervalUnitSql($interval, $unit)
    {
        if (!isset($this->_intervalUnits[$unit])) {
            throw new ErrorException(sprintf('Undefined interval unit "%s" specified', $unit));
        }

        return sprintf('INTERVAL %d %s', $interval, $this->_intervalUnits[$unit]);
    }

    /**
     * Add time values (intervals) to a date value
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param int $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     *
     * @see INTERVAL_* constants for $unit
     */
    public function getDateAddSql($date, $interval, $unit)
    {
        $expr = sprintf('DATE_ADD(%s, %s)', $date, $this->_getIntervalUnitSql($interval, $unit));
        return new Expression($expr);
    }

    /**
     * Subtract time values (intervals) to a date value
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param int|string $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     * @see INTERVAL_* constants for $expr
     */
    public function getDateSubSql($date, $interval, $unit)
    {
        $expr = sprintf('DATE_SUB(%s, %s)', $date, $this->_getIntervalUnitSql($interval, $unit));
        return new Expression($expr);
    }

    /**
     * Minus superfluous characters from hash.
     *
     * @param  string $hash
     * @param  string $prefix
     * @param  int $maxCharacters
     * @return string
     */
    protected function _minusSuperfluous($hash, $prefix, $maxCharacters)
    {
         $diff        = strlen($hash) + strlen($prefix) -  $maxCharacters;
         $superfluous = $diff / 2;
         $odd         = $diff % 2;
         $hash        = substr($hash, $superfluous, - ($superfluous + $odd));
         return $hash;
    }

    /**
     * Retrieve valid table name
     * Check table name length and allowed symbols
     *
     * @param string $tableName
     * @return string
     */
    public function getTableName($tableName)
    {
        $prefix = 't_';
        if (strlen($tableName) > self::LENGTH_TABLE_NAME) {
            $shortName = ExpressionConverter::shortName($tableName);
            if (strlen($shortName) > self::LENGTH_TABLE_NAME) {
                $hash = md5($tableName);
                if (strlen($prefix.$hash) > self::LENGTH_TABLE_NAME) {
                    $tableName = $this->_minusSuperfluous($hash, $prefix, self::LENGTH_TABLE_NAME);
                } else {
                    $tableName = $prefix . $hash;
                }
            } else {
                $tableName = $shortName;
            }
        }

        return $tableName;
    }

    /**
     * Retrieve valid index name
     * Check index name length and allowed symbols
     *
     * @param string $tableName
     * @param string|string[] $fields  the columns list
     * @param string $indexType
     * @return string
     */
    public function getIndexName($tableName, $fields, $indexType = '')
    {
        if (is_array($fields)) {
            $fields = implode('_', $fields);
        }

        switch (strtolower($indexType)) {
            case AdapterInterface::INDEX_TYPE_UNIQUE:
                $prefix = 'unq_';
                $shortPrefix = 'u_';
                break;
            case AdapterInterface::INDEX_TYPE_FULLTEXT:
                $prefix = 'fti_';
                $shortPrefix = 'f_';
                break;
            case AdapterInterface::INDEX_TYPE_INDEX:
            default:
                $prefix = 'idx_';
                $shortPrefix = 'i_';
        }

        $hash = $tableName . '_' . $fields;

        if (strlen($hash) + strlen($prefix) > self::LENGTH_INDEX_NAME) {
            $short = ExpressionConverter::shortName($prefix . $hash);
            if (strlen($short) > self::LENGTH_INDEX_NAME) {
                $hash = md5($hash);
                if (strlen($hash) + strlen($shortPrefix) > self::LENGTH_INDEX_NAME) {
                    $hash = $this->_minusSuperfluous($hash, $shortPrefix, self::LENGTH_INDEX_NAME);
                }
            } else {
                $hash = $short;
            }
        } else {
            $hash = $prefix . $hash;
        }

        return strtoupper($hash);
    }

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
    public function getForeignKeyName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        $prefix = 'fk_';
        $hash = sprintf('%s_%s_%s_%s', $priTableName, $priColumnName, $refTableName, $refColumnName);
        if (strlen($prefix.$hash) > self::LENGTH_FOREIGN_NAME) {
            $short = ExpressionConverter::shortName($prefix . $hash);
            if (strlen($short) > self::LENGTH_FOREIGN_NAME) {
                $hash = md5($hash);
                if (strlen($prefix.$hash) > self::LENGTH_FOREIGN_NAME) {
                    $hash = $this->_minusSuperfluous($hash, $prefix, self::LENGTH_FOREIGN_NAME);
                } else {
                    $hash = $prefix . $hash;
                }
            } else {
                $hash = $short;
            }
        } else {
            $hash = $prefix . $hash;
        }

        return strtoupper($hash);
    }

    /**
     * Stop updating indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     */
    public function disableTableKeys($tableName, $schemaName = null)
    {
        $query     = sprintf(
            'ALTER TABLE %s DISABLE KEYS',
            $this->getPlatform()->quoteIdentifierChain($tableName)
        );
        $this->query($query);

        return $this;
    }

    /**
     * Re-create missing indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     */
    public function enableTableKeys($tableName, $schemaName = null)
    {
        $tableName = $this->_getTableName($tableName, $schemaName);
        $query     = sprintf(
            'ALTER TABLE %s ENABLE KEYS',
            $this->getPlatform()->quoteIdentifierChain($tableName)
        );
        $this->query($query);

        return $this;
    }

    /**
     * Calculate checksum for table or for group of tables
     *
     * @param array|string $tableNames array of tables names | table name
     * @param string $schemaName schema name
     * @return array
     */
    public function getTablesChecksum($tableNames, $schemaName = null)
    {
        $result     = array();
        $tableNames = is_array($tableNames) ? $tableNames : array($tableNames);

        foreach ($tableNames as $tableName) {
            $query = 'CHECKSUM TABLE ' . $this->_getTableName($tableName, $schemaName);
            $checkSumArray      = $this->fetchRow($query);
            $result[$tableName] = $checkSumArray['Checksum'];
        }

        return $result;
    }

    /**
     * Render SQL FOR UPDATE clause
     *
     * @param string $sql
     * @return string
     */
    public function forUpdate($sql)
    {
        return sprintf('%s FOR UPDATE', $sql);
    }

    /**
     * Prepare insert data
     *
     * @param mixed $row
     * @param array &$bind
     * @return string
     */
    protected function _prepareInsertData($row, &$bind)
    {
        $row = (array)$row;
        $line = array();
        foreach ($row as $value) {
            if ($value instanceof \Zend_Db_Expr) {
                $line[] = $value->__toString();
            } else {
                $line[] = '?';
                $bind[] = $value;
            }
        }
        $line = implode(', ', $line);

        return sprintf('(%s)', $line);
    }

    /**
     * Return ddl type
     *
     * @param array $options
     * @return string
     */
    protected function _getDdlType($options)
    {
        $ddlType = null;
        if (isset($options['TYPE'])) {
            $ddlType = $options['TYPE'];
        } elseif (isset($options['COLUMN_TYPE'])) {
            $ddlType = $options['COLUMN_TYPE'];
        }

        return $ddlType;
    }

    /**
     * Return DDL action
     *
     * @param string $action
     * @return string
     */
    protected function _getDdlAction($action)
    {
        switch ($action) {
            case AdapterInterface::FK_ACTION_CASCADE:
                return Table::ACTION_CASCADE;
            case AdapterInterface::FK_ACTION_SET_NULL:
                return Table::ACTION_SET_NULL;
            case AdapterInterface::FK_ACTION_RESTRICT:
                return Table::ACTION_RESTRICT;
            default:
                return Table::ACTION_NO_ACTION;
        }
    }

    /**
     * Prepare sql date condition
     *
     * @param array $condition
     * @param string $key
     * @return string
     */
    protected function _prepareSqlDateCondition($condition, $key)
    {
        if (empty($condition['date'])) {
            if (empty($condition['datetime'])) {
                $result = $condition[$key];
            } else {
                $result = $this->formatDate($condition[$key]);
            }
        } else {
            $result = $this->formatDate($condition[$key]);
        }

        return $result;
    }

    /**
     * Try to find installed primary key name, if not - formate new one.
     *
     * @param string $tableName Table name
     * @param string $schemaName OPTIONAL
     * @return string Primary Key name
     */
    public function getPrimaryKeyName($tableName, $schemaName = null)
    {
        $indexes = $this->getIndexList($tableName, $schemaName);
        if (isset($indexes['PRIMARY'])) {
            return $indexes['PRIMARY']['KEY_NAME'];
        } else {
            return 'PK_' . strtoupper($tableName);
        }
    }

    /**
     * Parse text size
     * Returns max allowed size if value great it
     *
     * @param string|int $size
     * @return int
     */
    protected function _parseTextSize($size)
    {
        $size = trim($size);
        $last = strtolower(substr($size, -1));

        switch ($last) {
            case 'k':
                $size = intval($size) * 1024;
                break;
            case 'm':
                $size = intval($size) * 1024 * 1024;
                break;
            case 'g':
                $size = intval($size) * 1024 * 1024 * 1024;
                break;
        }

        if (empty($size)) {
            return Table::DEFAULT_TEXT_SIZE;
        }
        if ($size >= Table::MAX_TEXT_SIZE) {
            return Table::MAX_TEXT_SIZE;
        }

        return intval($size);
    }

    /**
     * Converts fetched blob into raw binary PHP data.
     * The MySQL drivers do it nice, no processing required.
     *
     * @param mixed $value
     * @return mixed
     */
    public function decodeVarbinary($value)
    {
        return $value;
    }

    /**
     * Create trigger
     *
     * @param \Magento\Setup\Framework\DB\Ddl\Trigger $trigger
     * @throws ErrorException
     * @return StatementInterface
     */
    public function createTrigger(\Magento\Setup\Framework\DB\Ddl\Trigger $trigger)
    {
        if (!$trigger->getStatements()) {
            throw new ErrorException(sprintf(__('Trigger %s has not statements available'), $trigger->getName()));
        }

        $statements = implode("\n", $trigger->getStatements());

        $sql = sprintf(
            "CREATE TRIGGER %s %s %s ON %s FOR EACH ROW\nBEGIN\n%s\nEND",
            $trigger->getName(),
            $trigger->getTime(),
            $trigger->getEvent(),
            $trigger->getTable(),
            $statements
        );

        return $this->query($sql);
    }

    /**
     * Drop trigger from database
     *
     * @param string $triggerName
     * @param string|null $schemaName
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function dropTrigger($triggerName, $schemaName = null)
    {
        if (empty($triggerName)) {
            throw new \InvalidArgumentException(__('Trigger name is not defined'));
        }

        $triggerName = ($schemaName ? $schemaName . '.' : '') . $triggerName;

        $sql = 'DROP TRIGGER IF EXISTS ' . $this->quoteIdentifier($triggerName);
        $this->query($sql);

        return true;
    }

    /**
     * Check if all transactions have been committed
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_transactionLevel > 0) {
            trigger_error('Some transactions have not been committed or rolled back', E_USER_ERROR);
        }
    }

    /**
     * Quotes an identifier.
     *
     * @param array|string|\Zend_Db_Expr $ident
     * @param bool $auto
     * @return string
     */
    public function quoteIdentifier($ident, $auto = false)
    {
        return $this->getPlatform()->quoteIdentifier($ident);
    }

    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string.
     *
     * @param mixed $value The value to quote.
     * @param mixed $type OPTIONAL the SQL datatype name, or constant, or null.
     * @return mixed An SQL-safe quoted value (or string of separated values).
     */
    public function quote($value, $type = null)
    {
        return $this->getPlatform()->quoteValueList($value);
    }

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed $table The table to update.
     * @param  mixed $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '')
    {
        $sql = new Sql($this);
        $delete = $sql->delete($this->_getTableName($table));
        $delete->where($where);

        $sqlString = $sql->getSqlStringForSqlObject($delete);
        $result = $this->query($sqlString);
        return $result->count();
    }

    /**
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  mixed $table The table to update.
     * @param  array $bind Column-value pairs.
     * @param  mixed $where UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, array $bind, $where = '')
    {
        $sql = new Sql($this);
        $update = $sql->update($this->_getTableName($table));
        $update->where($where);
        $update->set($bind);
        $updateString = $sql->getSqlStringForSqlObject($update);
        $result = $this->query($updateString);
        return $result->count();
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @param boolean $onDuplicate
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind, $onDuplicate = false)
    {
        $sql = new Sql($this);
        $insert = $sql->insert($this->_getTableName($table));
        $insert->values($bind);

        $sqlString = $sql->getSqlStringForSqlObject($insert);
        if ($onDuplicate) {
            $sqlString .= ' ON DUPLICATE KEY UPDATE ';
            $parts = [];
            foreach (array_keys($bind) as $filed) {
                $parts[] = $this->quoteIdentifier($filed) . '=VALUES(' . $this->quoteIdentifier($filed) . ')';
            }
            $sqlString .= implode(', ', $parts);
        }

        $result = $this->query($sqlString);
        return $result->count();
    }
}
