<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\App\DeploymentConfig;

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
     * @param DeploymentConfig $config
     * @param string $connection
     * @param string $exchange
     */
    public function __construct(DeploymentConfig $config, $connection = 'db', $exchange = 'magento')
    {
        $this->config = $config;
        $this->connection = $this->config->get('queue/default_connection', $connection);
        $this->exchange = $exchange;
    }

    /**
     * Get default connection name.
     *
     * @return string
     */
    public function getConnection()
    {
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
