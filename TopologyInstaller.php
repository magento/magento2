<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            foreach ($this->topologyConfig->getQueues() as $queue) {
                if ($this->connectionTypeResolver->getConnectionType($queue->getConnection()) != 'amqp') {
                    continue;
                }
                $amqpConfig = $this->configPool->get($queue->getConnection());
                $this->queueInstaller->install($amqpConfig->getChannel(), $queue);
            }
            foreach ($this->topologyConfig->getExchanges() as $exchange) {
                if ($this->connectionTypeResolver->getConnectionType($exchange->getConnection()) != 'amqp') {
                    continue;
                }
                $amqpConfig = $this->configPool->get($exchange->getConnection());
                $this->exchangeInstaller->install($amqpConfig->getChannel(), $exchange);
            }
        } catch (\PhpAmqpLib\Exception\AMQPExceptionInterface $e) {
            $this->logger->error("AMQP topology installation failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }
    }
}
