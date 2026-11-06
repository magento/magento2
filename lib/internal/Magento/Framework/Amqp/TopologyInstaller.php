<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Topology\ExchangeInstaller;
use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\ConfigInterface;

/**
 * Class Topology creates topology for Amqp messaging
 */
class TopologyInstaller
{
    /**
     * @var ConfigInterface
     */
    private $topologyConfig;

    /**
     * @var \Magento\Framework\Amqp\Topology\ExchangeInstaller
     */
    private $exchangeInstaller;

    /**
     * @var ConfigPool
     */
    private $configPool;

    /**
     * @var \Magento\Framework\Amqp\Topology\QueueInstaller
     */
    private $queueInstaller;

    /**
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $topologyConfig
     * @param ExchangeInstaller $exchangeInstaller
     * @param ConfigPool $configPool
     * @param QueueInstaller $queueInstaller
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $topologyConfig,
        ExchangeInstaller $exchangeInstaller,
        ConfigPool $configPool,
        QueueInstaller $queueInstaller,
        ConnectionTypeResolver $connectionTypeResolver,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->topologyConfig = $topologyConfig;
        $this->exchangeInstaller = $exchangeInstaller;
        $this->configPool = $configPool;
        $this->queueInstaller = $queueInstaller;
        $this->connectionTypeResolver = $connectionTypeResolver;
        $this->logger = $logger;
    }

    /**
     * Install Amqp Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install()
    {
        try {
            $this->declareTopology();
        } catch (\Exception $e) {
            $this->logger->error("AMQP topology installation failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }
    }

    /**
     * Declare Amqp Exchanges, Queues and bind them, propagating broker failures to the caller.
     *
     * @return string[]
     * @throws \Exception
     */
    public function declareTopology(): array
    {
        $applied = [];
        foreach ($this->topologyConfig->getQueues() as $queue) {
            if ($this->connectionTypeResolver->getConnectionType($queue->getConnection()) != 'amqp') {
                continue;
            }
            $amqpConfig = $this->configPool->get($queue->getConnection());
            $this->queueInstaller->install($amqpConfig->getChannel(), $queue);
            $applied[] = sprintf(
                'Queue "%s" is in place on connection "%s".',
                $queue->getName(),
                $queue->getConnection()
            );
        }
        foreach ($this->topologyConfig->getExchanges() as $exchange) {
            if ($this->connectionTypeResolver->getConnectionType($exchange->getConnection()) != 'amqp') {
                continue;
            }
            $amqpConfig = $this->configPool->get($exchange->getConnection());
            $this->exchangeInstaller->install($amqpConfig->getChannel(), $exchange);
            $applied[] = sprintf(
                'Exchange "%s" is in place on connection "%s".',
                $exchange->getName(),
                $exchange->getConnection()
            );
        }

        return $applied;
    }
}
