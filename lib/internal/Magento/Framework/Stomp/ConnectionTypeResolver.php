<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;

/**
 * Stomp connection type resolver.
 *
 * @api
 */
class ConnectionTypeResolver implements ConnectionTypeResolverInterface
{
    /**
     * Stomp connection names.
     *
     * @var string[]
     */
    private array $stompConnectionName = [];

    /**
     * Initialize dependencies.
     *
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $queueConfig = $deploymentConfig->getConfigData(Config::QUEUE_CONFIG);
        if (isset($queueConfig['connections']) && is_array($queueConfig['connections'])) {
            $this->stompConnectionName = array_keys($queueConfig['connections']);
        }
        if (isset($queueConfig[Config::STOMP_CONFIG])) {
            $this->stompConnectionName[] = Config::STOMP_CONFIG;
        }
    }

    /**
     * @inheritdoc
     */
    public function getConnectionType($connectionName): ?string
    {
        return in_array($connectionName, $this->stompConnectionName, true) ? 'stomp' : null;
    }
}
