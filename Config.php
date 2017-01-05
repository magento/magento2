<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\App\DeploymentConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Reads the Amqp config in the deployed environment configuration
 */
class Config
{
    /**
     * Queue config key
     */
    const QUEUE_CONFIG = 'queue';

    /**
     * Amqp config key
     */
    const AMQP_CONFIG = 'amqp';

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
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * Associative array of Amqp configuration
     *
     * @var array
     */
    private $data;

    /**
     * AMQP connection name.
     *
     * @var string
     */
    private $connectionName;

    /**
     * Initialize dependencies.
     *
     * @param DeploymentConfig $config
     * @param string $connectionName
     */
    public function __construct(DeploymentConfig $config, $connectionName = 'amqp')
    {
        $this->deploymentConfig = $config;
        $this->connectionName = $connectionName;
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Returns the configuration set for the key.
     *
     * @param string $key
     * @return string
     * @throws \LogicException
     */
    public function getValue($key)
    {
        $this->load();
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Return Amqp channel
     *
     * @return AMQPChannel
     * @throws \LogicException
     */
    public function getChannel()
    {
        if (!isset($this->connection) || !isset($this->channel)) {
            $this->connection = new AMQPStreamConnection(
                $this->getValue(Config::HOST),
                $this->getValue(Config::PORT),
                $this->getValue(Config::USERNAME),
                $this->getValue(Config::PASSWORD),
                $this->getValue(Config::VIRTUALHOST)
            );
            $this->channel = $this->connection->channel();
        }
        return $this->channel;
    }

    /**
     * Load the configuration for Amqp
     *
     * @return void
     * @throws \LogicException
     */
    private function load()
    {
        if (null === $this->data) {
            $queueConfig = $this->deploymentConfig->getConfigData(self::QUEUE_CONFIG);
            if ($this->connectionName == self::AMQP_CONFIG) {
                $this->data = isset($queueConfig[self::AMQP_CONFIG]) ? $queueConfig[self::AMQP_CONFIG] : [];
            } else {
                $this->data = isset($queueConfig['connections'][$this->connectionName])
                    ? $queueConfig['connections'][$this->connectionName]
                    : [];
            }
            if (empty($this->data)) {
                throw  new \LogicException('Unknown connection name ' . $this->connectionName);
            }
        }
    }

    /**
     * Close Amqp connection and Channel
     *
     * @return void
     */
    private function closeConnection()
    {
        if (isset($this->channel)) {
            $this->channel->close();
            unset($this->channel);
        }

        if (isset($this->connection)) {
            $this->connection->close();
            unset($this->connection);
        }
    }
}
