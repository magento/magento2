<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Amqp connection type resolver.
 *
 * @api
 * @since 103.0.0
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
     * @since 103.0.0
     */
    public function getConnectionType($connectionName)
    {
        return in_array($connectionName, $this->amqpConnectionName) ? 'amqp' : null;
    }
}
