<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\TableNotFoundException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Config source. Retrieve all configuration for scopes from db
 */
class RuntimeConfigSource implements ConfigSourceInterface, ResetAfterRequestInterface
{
    /**
     * Deployment settings used to determine database availability.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Database resources for scope tables.
     *
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Connection used by the current runtime read.
     *
     * @var AdapterInterface
     */
    private $connection;

    /**
     * Whether the last read contained entities from every scope table.
     *
     * @var bool
     */
    private bool $hasRuntimeData = false;

    /**
     * Initialize the deployment settings and database resources.
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Return whole scopes config data from db.
     *
     * Ignore $path argument due to config source must return all config data
     *
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($path = '')
    {
        $data = [];
        try {
            if ($this->isDbAvailable()) {
                $data = [
                    'websites' => $this->getEntities('store_website', 'code'),
                    'groups' => $this->getEntities('store_group', 'group_id'),
                    'stores' => $this->getEntities('store', 'code'),
                ];
            }
        } catch (TableNotFoundException $exception) {
            // database is empty or not setup
            $data = [];
        }

        $this->hasRuntimeData = !empty($data['websites']) && !empty($data['groups']) && !empty($data['stores']);
        return $data;
    }

    /**
     * Whether the last read returned entities from all three scope tables.
     *
     * @return bool
     */
    public function hasRuntimeData(): bool
    {
        return $this->hasRuntimeData;
    }

    /**
     * Release the connection and runtime-data status between requests.
     *
     * @return void
     */
    public function _resetState(): void
    {
        $this->hasRuntimeData = false;
        $this->connection = null;
    }

    /**
     * Retrieve default connection
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }

    /**
     * Get entities from specified table in format [entityKeyField => [entity data], ...]
     *
     * @param string $table
     * @param string $keyField
     * @return array
     */
    private function getEntities($table, $keyField)
    {
        $data = [];
        $tableName = $this->resourceConnection->getTableName($table);
        // Check if db table exists before fetch data

        $entities = $this->getConnection()->fetchAll(
            $this->getConnection()->select()->from($tableName)
        );

        foreach ($entities as $entity) {
            $data[$entity[$keyField]] = $entity;
        }

        return $data;
    }

    /**
     * Whether deployment configuration permits runtime database reads.
     *
     * @return bool
     */
    public function isDbAvailable(): bool
    {
        return $this->deploymentConfig->isDbAvailable();
    }
}
