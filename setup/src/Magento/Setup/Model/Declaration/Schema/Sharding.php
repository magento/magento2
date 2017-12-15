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
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Check whether our resource is valid one
     *
     * @param string $scopeName
     * @return bool
     */
    public function canUseResource($scopeName)
    {
        $connections = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS);
        return isset($connections[$scopeName]);
    }

    /**
     * Retrieve default resource name, that is used by the system
     *
     * @return string
     */
    public function getDefaultResource()
    {
        return 'default';
    }
}
