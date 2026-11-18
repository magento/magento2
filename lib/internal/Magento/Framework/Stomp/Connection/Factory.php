<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Connection;

use Magento\Framework\Stomp\Config;
use Stomp\Exception\ConnectionException;
use Stomp\Network\Connection;

/**
 * Create connection based on options.
 */
class Factory
{
    /**
     * Create a connection from the configured transport options.
     *
     * @param Config $config
     * @return Connection
     * @throws ConnectionException
     */
    public function createFromConfig(Config $config): Connection
    {
        $options = new FactoryOptions();
        $options->setHost($config->getValue(Config::HOST));
        $options->setPort($config->getValue(Config::PORT));
        $options->setUsername($config->getValue(Config::USERNAME));
        $options->setPassword($config->getValue(Config::PASSWORD));
        $options->setSslEnabled(trim($config->getValue(Config::SSL) ?? '') === 'true');
        $options->setSslOptions($config->getValue(Config::SSL_OPTIONS));

        return $this->create($options);
    }

    /**
     * Create connection according to given options.
     *
     * @param FactoryOptions $options
     * @return Connection
     * @throws ConnectionException
     */
    public function create(FactoryOptions $options): Connection
    {
        if ($options->isSslEnabled()) {
            $broker = 'ssl://' . $options->getHost() . ':' . $options->getPort();
            $streamContext = ['ssl' => $options->getSslOptions()];
            $connection = $this->createConnectionInstance($broker, 1, $streamContext);
        } else {
            $broker = 'tcp://' . $options->getHost() . ':' . $options->getPort();
            $connection = $this->createConnectionInstance($broker);
        }

        return $connection;
    }

    // In Factory.php

    /**
     * Create connection instance
     *
     * @param string $broker
     * @param int $timeout
     * @param array $context
     * @return Connection
     * @throws ConnectionException
     */
    protected function createConnectionInstance(string $broker, int $timeout = 1, array $context = []): Connection
    {
        return new Connection($broker, $timeout, $context);
    }
}
