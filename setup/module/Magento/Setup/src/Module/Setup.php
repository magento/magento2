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
namespace Magento\Setup\Module;

use Magento\Setup\Module\Setup\Connection\AdapterInterface;
use Magento\Setup\Module\Setup\FileResolver as SetupFileResolver;
use Magento\Setup\Module\Updater\SetupInterface;
use Magento\Setup\Model\LoggerInterface;

class Setup implements SetupInterface
{
    /**
     * Call afterApplyAllUpdates method flag
     *
     * @var boolean
     */
    protected $callAfterApplyAllUpdates = false;

    /**
     * Setup Connection
     *
     * @var \Magento\Setup\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection = null;

    /**
     * Tables cache array
     *
     * @var array
     */
    protected $tables = array();

    /**
     * Tables data cache array
     *
     * @var array
     */
    protected $setupCache = array();

    /**
     * Filesystem instance
     *
     * @var \Magento\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Setup File Resolver
     *
     * @var SetupFileResolver
     */
    protected $setupFileResolver;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Table Prefix
     *
     * @var string
     */
    protected $tablePrefix;

    /**
     * Constructor
     *
     * @param AdapterInterface $connection
     * @param SetupFileResolver $setupFileResolver
     * @param LoggerInterface $logger
     * @param array $connectionConfig
     */
    public function __construct(
        AdapterInterface $connection,
        SetupFileResolver $setupFileResolver,
        LoggerInterface $logger,
        array $connectionConfig = array()
    ) {
        $this->logger = $logger;
        $this->connection = $connection->getConnection($connectionConfig);
        $this->setupFileResolver = $setupFileResolver;
    }

    /**
     * Get connection object
     *
     * @return \Magento\Setup\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Add table placeholder/table name relation
     *
     * @param string $tableName
     * @param string $realTableName
     * @return $this
     */
    public function setTable($tableName, $realTableName)
    {
        $this->tables[$tableName] = $realTableName;
        return $this;
    }

    /**
     * Get table name (validated by db adapter) by table placeholder
     *
     * @param string|array $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        $tablePrefix = (string)$this->tablePrefix;
        if ($tablePrefix && strpos($tableName, $tablePrefix) !== 0) {
            $tableName = $tablePrefix . $tableName;
        }

        $cacheKey = $this->getTableCacheName($tableName);
        if (!isset($this->tables[$cacheKey])) {
            $this->tables[$cacheKey] = $this->connection->getTableName($tableName);
        }
        return $this->tables[$cacheKey];
    }

    /**
     * Retrieve table name for cache
     *
     * @param string|array $tableName
     * @return string
     */
    protected function getTableCacheName($tableName)
    {
        if (is_array($tableName)) {
            return join('_', $tableName);
        }
        return $tableName;
    }

    /**
     * Include file by path
     * This method should perform only file inclusion.
     * Implemented to prevent possibility of changing important and used variables
     * inside the setup model while installing
     *
     * @param string $fileName
     * @return mixed
     */
    protected function includeFile($fileName)
    {
        $this->logger->log("Include {$fileName}");
        return include $fileName;
    }

    /**
     * Apply data updates to the system after upgrading.
     *
     * @return $this
     */
    public function applyDataUpdates()
    {
        return $this;
    }

    /******************* UTILITY METHODS *****************/

    /**
     * Retrieve row or field from table by id or string and parent id
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string|null $field
     * @param string|null $parentField
     * @param string|integer $parentId
     * @return mixed
     */
    public function getTableRow($table, $idField, $rowId, $field = null, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        if (empty($this->setupCache[$table][$parentId][$rowId])) {
            $adapter = $this->getConnection();
            $bind = array('id_field' => $rowId);
            $select = $adapter->select()->from($table)->where($adapter->quoteIdentifier($idField) . '= :id_field');
            if (null !== $parentField) {
                $select->where($adapter->quoteIdentifier($parentField) . '= :parent_id');
                $bind['parent_id'] = $parentId;
            }
            $this->setupCache[$table][$parentId][$rowId] = $adapter->query($select, $bind);
        }

        if (null === $field) {
            return $this->setupCache[$table][$parentId][$rowId];
        }
        return isset(
        $this->setupCache[$table][$parentId][$rowId][$field]
        ) ? $this->setupCache[$table][$parentId][$rowId][$field] : false;
    }

