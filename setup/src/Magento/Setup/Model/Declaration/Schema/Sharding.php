<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Sharding gives possibility to see what structural element should be installed on what shard
 * You can find what is shard in any SQL documentation
 */
class Sharding
{
    /**
     * Name of default connection
     */
    const DEFAULT_CONNECTION = 'default';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Under resources means connection names
     * Each connection name represents each shard
     *
     * @var array
     */
    private $resources;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param array $resources
     */
    public function __construct(DeploymentConfig $deploymentConfig, array $resources)
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->resources = $resources;
    }

    /**
     * Depends on different settings we should have different qty of connection names
     *
     * @return array
     */
    public function getResources()
    {
        $resources = [];

        foreach ($this->resources as $resource) {
            if ($this->canUseResource($resource)) {
                $resources[] = $resource;
            }
        }

        return $resources;
    }

    /**
     * Check whether our resource is valid one
     *
     * @param string $scopeName
     * @return bool
     */
    public function canUseResource($scopeName)
    {
        $connections = $this->deploymentConfig
            ->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS);
        return isset($connections[$scopeName]);
    }

    /**
     * Retrieve default resource name, that is used by the system
     *
     * @return string
     */
    public function getDefaultResource()
    {
        return self::DEFAULT_CONNECTION;
    }
}
