<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection\ConfigInterface as ResourceConfigInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Application provides ability to configure multiple connections to persistent storage.
 * This class provides access to all these connections.
 * @api
 * @since 2.0.0
 */
class ResourceConnection
{
    const AUTO_UPDATE_ONCE = 0;

    const AUTO_UPDATE_NEVER = -1;

    const AUTO_UPDATE_ALWAYS = 1;

    const DEFAULT_CONNECTION = 'default';

    /**
     * Instances of actual connections
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface[]
     * @since 2.0.0
     */
    protected $connections = [];

    /**
     * Mapped tables cache array
     *
     * @var array
     * @since 2.0.0
     */
    protected $mappedTableNames;

    /**
     * Resource config
     *
     * @var ResourceConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * Resource connection adapter factory
     *
     * @var ConnectionFactoryInterface
     * @since 2.0.0
     */
    protected $connectionFactory;

    /**
     * @var DeploymentConfig $deploymentConfig
     * @since 2.0.0
     */
    private $deploymentConfig;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $tablePrefix;

    /**
     * @param ResourceConfigInterface $resourceConfig
     * @param ConnectionFactoryInterface $connectionFactory
     * @param DeploymentConfig $deploymentConfig
     * @param string $tablePrefix
     * @since 2.0.0
     */
    public function __construct(
        ResourceConfigInterface $resourceConfig,
        ConnectionFactoryInterface $connectionFactory,
        DeploymentConfig $deploymentConfig,
        $tablePrefix = ''
    ) {
        $this->config = $resourceConfig;
        $this->connectionFactory = $connectionFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->tablePrefix = $tablePrefix ?: null;
    }

    /**
     * Retrieve connection to resource specified by $resourceName
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getConnection($resourceName = self::DEFAULT_CONNECTION)
    {
        $connectionName = $this->config->getConnectionName($resourceName);
        return $this->getConnectionByName($connectionName);
    }

    /**
     * @param string $resourceName
     * @return void
     * @since 2.2.0
     */
    public function closeConnection($resourceName = self::DEFAULT_CONNECTION)
    {
        $processConnectionName = $this->getProcessConnectionName($this->config->getConnectionName($resourceName));
        if (isset($this->connections[$processConnectionName])) {
            $this->connections[$processConnectionName] = null;
        }
    }

    /**
     * Retrieve connection by $connectionName
     *
     * @param string $connectionName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     * @since 2.0.0
     */
    public function getConnectionByName($connectionName)
    {
        $processConnectionName = $this->getProcessConnectionName($connectionName);
        if (isset($this->connections[$processConnectionName])) {
            return $this->connections[$processConnectionName];
        }

        $connectionConfig = $this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/' . $connectionName
        );

        if ($connectionConfig) {
            $connection = $this->connectionFactory->create($connectionConfig);
        } else {
            throw new \DomainException('Connection "' . $connectionName . '" is not defined');
        }

        $this->connections[$processConnectionName] = $connection;
        return $connection;
    }

    /**
     * @param string $connectionName
     * @return string
     * @since 2.2.0
     */
    private function getProcessConnectionName($connectionName)
    {
        return  $connectionName . '_process_' . getmypid();
    }

    /**
     * Get resource table name, validated by db adapter
     *
     * @param   string|string[] $modelEntity
     * @param string $connectionName
     * @return  string
     * @api
     * @since 2.0.0
     */
    public function getTableName($modelEntity, $connectionName = self::DEFAULT_CONNECTION)
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
     * Gets table placeholder by table name
     *
     * @param string $tableName
     * @return string
     * @since 2.1.0
     */
    public function getTablePlaceholder($tableName)
    {
        $tableName = preg_replace('/^' . preg_quote($this->getTablePrefix()) . '_/', '', $tableName);
        return $tableName;
    }

    /**
     * Build a trigger name
     *
     * @param string $tableName  The table that is the subject of the trigger
     * @param string $time  Either "before" or "after"
     * @param string $event  The DB level event which activates the trigger, i.e. "update" or "insert"
     * @return string
     * @since 2.0.0
     */
    public function getTriggerName($tableName, $time, $event)
    {
        return $this->getConnection()->getTriggerName($tableName, $time, $event);
    }

    /**
     * Set mapped table name
     *
     * @param string $tableName
     * @param string $mappedName
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setMappedTableName($tableName, $mappedName)
    {
        $this->mappedTableNames[$tableName] = $mappedName;
        return $this;
    }

    /**
     * Get mapped table name
     *
     * @param string $tableName
     * @return bool|string
     * @since 2.0.0
     */
    public function getMappedTableName($tableName)
    {
        if (isset($this->mappedTableNames[$tableName])) {
            return $this->mappedTableNames[$tableName];
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
     * @since 2.0.0
     */
    public function getIdxName(
        $tableName,
        $fields,
        $indexType = \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
    ) {
        return $this->getConnection()
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
     * @since 2.0.0
     */
    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        return $this->getConnection()->getForeignKeyName(
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
     * @since 2.0.0
     */
    private function getTablePrefix()
    {
        if (null === $this->tablePrefix) {
            $this->tablePrefix = (string)$this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
            );
        }
        return $this->tablePrefix;
    }
}
