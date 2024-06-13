<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Stomp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Stomp\Connection\FactoryOptions;
use Stomp\Exception\ConnectionException;
use Stomp\Network\Connection;

/**
 * Reads the Stomp config in the deployed environment configuration
 *
 * @api
 */
class Config implements ResetAfterRequestInterface
{
    /**
     * Queue config key
     */
    public const QUEUE_CONFIG = 'queue';

    /**
     * Stomp config key
     */
    public const STOMP_CONFIG = 'stomp';

    public const HOST = 'host';
    public const PORT = 'port';
    public const USERNAME = 'user';
    public const PASSWORD = 'password';
    public const SSL = 'ssl';
    public const SSL_OPTIONS = 'ssl_options';

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Associative array of Stomp configuration
     *
     * @var array
     */
    private $data;

    /**
     * STOMP connection name.
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
     *         'stomp' => [
     *             'host' => 'localhost',
     *             'port' => 61613,
     *             'username' => 'artemis',
     *             'password' => 'artemis',
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
        $connectionName = 'stomp',
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
     * @return mixed
     * @throws \LogicException
     * @since 103.0.0
     */
    public function getValue($key): mixed
    {
        $this->load();
        return $this->data[$key] ?? null;
    }

    /**
     * Create stomp connection
     *
     * @return Connection
     * @throws ConnectionException
     */
    private function createConnection(): Connection
    {
        $sslEnabled = trim($this->getValue(self::SSL) ?? '') == 'true';
        $options = new FactoryOptions();
        $options->setHost($this->getValue(self::HOST));
        $options->setPort($this->getValue(self::PORT));
        $options->setUsername($this->getValue(self::USERNAME));
        $options->setPassword($this->getValue(self::PASSWORD));
        $options->setSslEnabled($sslEnabled);
        /** @var array $sslOptions */
        if ($sslOptions = $this->getValue(self::SSL_OPTIONS)) {
            $options->setSslOptions($sslOptions);
        }

        return $this->connectionFactory->create($options);
    }

    /**
     * Return Stomp connection
     *
     * @return Connection
     * @throws \LogicException\
     */
    public function getConnection() : Connection
    {
        if (!isset($this->connection)) {
            $this->connection = $this->createConnection();
        }
        return $this->connection;
    }
    /**
     * Load the configuration for Stomp
     *
     * @return void
     * @throws \LogicException
     */
    private function load(): void
    {
        if (null === $this->data) {
            $queueConfig = $this->deploymentConfig->getConfigData(self::QUEUE_CONFIG);
            if ($this->connectionName == self::STOMP_CONFIG) {
                $this->data = isset($queueConfig[self::STOMP_CONFIG]) ? $queueConfig[self::STOMP_CONFIG] : [];
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
     * Close Stomp connection and Channel
     *
     * @return void
     */
    private function closeConnection(): void
    {
        if (isset($this->connection)) {
            $this->connection->disconnect();
            unset($this->connection);
        }
    }

    /**
     * Get connection name
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return$this->connectionName;
    }
}
