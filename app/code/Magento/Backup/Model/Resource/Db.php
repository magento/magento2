<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Model\Resource;

/**
 * Database backup resource model
 */
class Db
{
    /**
     * Database connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_write;

    /**
     * Tables foreign key data array
     * [tbl_name] = array(create foreign key strings)
     *
     * @var array
     */
    protected $_foreignKeys = [];

    /**
     * Backup resource helper
     *
     * @var \Magento\Backup\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * Initialize Backup DB resource model
     *
     * @param \Magento\Backup\Model\Resource\HelperFactory $resHelperFactory
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        \Magento\Backup\Model\Resource\HelperFactory $resHelperFactory,
        \Magento\Framework\App\Resource $resource
    ) {
        $this->_resourceHelper = $resHelperFactory->create();
        $this->_write = $resource->getConnection('backup_write');
    }

    /**
     * Clear data
     *
     * @return void
     */
    public function clear()
    {
        $this->_foreignKeys = [];
    }

    /**
     * Retrieve table list
     *
     * @return array
     */
    public function getTables()
    {
        return $this->_write->listTables();
    }

    /**
     * Retrieve SQL fragment for drop table
     *
     * @param string $tableName
     * @return string
     */
    public function getTableDropSql($tableName)
    {
        return $this->_resourceHelper->getTableDropSql($tableName);
    }

    /**
     * Retrieve SQL fragment for create table
     *
     * @param string $tableName
     * @param bool $withForeignKeys
     * @return string
     */
    public function getTableCreateSql($tableName, $withForeignKeys = false)
    {
        return $this->_resourceHelper->getTableCreateSql($tableName, $withForeignKeys = false);
    }

    /**
     * Retrieve foreign keys for table(s)
     *
     * @param string|null $tableName
     * @return string
     */
    public function getTableForeignKeysSql($tableName = null)
    {
        $fkScript = '';
        if (!$tableName) {
            $tables = $this->getTables();
            foreach ($tables as $table) {
                $tableFkScript = $this->_resourceHelper->getTableForeignKeysSql($table);
                if (!empty($tableFkScript)) {
                    $fkScript .= "\n" . $tableFkScript;
                }
            }
        } else {
            $fkScript = $this->getTableForeignKeysSql($tableName);
        }
        return $fkScript;
    }

    /**
     * Retrieve table status
     *
     * @param string $tableName
     * @return \Magento\Framework\Object|bool
     */
    public function getTableStatus($tableName)
    {
        $row = $this->_write->showTableStatus($tableName);

        if ($row) {
            $statusObject = new \Magento\Framework\Object();
            $statusObject->setIdFieldName('name');
            foreach ($row as $field => $value) {
                $statusObject->setData(strtolower($field), $value);
            }

            $cntRow = $this->_write->fetchRow($this->_write->select()->from($tableName, 'COUNT(1) as rows'));
            $statusObject->setRows($cntRow['rows']);

            return $statusObject;
        }

        return false;
    }

    /**
     * Retrieve table partial data SQL insert
     *
     * @param string $tableName
     * @param null|int $count
     * @param null|int $offset
     * @return string
     */
    public function getTableDataSql($tableName, $count = null, $offset = null)
    {
        return $this->_resourceHelper->getPartInsertSql($tableName, $count, $offset);
    }

    /**
     * Enter description here...
     *
     * @param string|array|\Zend_Db_Expr $tableName
     * @param bool $addDropIfExists
     * @return string
     */
    public function getTableCreateScript($tableName, $addDropIfExists = false)
    {
        return $this->_resourceHelper->getTableCreateScript($tableName, $addDropIfExists);
    }

    /**
     * Retrieve table header comment
     *
     * @param string $tableName
     * @return string
     */
    public function getTableHeader($tableName)
    {
        $quotedTableName = $this->_write->quoteIdentifier($tableName);
        return "\n--\n" . "-- Table structure for table {$quotedTableName}\n" . "--\n\n";
    }

    /**
     * Return table data dump
     *
     * @param string $tableName
     * @param bool $step
     * @return string
     */
    public function getTableDataDump($tableName, $step = false)
    {
        return $this->getTableDataSql($tableName);
    }

    /**
     * Returns SQL header data
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->_resourceHelper->getHeader();
    }

    /**
     * Returns SQL footer data
     *
     * @return string
     */
    public function getFooter()
    {
        return $this->_resourceHelper->getFooter();
    }

    /**
     * Retrieve before insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function getTableDataBeforeSql($tableName)
    {
        return $this->_resourceHelper->getTableDataBeforeSql($tableName);
    }

    /**
     * Retrieve after insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function getTableDataAfterSql($tableName)
    {
        return $this->_resourceHelper->getTableDataAfterSql($tableName);
    }

    /**
     * Start transaction mode
     *
     * @return $this
     */
    public function beginTransaction()
    {
        $this->_resourceHelper->prepareTransactionIsolationLevel();
        $this->_write->beginTransaction();
        return $this;
    }

    /**
     * Commit transaction
     *
     * @return $this
     */
    public function commitTransaction()
    {
        $this->_write->commit();
        $this->_resourceHelper->restoreTransactionIsolationLevel();
        return $this;
    }

    /**
     * Rollback transaction
     *
     * @return $this
     */
    public function rollBackTransaction()
    {
        $this->_write->rollBack();
        $this->_resourceHelper->restoreTransactionIsolationLevel();
        return $this;
    }

    /**
     * Run sql code
     *
     * @param string $command
     * @return $this
     */
    public function runCommand($command)
    {
        $this->_write->query($command);
        return $this;
    }
}
