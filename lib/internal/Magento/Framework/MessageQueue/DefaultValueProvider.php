<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;

/**
 * Message Queue default config value provider.
 */
class DefaultValueProvider
{
    /**
     * Default connection name.
     *
     * @var string
     */
    private $connection;

    /**
     * Default exchange name.
     *
     * @var string
     */
    private $exchange;

    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * Initialize dependencies.
     *
     * @param string $connection
     * @param string $exchange
     * @param DeploymentConfig|null $config
     */
    public function __construct(
        $connection = 'db',
        $exchange = 'magento',
        DeploymentConfig $config = null
    ) {
        $this->connection = $connection;
        $this->exchange = $exchange;
        $this->config = $config ?? ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * Get default connection name.
     *
     * @return string
     */
    public function getConnection()
    {
        // get amqp or default_connection if it is set in deployment configuration
        // otherwise use db as a default connection
        if (isset($this->config)) {
            if ($this->config->get('queue/default_connection')) {
                $this->connection = $this->config->get('queue/default_connection');
            } elseif ($this->config->get('queue/amqp') && count($this->config->get('queue/amqp')) > 0) {
                $this->connection = 'amqp';
            }
        }
        return $this->connection;
    }

    /**
     * Get default exchange name.
     *
     * @return string
     */
    public function getExchange()
    {
        return $this->exchange;
    }
}
