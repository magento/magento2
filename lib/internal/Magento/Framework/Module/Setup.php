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
use Magento\Framework\App\ResourceConnection;

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
     * @param string|null $connectionName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection($connectionName = null)
    {
        if ($connectionName !== null) {
            try {
                return $this->resourceModel->getConnectionByName($connectionName);
            } catch (\DomainException $exception) {
                //Fallback to default connection
            }
        }
        return $this->getDefaultConnection();
    }

    /**
     * Returns default setup connection instance
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getDefaultConnection()
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
     * Gets table placeholder by table name
     *
     * @param string $tableName
     * @return string
     */
    public function getTablePlaceholder($tableName)
    {
        return $this->resourceModel->getTablePlaceholder($tableName);
    }

    /**
     * Get table name (validated by db adapter) by table placeholder
     *
     * @param string|array $tableName
     * @param string $connectionName
     * @return string
     */
    public function getTable($tableName, $connectionName = ResourceConnection::DEFAULT_CONNECTION)
    {
        $cacheKey = $this->_getTableCacheName($tableName);
        if (!isset($this->tables[$cacheKey])) {
            $this->tables[$cacheKey] = $this->resourceModel->getTableName($tableName, $connectionName);
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
     * @param string $connectionName
     * @return bool
     */
    public function tableExists($table, $connectionName = ResourceConnection::DEFAULT_CONNECTION)
    {
        $table = $this->getTable($table, $connectionName);
        return $this->getConnection($connectionName)->isTableExists($table);
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
