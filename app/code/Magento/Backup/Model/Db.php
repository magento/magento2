<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Model;

use Magento\Backup\Helper\Data as Helper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\RuntimeException;

/**
 * Database backup model
 *
 * @api
 * @since 100.0.2
 * @deprecated Backup module is to be removed.
 */
class Db implements \Magento\Framework\Backup\Db\BackupDbInterface
{
    /**
     * Buffer length for multi rows
     * default 100 Kb
     */
    const BUFFER_LENGTH = 102400;

    /**
     * Backup resource model
     *
     * @var \Magento\Backup\Model\ResourceModel\Db
     */
    protected $_resourceDb = null;

    /**
     * Core resource model
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource = null;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param \Magento\Backup\Model\ResourceModel\Db $resourceDb
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Helper|null $helper
     */
    public function __construct(
        \Magento\Backup\Model\ResourceModel\Db $resourceDb,
        \Magento\Framework\App\ResourceConnection $resource,
        ?Helper $helper = null
    ) {
        $this->_resourceDb = $resourceDb;
        $this->_resource = $resource;
        $this->helper = $helper ?? ObjectManager::getInstance()->get(Helper::class);
    }

    /**
     * List of tables which data should not be backed up
     *
     * @var array
     */
    protected $_ignoreDataTablesList = ['importexport/importdata'];

    /**
     * Retrieve resource model
     *
     * @return \Magento\Backup\Model\ResourceModel\Db
     */
    public function getResource()
    {
        return $this->_resourceDb;
    }

    /**
     * Tables list.
     *
     * @return array
     */
    public function getTables()
    {
        return $this->getResource()->getTables();
    }

    /**
     * Command to recreate given table.
     *
     * @param string $tableName
     * @param bool $addDropIfExists
     * @return string
     */
    public function getTableCreateScript($tableName, $addDropIfExists = false)
    {
        return $this->getResource()->getTableCreateScript($tableName, $addDropIfExists);
    }

    /**
     * Generate table's data dump.
     *
     * @param string $tableName
     * @return string
     */
    public function getTableDataDump($tableName)
    {
        return $this->getResource()->getTableDataDump($tableName);
    }

    /**
     * Header for dumps.
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->getResource()->getHeader();
    }

    /**
     * Footer for dumps.
     *
     * @return string
     */
    public function getFooter()
    {
        return $this->getResource()->getFooter();
    }

    /**
     * Get backup SQL.
     *
     * @return string
     */
    public function renderSql()
    {
        ini_set('max_execution_time', 0);
        $sql = $this->getHeader();

        $tables = $this->getTables();
        foreach ($tables as $tableName) {
            $sql .= $this->getTableCreateScript($tableName, true);
            $sql .= $this->getTableDataDump($tableName);
        }

        $sql .= $this->getFooter();
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function createBackup(\Magento\Framework\Backup\Db\BackupInterface $backup)
    {
        if (!$this->helper->isEnabled()) {
            throw new RuntimeException(__('Backup functionality is disabled'));
        }

        $backup->open(true);

        $this->getResource()->beginTransaction();

        $tables = $this->getResource()->getTables();

        $backup->write($this->getResource()->getHeader());

        $ignoreDataTablesList = $this->getIgnoreDataTablesList();

        foreach ($tables as $table) {
            $backup->write(
                $this->getResource()->getTableHeader($table) . $this->getResource()->getTableDropSql($table) . "\n"
            );
            $backup->write($this->getResource()->getTableCreateSql($table, false) . "\n");

            $tableStatus = $this->getResource()->getTableStatus($table);

            if ($tableStatus->getRows() && !in_array($table, $ignoreDataTablesList)) {
                $backup->write($this->getResource()->getTableDataBeforeSql($table));

                if ($tableStatus->getDataLength() > self::BUFFER_LENGTH) {
                    if ($tableStatus->getAvgRowLength() < self::BUFFER_LENGTH) {
                        $limit = floor(self::BUFFER_LENGTH / max($tableStatus->getAvgRowLength(), 1));
                        $multiRowsLength = ceil($tableStatus->getRows() / $limit);
                    } else {
                        $limit = 1;
                        $multiRowsLength = $tableStatus->getRows();
                    }
                } else {
                    $limit = $tableStatus->getRows();
                    $multiRowsLength = 1;
                }

                for ($i = 0; $i < $multiRowsLength; $i++) {
                    $backup->write($this->getResource()->getTableDataSql($table, $limit, $i * $limit));
                }

                $backup->write($this->getResource()->getTableDataAfterSql($table));
            }
        }
        $backup->write($this->getResource()->getTableForeignKeysSql());
        $backup->write($this->getResource()->getTableTriggersSql());
        $backup->write($this->getResource()->getFooter());

        $this->getResource()->commitTransaction();

        $backup->close();
    }

    /**
     * Get database backup size
     *
     * @return int
     */
    public function getDBBackupSize()
    {
        $tables = $this->getResource()->getTables();
        $ignoreDataTablesList = $this->getIgnoreDataTablesList();
        $size = 0;
        foreach ($tables as $table) {
            $tableStatus = $this->getResource()->getTableStatus($table);
            if ($tableStatus->getRows() && !in_array($table, $ignoreDataTablesList)) {
                $size += $tableStatus->getDataLength() + $tableStatus->getIndexLength();
            }
        }
        return $size;
    }

    /**
     * Returns the list of tables which data should not be backed up
     *
     * @return string[]
     */
    public function getIgnoreDataTablesList()
    {
        $result = [];

        foreach ($this->_ignoreDataTablesList as $table) {
            $result[] = $this->_resource->getTableName($table);
        }

        return $result;
    }
}
