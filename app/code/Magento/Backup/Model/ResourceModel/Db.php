<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Model\ResourceModel;

/**
 * Database backup resource model
 * @api
 * @since 2.0.0
 */
class Db
{
    /**
     * Database connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $connection;

    /**
     * Tables foreign key data array
     * [tbl_name] = array(create foreign key strings)
     *
     * @var array
     * @since 2.0.0
     */
    protected $_foreignKeys = [];

    /**
     * Backup resource helper
     *
     * @var \Magento\Backup\Model\ResourceModel\Helper
     * @since 2.0.0
     */
    protected $_resourceHelper;

    /**
     * Initialize Backup DB resource model
     *
     * @param \Magento\Backup\Model\ResourceModel\HelperFactory $resHelperFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backup\Model\ResourceModel\HelperFactory $resHelperFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_resourceHelper = $resHelperFactory->create();
        $this->connection = $resource->getConnection('backup');
    }

    /**
     * Clear data
     *
     * @return void
     * @since 2.0.0
     */
    public function clear()
    {
        $this->_foreignKeys = [];
    }

    /**
     * Retrieve table list
     *
     * @return array
     * @since 2.0.0
     */
    public function getTables()
    {
        return $this->connection->listTables();
    }

    /**
     * Retrieve SQL fragment for drop table
     *
     * @param string $tableName
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @return \Magento\Framework\DataObject|bool
     * @since 2.0.0
     */
    public function getTableStatus($tableName)
    {
        $row = $this->connection->showTableStatus($tableName);

        if ($row) {
            $statusObject = new \Magento\Framework\DataObject();
            foreach ($row as $field => $value) {
                $statusObject->setData(strtolower($field), $value);
            }

            $cntRow = $this->connection->fetchRow($this->connection->select()->from($tableName, 'COUNT(1) as rows'));
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTableHeader($tableName)
    {
        $quotedTableName = $this->connection->quoteIdentifier($tableName);
        return "\n--\n" . "-- Table structure for table {$quotedTableName}\n" . "--\n\n";
    }

    /**
     * Return table data dump
     *
     * @param string $tableName
     * @param bool $step
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getTableDataDump($tableName, $step = false)
    {
        return $this->getTableDataSql($tableName);
    }

    /**
     * Returns SQL header data
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeader()
    {
        return $this->_resourceHelper->getHeader();
    }

    /**
     * Returns SQL footer data
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTableDataAfterSql($tableName)
    {
        return $this->_resourceHelper->getTableDataAfterSql($tableName);
    }

    /**
     * Start transaction mode
     *
     * @return $this
     * @since 2.0.0
     */
    public function beginTransaction()
    {
        $this->_resourceHelper->prepareTransactionIsolationLevel();
        $this->connection->beginTransaction();
        return $this;
    }

    /**
     * Commit transaction
     *
     * @return $this
     * @since 2.0.0
     */
    public function commitTransaction()
    {
        $this->connection->commit();
        $this->_resourceHelper->restoreTransactionIsolationLevel();
        return $this;
    }

    /**
     * Rollback transaction
     *
     * @return $this
     * @since 2.0.0
     */
    public function rollBackTransaction()
    {
        $this->connection->rollBack();
        $this->_resourceHelper->restoreTransactionIsolationLevel();
        return $this;
    }

    /**
     * Run sql code
     *
     * @param string $command
     * @return $this
     * @since 2.0.0
     */
    public function runCommand($command)
    {
        $this->connection->query($command);
        return $this;
    }
}
