<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Amqp\Connection\FactoryOptions;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Reads the Amqp config in the deployed environment configuration
 *
 * @api
 * @since 103.0.0
 */
class Config implements ResetAfterRequestInterface
{
    /**
     * Queue config key
     */
    public const QUEUE_CONFIG = 'queue';

    /**
     * Amqp config key
     */
    public const AMQP_CONFIG = 'amqp';

    public const HOST = 'host';
    public const PORT = 'port';
    public const USERNAME = 'user';
    public const PASSWORD = 'password';
    public const VIRTUALHOST = 'virtualhost';
    public const SSL = 'ssl';
    public const SSL_OPTIONS = 'ssl_options';

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var AbstractConnection
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
     * @var ConnectionFactory
     */
    private $connectionFactory;

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
     * @param ConnectionFactory|null $connectionFactory
     */
    public function __construct(
        DeploymentConfig $config,
        $connectionName = 'amqp',
        ?ConnectionFactory $connectionFactory = null
    ) {
        $this->deploymentConfig = $config;
        $this->connectionName = $connectionName;
        $this->connectionFactory = $connectionFactory
            ?: ObjectManager::getInstance()->get(ConnectionFactory::class);
    }

    /**
     * Destructor
     *
     * @return void
     * @since 103.0.0
     */
    public function __destruct()
    {
        try {
            $this->closeConnection();
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->closeConnection();
    }

    /**
     * Returns the configuration set for the key.
     *
     * @param string $key
     * @return string
     * @throws \LogicException
     * @since 103.0.0
     */
    public function getValue($key)
    {
        $this->load();
        return $this->data[$key] ?? null;
    }

    /**
     * Create amqp connection
     *
     * @return AbstractConnection
     */
    private function createConnection(): AbstractConnection
    {
        $sslEnabled = trim($this->getValue(self::SSL) ?? '') === 'true';
        $options = new FactoryOptions();
        $options->setHost($this->getValue(self::HOST));
        $options->setPort($this->getValue(self::PORT));
        $options->setUsername($this->getValue(self::USERNAME));
        $options->setPassword($this->getValue(self::PASSWORD));
        $options->setVirtualHost($this->getValue(self::VIRTUALHOST));
        $options->setSslEnabled($sslEnabled);
        /** @var array $sslOptions */
        if ($sslOptions = $this->getValue(self::SSL_OPTIONS)) {
            $options->setSslOptions($sslOptions);
        }

        return $this->connectionFactory->create($options);
    }

    /**
     * Return Amqp channel
     *
     * @return AMQPChannel
     * @throws \LogicException
     * @since 103.0.0
     */
    public function getChannel()
    {
        if (!isset($this->connection)) {
            $this->connection = $this->createConnection();
        }
        if (!isset($this->channel)
            || !$this->channel->getConnection()
            || !$this->channel->getConnection()->isConnected()
        ) {
            if (!$this->connection->isConnected()) {
                $this->connection->reconnect();
            }
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
