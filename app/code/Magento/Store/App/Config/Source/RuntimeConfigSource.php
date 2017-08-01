<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Config source. Retrieve all configuration for scopes from db
 * @since 2.2.0
 */
class RuntimeConfigSource implements ConfigSourceInterface
{
    /**
     * @var DeploymentConfig
     * @since 2.2.0
     */
    private $deploymentConfig;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     * @since 2.2.0
     */
    private $connection;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
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
     * Ignore $path argument due to config source must return all config data
     *
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function get($path = '')
    {
        if ($this->canUseDatabase()) {
            return [
                'websites' => $this->getEntities('store_website', 'code'),
                'groups' => $this->getEntities('store_group', 'group_id'),
                'stores' => $this->getEntities('store', 'code'),
            ];
        }

        return [];
    }

    /**
     * @return AdapterInterface
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function getEntities($table, $keyField)
    {
        $entities = $this->getConnection()->fetchAll(
            $this->getConnection()->select()->from($this->resourceConnection->getTableName($table))
        );
        $data = [];
        foreach ($entities as $entity) {
            $data[$entity[$keyField]] = $entity;
        }

        return $data;
    }

    /**
     * Check whether db connection is available and can be used
     *
     * @return bool
     * @since 2.2.0
     */
    private function canUseDatabase()
    {
        return $this->deploymentConfig->get('db');
    }
}
