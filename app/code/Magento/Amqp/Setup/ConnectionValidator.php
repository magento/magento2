<?php
/**
 * Copyright 2015 Adobe
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
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