    /**
     * Delete table row
     *
     * @param string $table
     * @param string $idField
     * @param string|int $rowId
     * @param null|string $parentField
     * @param int|string $parentId
     * @return $this
     */
    public function deleteTableRow($table, $idField, $rowId, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        $adapter = $this->getConnection();
        $where = array($adapter->quoteIdentifier($idField) . '=?' => $rowId);
        if (!is_null($parentField)) {
            $where[$adapter->quoteIdentifier($parentField) . '=?'] = $parentId;
        }

        $adapter->delete($table, $where);

        if (isset($this->setupCache[$table][$parentId][$rowId])) {
            unset($this->setupCache[$table][$parentId][$rowId]);
        }

        return $this;
    }

    /**
     * Update one or more fields of table row
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string|array $field
     * @param mixed|null $value
     * @param string $parentField
     * @param string|integer $parentId
     * @return $this
     */
    public function updateTableRow($table, $idField, $rowId, $field, $value = null, $parentField = null, $parentId = 0)
    {
        $table = $this->getTable($table);
        if (is_array($field)) {
            $data = $field;
        } else {
            $data = array($field => $value);
        }

        $adapter = $this->getConnection();
        $where = array($adapter->quoteIdentifier($idField) . '=?' => $rowId);
        $adapter->update($table, $data, $where);

        if (isset($this->setupCache[$table][$parentId][$rowId])) {
            if (is_array($field)) {
                $this->setupCache[$table][$parentId][$rowId] = array_merge(
                    $this->setupCache[$table][$parentId][$rowId],
                    $field
                );
            } else {
                $this->setupCache[$table][$parentId][$rowId][$field] = $value;
            }
        }

        return $this;
    }

    /**
     * Check is table exists
     *
     * @param string $table
     * @return bool
     */
    public function tableExists($table)
    {
        $table = $this->getTable($table);
        return $this->getConnection()->isTableExists($table);
    }

    /**
     * Prepare database before install/upgrade
     *
     * @return $this
     */
    public function startSetup()
    {
        $this->getConnection()->startSetup();
        return $this;
    }

    /**
     * Prepare database after install/upgrade
     *
     * @return $this
     */
    public function endSetup()
    {
        $this->getConnection()->endSetup();
        return $this;
    }

    /**
     * Retrieve 32bit UNIQUE HASH for a Table index
     *
     * @param string $tableName
     * @param array|string $fields
     * @param string $indexType
     * @return string
     */
    public function getIdxName($tableName, $fields, $indexType = '')
    {
        return $this->connection->getIndexName($tableName, $fields, $indexType);
    }

    /**
     * Retrieve 32bit UNIQUE HASH for a Table foreign key
     *
     * @param string $priTableName  the target table name
     * @param string $priColumnName the target table column name
     * @param string $refTableName  the reference table name
     * @param string $refColumnName the reference table column name
     * @return string
     */
    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        return $this->connection->getForeignKeyName($priTableName, $priColumnName, $refTableName, $refColumnName);
    }

    /**
     * Check call afterApplyAllUpdates method for setup class
     *
     * @return bool
     */
    public function getCallAfterApplyAllUpdates()
    {
        return $this->callAfterApplyAllUpdates;
    }

    /**
     * Run each time after applying of all updates,
     * if setup model's $_callAfterApplyAllUpdates flag is set to true
     *
     * @return $this
     */
    public function afterApplyAllUpdates()
    {
        return $this;
    }

    /**
     * Add configuration data to core_config_data table
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addConfigData($key, $value)
    {
        $this->getConnection()->insert(
            $this->getTable('core_config_data'),
            array(
                'path'  => $key,
                'value' => $value
            ),
            true
        );
    }

    /**
     * Set table prefix
     *
     * @param string $tablePrefix
     * @return void
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }
}
