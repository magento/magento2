<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Stomp\Setup;

use Magento\Framework\Stomp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Stomp\Connection\FactoryOptions;

/**
 * Class ConnectionValidator - validates Stomp related settings
 */
class ConnectionValidator
{
    /**
     * @var ConnectionFactory
     */
    private ConnectionFactory $connectionFactory;

    /**
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * Checks Stomp Connection
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param bool $ssl
     * @param string[]|null $sslOptions
     * @return bool true if the connection succeeded, false otherwise
     */
    public function isConnectionValid(
        string $host,
        string $port,
        string $user,
        string $password = '',
        bool   $ssl = false,
        ?array $sslOptions = null
    ): bool {
        try {
            $options = new FactoryOptions();
            $options->setHost($host);
            $options->setPort($port);
            $options->setUsername($user);
            $options->setPassword($password);
            $options->setSslEnabled($ssl);

            if ($sslOptions) {
                $options->setSslOptions($sslOptions);
            }

            $connection = $this->connectionFactory->create($options);

            $connection->disconnect();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
