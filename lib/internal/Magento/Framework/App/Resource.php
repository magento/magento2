<?php
/**
 * Resources and connections registry and factory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Resource\ConfigInterface as ResourceConfigInterface;
use Magento\Framework\Model\Resource\Type\Db\ConnectionFactoryInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;

class Resource
{
    const AUTO_UPDATE_ONCE = 0;

    const AUTO_UPDATE_NEVER = -1;

    const AUTO_UPDATE_ALWAYS = 1;

    const DEFAULT_READ_RESOURCE = 'core_read';

    const DEFAULT_WRITE_RESOURCE = 'core_write';

    /**
     * Instances of actual connections
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface[]
     */
    protected $_connections = [];

    /**
     * Mapped tables cache array
     *
     * @var array
     */
    protected $_mappedTableNames;

    /**
     * Resource config
     *
     * @var ResourceConfigInterface
     */
    protected $_config;

    /**
     * Resource connection adapter factory
     *
     * @var ConnectionFactoryInterface
     */
    protected $_connectionFactory;

    /**
     * @var DeploymentConfig $deploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var string
     */
    protected $_tablePrefix;

    /**
     * @param ResourceConfigInterface $resourceConfig
     * @param ConnectionFactoryInterface $adapterFactory
     * @param DeploymentConfig $deploymentConfig
     * @param string $tablePrefix
     */
    public function __construct(
        ResourceConfigInterface $resourceConfig,
        ConnectionFactoryInterface $adapterFactory,
        DeploymentConfig $deploymentConfig,
        $tablePrefix = ''
    ) {
        $this->_config = $resourceConfig;
        $this->_connectionFactory = $adapterFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->_tablePrefix = $tablePrefix ?: null;
    }

    /**
     * Retrieve connection to resource specified by $resourceName
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     * @codeCoverageIgnore
     */
    public function getConnection($resourceName)
    {
        $connectionName = $this->_config->getConnectionName($resourceName);
        return $this->getConnectionByName($connectionName);
    }

    /**
     * Retrieve connection by $connectionName
     *
     * @param string $connectionName
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnectionByName($connectionName)
    {
        if (isset($this->_connections[$connectionName])) {
            return $this->_connections[$connectionName];
        }

        $connectionConfig = $this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/' . $connectionName
        );

        if ($connectionConfig) {
            $connection = $this->_connectionFactory->create($connectionConfig);
        }
        if (empty($connection)) {
            return false;
        }

        $this->_connections[$connectionName] = $connection;
        return $connection;
    }

    /**
     * Get resource table name, validated by db adapter
     *
     * @param   string|string[] $modelEntity
     * @param   string $connectionName
     * @return  string
     * @api
     */
    public function getTableName($modelEntity, $connectionName = self::DEFAULT_READ_RESOURCE)
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
            $tablePrefix = $this->getTablePrefix();
            if ($tablePrefix && strpos($tableName, $tablePrefix) !== 0) {
                $tableName = $tablePrefix . $tableName;
            }
        }

        if ($tableSuffix) {
            $tableName .= '_' . $tableSuffix;
        }
        return $this->getConnection($connectionName)->getTableName($tableName);
    }

    /**
     * Build a trigger name
     *
     * @param string $tableName  The table that is the subject of the trigger
     * @param string $time  Either "before" or "after"
     * @param string $event  The DB level event which activates the trigger, i.e. "update" or "insert"
     * @return string
     */
    public function getTriggerName($tableName, $time, $event)
    {
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)->getTriggerName($tableName, $time, $event);
    }

    /**
     * Set mapped table name
     *
     * @param string $tableName
     * @param string $mappedName
     * @return $this
     * @codeCoverageIgnore
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
    public function getIdxName(
        $tableName,
        $fields,
        $indexType = \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
    ) {
        return $this->getConnection(self::DEFAULT_READ_RESOURCE)
            ->getIndexName(
                $this->getTableName($tableName),
                $fields,
                $indexType
            );
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
        return $this->getConnection(
            self::DEFAULT_READ_RESOURCE
        )->getForeignKeyName(
            $this->getTableName($priTableName),
            $priColumnName,
            $this->getTableName($refTableName),
            $refColumnName
        );
    }

    /**
     * Get table prefix
     *
     * @return string
     */
    private function getTablePrefix()
    {
        if (null === $this->_tablePrefix) {
            $this->_tablePrefix = (string)$this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
            );
        }
        return $this->_tablePrefix;
    }
}
