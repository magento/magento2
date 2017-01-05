<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Amqp connection type resolver.
 */
class ConnectionTypeResolver implements ConnectionTypeResolverInterface
{
    /**
     * Amqp connection names.
     *
     * @var string[]
     */
    private $amqpConnectionName = [];

    /**
     * Initialize dependencies.
     *
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $queueConfig = $deploymentConfig->getConfigData(Config::QUEUE_CONFIG);
        if (isset($queueConfig['connections']) && is_array($queueConfig['connections'])) {
            $this->amqpConnectionName = array_keys($queueConfig['connections']);
        }
        if (isset($queueConfig[Config::AMQP_CONFIG])) {
            $this->amqpConnectionName[] = Config::AMQP_CONFIG;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionType($connectionName)
    {
        return in_array($connectionName, $this->amqpConnectionName) ? 'amqp' : null;
    }
}
