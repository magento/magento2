<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backup\Model\ResourceModel;

class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Tables foreign key data array
     * [tbl_name] = array(create foreign key strings)
     *
     * @var array
     */
    protected $_foreignKeys = [];

    /**
     * Core Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        $modulePrefix,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
    ) {
        parent::__construct($resource, $modulePrefix);
        $this->_coreDate = $coreDate;
    }

    /**
     * Retrieve SQL fragment for drop table
     *
     * @param string $tableName
     * @return string
     */
    public function getTableDropSql($tableName)
    {
        $quotedTableName = $this->getConnection()->quoteIdentifier($tableName);
        return sprintf('DROP TABLE IF EXISTS %s;', $quotedTableName);
    }

    /**
     * Retrieve foreign keys for table(s)
     *
     * @param string|null $tableName
     * @return string|bool
     */
    public function getTableForeignKeysSql($tableName = null)
    {
        $sql = false;

        if ($tableName === null) {
            $sql = '';
            foreach ($this->_foreignKeys as $table => $foreignKeys) {
                $sql .= $this->_buildForeignKeysAlterTableSql($table, $foreignKeys);
            }
        } elseif (isset($this->_foreignKeys[$tableName])) {
            $foreignKeys = $this->_foreignKeys[$tableName];
            $sql = $this->_buildForeignKeysAlterTableSql($tableName, $foreignKeys);
        }

        return $sql;
    }

    /**
     * Build sql that will add foreign keys to it
     *
     * @param string $tableName
     * @param array $foreignKeys
     * @return string
     */
    protected function _buildForeignKeysAlterTableSql($tableName, $foreignKeys)
    {
        if (!is_array($foreignKeys) || empty($foreignKeys)) {
            return '';
        }

        return sprintf(
            "ALTER TABLE %s\n  %s;\n",
            $this->getConnection()->quoteIdentifier($tableName),
            join(",\n  ", $foreignKeys)
        );
    }

    /**
     * Get create script for table
     *
     * @param string $tableName
     * @param boolean $addDropIfExists
     * @return string
     */
    public function getTableCreateScript($tableName, $addDropIfExists = false)
    {
        $script = '';
        $quotedTableName = $this->getConnection()->quoteIdentifier($tableName);

        if ($addDropIfExists) {
            $script .= 'DROP TABLE IF EXISTS ' . $quotedTableName . ";\n";
        }
        //TODO fix me
        $sql = 'SHOW CREATE TABLE ' . $quotedTableName;
        $data = $this->getConnection()->fetchRow($sql);
        $script .= isset($data['Create Table']) ? $data['Create Table'] . ";\n" : '';

        return $script;
    }

    /**
     * Retrieve SQL fragment for create table
     *
     * @param string $tableName
     * @param bool $withForeignKeys
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getTableCreateSql($tableName, $withForeignKeys = false)
    {
        $connection = $this->getConnection();
        $quotedTableName = $connection->quoteIdentifier($tableName);
        $query = 'SHOW CREATE TABLE ' . $quotedTableName;
        $row = $connection->fetchRow($query);

        if (!$row || !isset($row['Table']) || !isset($row['Create Table'])) {
            return false;
        }

        $regExp = '/,\s+CONSTRAINT `([^`]*)` FOREIGN KEY \(`([^`]*)`\) ' .
            'REFERENCES `([^`]*)` \(`([^`]*)`\)' .
            '( ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?' .
            '( ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?/';
        $matches = [];
        preg_match_all($regExp, $row['Create Table'], $matches, PREG_SET_ORDER);

        if (is_array($matches)) {
            foreach ($matches as $match) {
                $this->_foreignKeys[$tableName][] = sprintf(
                    'ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)%s%s',
                    $connection->quoteIdentifier($match[1]),
                    $connection->quoteIdentifier($match[2]),
                    $connection->quoteIdentifier($match[3]),
                    $connection->quoteIdentifier($match[4]),
                    isset($match[5]) ? $match[5] : '',
                    isset($match[7]) ? $match[7] : ''
                );
            }
        }

        if ($withForeignKeys) {
            $sql = $row['Create Table'];
        } else {
            $sql = preg_replace($regExp, '', $row['Create Table']);
        }

        return $sql . ';';
    }

    /**
     * Returns SQL header data, move from original resource model
     *
     * @return string
     */
    public function getHeader()
    {
        $dbConfig = $this->getConnection()->getConfig();

        $versionRow = $this->getConnection()->fetchRow('SHOW VARIABLES LIKE \'version\'');
        $hostName = !empty($dbConfig['unix_socket']) ? $dbConfig['unix_socket'] : (!empty($dbConfig['host']) ? $dbConfig['host'] : 'localhost');

        $header = "-- Magento DB backup\n" .
            "--\n" .
            "-- Host: {$hostName}    Database: {$dbConfig['dbname']}\n" .
            "-- ------------------------------------------------------\n" .
            "-- Server version: {$versionRow['Value']}\n\n" .
            "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n" .
            "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n" .
            "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n" .
            "/*!40101 SET NAMES utf8 */;\n" .
            "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n" .
            "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n" .
            "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n" .
            "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n";

        return $header;
    }

    /**
     * Returns SQL footer data, move from original resource model
     *
     * @return string
     */
    public function getFooter()
    {
        $footer = "\n/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n" .
            "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */; \n" .
            "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n" .
            "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n" .
            "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n" .
            "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n" .
            "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n" .
            "\n-- Dump completed on " .
            $this->_coreDate->gmtDate() .
            " GMT";

        return $footer;
    }

    /**
     * Retrieve before insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function getTableDataBeforeSql($tableName)
    {
        $quotedTableName = $this->getConnection()->quoteIdentifier($tableName);
        return "\n--\n" .
            "-- Dumping data for table {$quotedTableName}\n" .
            "--\n\n" .
            "LOCK TABLES {$quotedTableName} WRITE;\n" .
            "/*!40000 ALTER TABLE {$quotedTableName} DISABLE KEYS */;\n";
    }

    /**
     * Retrieve after insert data SQL fragment
     *
     * @param string $tableName
     * @return string
     */
    public function getTableDataAfterSql($tableName)
    {
        $quotedTableName = $this->getConnection()->quoteIdentifier($tableName);
        return "/*!40000 ALTER TABLE {$quotedTableName} ENABLE KEYS */;\n" . "UNLOCK TABLES;\n";
    }

    /**
     * Return table part data SQL insert
     *
     * @param string $tableName
     * @param int $count
     * @param int $offset
     * @return string
     */
    public function getPartInsertSql($tableName, $count = null, $offset = null)
    {
        $sql = null;
        $connection = $this->getConnection();
        $select = $connection->select()->from($tableName)->limit($count, $offset);
        $query = $connection->query($select);

        while (true == ($row = $query->fetch())) {
            if ($sql === null) {
                $sql = sprintf('INSERT INTO %s VALUES ', $connection->quoteIdentifier($tableName));
            } else {
                $sql .= ',';
            }

            $sql .= $this->_quoteRow($tableName, $row);
        }

        if ($sql !== null) {
            $sql .= ';' . "\n";
        }

        return $sql;
    }

    /**
     * Return table data SQL insert
     *
     * @param string $tableName
     * @return string
     */
    public function getInsertSql($tableName)
    {
        return $this->getPartInsertSql($tableName);
    }

    /**
     * Quote Table Row
     *
     * @param string $tableName
     * @param array $row
     * @return string
     */
    protected function _quoteRow($tableName, array $row)
    {
        $connection = $this->getConnection();
        $describe = $connection->describeTable($tableName);
        $dataTypes = ['bigint', 'mediumint', 'smallint', 'tinyint'];
        $rowData = [];
        foreach ($row as $key => $data) {
            if ($data === null) {
                $value = 'NULL';
            } elseif (in_array(strtolower($describe[$key]['DATA_TYPE']), $dataTypes)) {
                $value = $data;
            } else {
                $value = $connection->quoteInto('?', $data);
            }
            $rowData[] = $value;
        }

        return sprintf('(%s)', implode(',', $rowData));
    }

    /**
     * Prepare transaction isolation level for backup process
     *
     * @return void
     */
    public function prepareTransactionIsolationLevel()
    {
        $this->getConnection()->query('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
    }

    /**
     * Restore transaction isolation level after backup
     *
     * @return void
     */
    public function restoreTransactionIsolationLevel()
    {
        $this->getConnection()->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    }
}
