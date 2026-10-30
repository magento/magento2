<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Setup;

use Magento\Framework\Amqp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Amqp\Connection\FactoryOptions;

/**
 * Class ConnectionValidator - validates Amqp related settings
 */
class ConnectionValidator
{
    /**
     * Minimum required RabbitMQ version
     */
    public const MINIMUM_RABBITMQ_VERSION = '4.3.0';

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * Checks Amqp Connection
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $virtualHost
     * @param bool $ssl
     * @param string[]|null $sslOptions
     * @return bool true if the connection succeeded, false otherwise
     */
    public function isConnectionValid(
        $host,
        $port,
        $user,
        $password = '',
        $virtualHost = '',
        bool $ssl = false,
        ?array $sslOptions = null
    ) {
        try {
            $options = new FactoryOptions();
            $options->setHost($host);
            $options->setPort($port);
            $options->setUsername($user);
            $options->setPassword($password);
            $options->setVirtualHost($virtualHost);
            $options->setSslEnabled($ssl);

            if ($sslOptions) {
                $options->setSslOptions($sslOptions);
            }

            $connection = $this->connectionFactory->create($options);

            $connection->close();
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    /**
     * Get the RabbitMQ server version via AMQP protocol handshake.
     *
     * Connects to the RabbitMQ server and retrieves the version from
     * server properties exchanged during the AMQP connection handshake.
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $virtualHost
     * @param bool $ssl
     * @param string[]|null $sslOptions
     * @return string|null Server version or null if unavailable
     */
    public function getServerVersion(
        $host,
        $port,
        $user,
        $password = '',
        $virtualHost = '',
        bool $ssl = false,
        ?array $sslOptions = null
    ): ?string {
        try {
            $options = new FactoryOptions();
            $options->setHost($host);
            $options->setPort($port);
            $options->setUsername($user);
            $options->setPassword($password);
            $options->setVirtualHost($virtualHost);
            $options->setSslEnabled($ssl);

            if ($sslOptions) {
                $options->setSslOptions($sslOptions);
            }

            $connection = $this->connectionFactory->create($options);

            try {
                $properties = $connection->getServerProperties();
                $versionData = $properties['version'] ?? null;

                if (is_array($versionData) && isset($versionData[1])) {
                    // AMQP table format: ['S', 'version_string']
                    return (string)$versionData[1];
                } elseif (is_string($versionData)) {
                    return $versionData;
                }

                return null;
            } finally {
                try {
                    $connection->close();
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                } catch (\Exception) {
                    // Ignore errors closing connection
                }
            }
        } catch (\Exception) {
            return null;
        }
    }
}
