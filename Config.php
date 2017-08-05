<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\App\DeploymentConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSSLConnection;

/**
 * Reads the Amqp config in the deployed environment configuration
 *
 * @api
 * @since 100.0.0
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
    const SSL_OPTIONS = 'ssl_options';

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
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'amqp' => [
     *             'host' => 'localhost',
     *             'port' => 5672,
     *             'username' => 'guest',
     *             'password' => 'guest',
     *             'virtual_host' => '/',
     *             'ssl' => false,
     *             'ssl_options' => [],
     *         ],
     *     ],
     * </code>
     *
     * @param DeploymentConfig $config
     * @param string $connectionName
     * @since 100.0.0
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
     * @since 100.0.0
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
     * @since 100.0.0
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
     * @since 100.0.0
     */
    public function getChannel()
    {
        if (!isset($this->connection) || !isset($this->channel)) {
            $this->connection = $this->getValue(self::SSL) ? $this->createSecureConnection() :
                $this->createUnsecureConnection();

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

    /**
     * @return AMQPStreamConnection
     */
    private function createUnsecureConnection()
    {
        return new AMQPStreamConnection(
            $this->getValue(self::HOST),
            $this->getValue(self::PORT),
            $this->getValue(self::USERNAME),
            $this->getValue(self::PASSWORD),
            $this->getValue(self::VIRTUALHOST)
        );
    }

    /**
     * Create secure connection to AMQP server.
     *
     * Note: when you are passing empty array of SSL options PHP-AMQPLIB will actually create unsecure connection.
     *
     * @return AMQPSSLConnection
     */
    private function createSecureConnection()
    {
        $sslOptions = $this->getValue(self::SSL_OPTIONS);

        if (empty($sslOptions)) {
            $sslOptions = ['verify_peer' => true];
        }

        return new AMQPSSLConnection(
            $this->getValue(self::HOST),
            $this->getValue(self::PORT),
            $this->getValue(self::USERNAME),
            $this->getValue(self::PASSWORD),
            $this->getValue(self::VIRTUALHOST),
            $sslOptions
        );
    }
}
