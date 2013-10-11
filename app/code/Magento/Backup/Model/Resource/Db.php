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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Database backup resource model
 */
namespace Magento\Backup\Model\Resource;

class Db
{
    /**
     * Database connection adapter
     *
     * @var \Magento\DB\Adapter\Pdo\Mysql
     */
    protected $_write;

    /**
     * tables Foreign key data array
     * [tbl_name] = array(create foreign key strings)
     *
     * @var array
     */
    protected $_foreignKeys    = array();

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
     * @param \Magento\Core\Model\Resource $resource
     */
    public function __construct(
        \Magento\Backup\Model\Resource\HelperFactory $resHelperFactory,
        \Magento\Core\Model\Resource $resource
    ) {
        $this->_resourceHelper = $resHelperFactory->create();
        $this->_write = $resource->getConnection('backup_write');
    }

    /**
     * Clear data
     *
     */
    public function clear()
    {
        $this->_foreignKeys = array();
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
            foreach($tables as $table) {
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
     * @return \Magento\Object
     */
    public function getTableStatus($tableName)
    {
        $row = $this->_write->showTableStatus($tableName);

        if ($row) {
            $statusObject = new \Magento\Object();
            $statusObject->setIdFieldName('name');
            foreach ($row as $field => $value) {
                $statusObject->setData(strtolower($field), $value);
            }

            $cntRow = $this->_write->fetchRow(
                    $this->_write->select()->from($tableName, 'COUNT(1) as rows'));
            $statusObject->setRows($cntRow['rows']);

            return $statusObject;
        }

        return false;
    }

    /**
     * Retrive table partical data SQL insert
     *
     * @param string $tableName
     * @param int $count
     * @param int $offset
     * @return string
     */
    public function getTableDataSql($tableName, $count = null, $offset = null)
    {
        return $this->_resourceHelper->getPartInsertSql($tableName, $count, $offset);
    }

    /**
     * Enter description here...
     *
     * @param string|array|Zend_Db_Expr $tableName
     * @param bool $addDropIfExists
     * @return string
     */
    public function getTableCreateScript($tableName, $addDropIfExists = false)
    {
        return $this->_resourceHelper->getTableCreateScript($tableName, $addDropIfExists);;
    }

    /**
     * Retrieve table header comment
     *
     * @param unknown_type $tableName
     * @return string
     */
    public function getTableHeader($tableName)
    {
        $quotedTableName = $this->_write->quoteIdentifier($tableName);
        return "\n--\n"
            . "-- Table structure for table {$quotedTableName}\n"
            . "--\n\n";
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
     * @return \Magento\Backup\Model\Resource\Db
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
     * @return \Magento\Backup\Model\Resource\Db
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
     * @return \Magento\Backup\Model\Resource\Db
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
     * @param $command
     * @return \Magento\Backup\Model\Resource\Db
     */
    public function runCommand($command){
        $this->_write->query($command);
        return $this;
    }
}
