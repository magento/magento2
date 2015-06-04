<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Model;

/**
 * Database backup model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * @var \Magento\Backup\Model\Resource\Db
     */
    protected $_resourceDb = null;

    /**
     * Core resource model
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource = null;

    /**
     * @param \Magento\Backup\Model\Resource\Db $resourceDb
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        \Magento\Backup\Model\Resource\Db $resourceDb,
        \Magento\Framework\App\Resource $resource
    ) {
        $this->_resourceDb = $resourceDb;
        $this->_resource = $resource;
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
     * @return \Magento\Backup\Model\Resource\Db
     */
    public function getResource()
    {
        return $this->_resourceDb;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->getResource()->getTables();
    }

    /**
     * @param string $tableName
     * @param bool $addDropIfExists
     * @return string
     */
    public function getTableCreateScript($tableName, $addDropIfExists = false)
    {
        return $this->getResource()->getTableCreateScript($tableName, $addDropIfExists);
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getTableDataDump($tableName)
    {
        return $this->getResource()->getTableDataDump($tableName);
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->getResource()->getHeader();
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        return $this->getResource()->getFooter();
    }

    /**
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
     * Create backup and stream write to adapter
     *
     * @param \Magento\Framework\Backup\Db\BackupInterface $backup
     * @return $this
     */
    public function createBackup(\Magento\Framework\Backup\Db\BackupInterface $backup)
    {
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
                        $limit = floor(self::BUFFER_LENGTH / $tableStatus->getAvgRowLength());
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
        $backup->write($this->getResource()->getFooter());

        $this->getResource()->commitTransaction();

        $backup->close();

        return $this;
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
