<?php
/**
 * Resources and connections registry and factory
 *
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
namespace Magento\App;

class Resource
{
    const AUTO_UPDATE_ONCE       = 0;
    const AUTO_UPDATE_NEVER      = -1;
    const AUTO_UPDATE_ALWAYS     = 1;

    const PARAM_TABLE_PREFIX = 'db.table_prefix';
    const DEFAULT_READ_RESOURCE  = 'core_read';

    /**
     * Instances of actual connections
     *
     * @var \Magento\DB\Adapter\Interface[]
     */
    protected $_connections = array();

    /**
     * Mapped tables cache array
     *
     * @var array
     */
    protected $_mappedTableNames;

    /**
     * Resource config
     *
     * @var \Magento\App\Resource\ConfigInterface
     */
    protected $_config;

    /**
     * Resource connection adapter factory
     *
     * @var \Magento\App\Resource\ConnectionFactory
     */
    protected $_connectionFactory;

    /**
     * Application cache
     *
     * @var \Magento\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var string
     */
    protected $_tablePrefix;

    /**
     * @param \Magento\App\CacheInterface $cache
     * @param \Magento\App\Resource\ConfigInterface $resourceConfig
     * @param \Magento\App\Resource\ConnectionFactory $adapterFactory
     * @param string $tablePrefix
     */
    public function __construct(
        \Magento\App\CacheInterface $cache,
        Resource\ConfigInterface $resourceConfig,
        Resource\ConnectionFactory $adapterFactory,
        $tablePrefix = ''
    ) {
        $this->_cache = $cache;
        $this->_config = $resourceConfig;
        $this->_connectionFactory = $adapterFactory;
        $this->_tablePrefix = $tablePrefix;
    }

    /**
     * Set cache instance
     *
     * @param \Magento\App\CacheInterface $cache
     * @return void
     */
    public function setCache(\Magento\App\CacheInterface $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Set table prefix
     * Added for console installation
     *
     * @param string $tablePrefix
     * @return void
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->_tablePrefix = $tablePrefix;
    }

    /**
     * Retrieve connection to resource specified by $resourceName
     *
     * @param string $resourceName
     * @return \Magento\DB\Adapter\AdapterInterface|bool
     */
    public function getConnection($resourceName)
    {
        $connectionName = $this->_config->getConnectionName($resourceName);
        if (isset($this->_connections[$connectionName])) {
            return $this->_connections[$connectionName];
        }

        $connection = $this->_connectionFactory->create($connectionName);
        if (!$connection) {
            return false;
        }
        $connection->setCacheAdapter($this->_cache->getFrontend());

        $this->_connections[$connectionName] = $connection;
        return $connection;
    }

    /**
     * Get resource table name, validated by db adapter
     *
     * @param   string|string[] $modelEntity
     * @return  string
     */
    public function getTableName($modelEntity)
    {
        $tableSuffix = null;
        if (is_array($modelEntity)) {
            list($modelEntity, $tableSuffix) = $modelEntity;
        }

        $tableName = $modelEntity;

        $mappedTableName = $this->getMappedTableName($tableName);
        if ($mappedTableName) {
            $tableName = $mappedTableName;
        } else {
            $tablePrefix = (string)$this->_tablePrefix;
            if ($tablePrefix && strpos($tableName, $tablePrefix) !== 0) {
                $tableName = $tablePrefix . $tableName;
            }
        }

        if ($tableSuffix) {
            $tableName .= '_' . $tableSuffix;
        }
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)->getTableName($tableName);
    }

    /**
     * Set mapped table name
     *
     * @param string $tableName
     * @param string $mappedName
     * @return $this
     */
    public function setMappedTableName($tableName, $mappedName)
    {
        $this->_mappedTableNames[$tableName] = $mappedName;
        return $this;
    }

    /**
     * Get mapped table name
     *
     * @param string $tableName
     * @return bool|string
     */
    public function getMappedTableName($tableName)
    {
        if (isset($this->_mappedTableNames[$tableName])) {
            return $this->_mappedTableNames[$tableName];
        } else {
            return false;
        }
    }

    /**
     * Retrieve 32bit UNIQUE HASH for a Table index
     *
     * @param string $tableName
     * @param string|string[] $fields
     * @param string $indexType
     * @return string
     */
    public function getIdxName($tableName, $fields, $indexType = \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX)
    {
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)
            ->getIndexName($this->getTableName($tableName), $fields, $indexType);
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
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)
            ->getForeignKeyName($this->getTableName($priTableName), $priColumnName,
                $this->getTableName($refTableName), $refColumnName);
    }
}
