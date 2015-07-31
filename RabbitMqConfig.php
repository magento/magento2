<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp;

use Magento\Framework\App\DeploymentConfig;

/**
 * Reads the RabbitMQ config in the deployed environment configuration
 */
class RabbitMqConfig
{
    /**
     * Queue config key
     */
    const QUEUE_CONFIG = 'queue';

    /**
     * RabbitMQ config key
     */
    const RABBITMQ_CONFIG = 'rabbit';

    const HOST = 'host';
    const PORT = 'port';
    const USERNAME = 'user';
    const PASSWORD = 'password';
    const VIRTUALHOST = 'virtualhost';
    const SSL = 'ssl';

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Associative array of RabbitMQ configuration
     *
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'rabbit' => [
     *             'host' => 'localhost',
     *             'port' => 5672,
     *             'username' => 'guest',
     *             'password' => 'guest',
     *             'virtual_host' => '/',
     *             'ssl' => [],
     *         ],
     *     ],
     * </code>
     *
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->deploymentConfig = $config;
    }

    /**
     * Returns the configuration set for the key.
     *
     * @param string $key
     * @return string
     */
    public function getValue($key)
    {
        $this->load();
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Load the configuration for RabbitMQ
     *
     * @return void
     */
    private function load()
    {
        if (null === $this->data) {
            $queueConfig = $this->deploymentConfig->getConfigData(self::QUEUE_CONFIG);
            $this->data = isset($queueConfig[self::RABBITMQ_CONFIG]) ? $queueConfig[self::RABBITMQ_CONFIG] : [];
        }
    }
}
