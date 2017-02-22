<?php
/**
 * Base Resource Setup Model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SetupInterface;

class Setup implements SetupInterface
{
    /**
     * Setup Connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection = null;

    /**
     * Tables cache array
     *
     * @var array
     */
    private $tables = [];

    /**
     * Modules configuration
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceModel;

    /**
     * Connection instance name
     *
     * @var string
     */
    private $connectionName;

    /**
     * Init
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        $connectionName = ModuleDataSetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->resourceModel = $resource;
        $this->connectionName = $connectionName;
    }

    /**
     * Get connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceModel->getConnection($this->connectionName);
        }
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
        $cacheKey = $this->_getTableCacheName($tableName);
        if (!isset($this->tables[$cacheKey])) {
            $this->tables[$cacheKey] = $this->resourceModel->getTableName($tableName);
        }
        return $this->tables[$cacheKey];
    }

    /**
     * Retrieve table name for cache
     *
     * @param string|array $tableName
     * @return string
     */
    private function _getTableCacheName($tableName)
    {
        if (is_array($tableName)) {
            return join('_', $tableName);
        }
        return $tableName;
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
     * Run plain SQL query(ies)
     *
     * @param string $sql
     * @return $this
     */
    public function run($sql)
    {
        $this->getConnection()->query($sql);
        return $this;
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
}
