<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Connection;

use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\StompClient;
use Stomp\Client;
use Stomp\Exception\ConnectionException;
use Stomp\Network\Connection;
use Stomp\Network\Observer\HeartbeatEmitter;

class ClientFactory
{
    /**
     * @param Factory $connectionFactory
     */
    public function __construct(private readonly Factory $connectionFactory)
    {
    }

    /**
     * Create a configured client, optionally with an independent connection and parser.
     *
     * @param Config $config
     * @param bool $isolated
     * @return Client
     * @throws ConnectionException
     */
    public function create(Config $config, bool $isolated = false): Client
    {
        $connection = $isolated ? $this->createConnection($config) : clone $config->getConnection();
        $readTimeout = $config->getValue('read_timeout') ?? StompClient::READ_TIME_OUT;
        $connection->setReadTimeout(0, $readTimeout);
        $connection->getObservers()->addObserver(new HeartbeatEmitter($connection));

        $client = new Client($connection);
        $client->setVersions([StompClient::VERSION]);
        $client->setLogin($config->getValue(Config::USERNAME), $config->getValue(Config::PASSWORD));
        $heartbeatSend = $config->getValue('heartbeat_send') ?? StompClient::HEARTBEAT_SEND_TIME;
        $heartbeatReceive = $config->getValue('heartbeat_receive') ?? StompClient::HEARTBEAT_RECEIVE_TIME;
        $client->setHeartbeat($heartbeatSend, $heartbeatReceive);

        return $client;
    }

    /**
     * Create an independent connection without sharing the cached connection's parser.
     *
     * @param Config $config
     * @return Connection
     * @throws ConnectionException
     */
    private function createConnection(Config $config): Connection
    {
        $options = new FactoryOptions();
        $options->setHost($config->getValue(Config::HOST));
        $options->setPort($config->getValue(Config::PORT));
        $options->setSslEnabled(trim($config->getValue(Config::SSL) ?? '') === 'true');
        $options->setSslOptions($config->getValue(Config::SSL_OPTIONS));

        return $this->connectionFactory->create($options);
    }
}
