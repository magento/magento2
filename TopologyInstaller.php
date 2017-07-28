<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Topology\ExchangeInstaller;
use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\ConfigInterface;

/**
 * Class Topology creates topology for Amqp messaging
 * @since 2.2.0
 */
class TopologyInstaller
{
    /**
     * @var ConfigInterface
     * @since 2.2.0
     */
    private $topologyConfig;

    /**
     * @var \Magento\Framework\Amqp\Topology\ExchangeInstaller
     * @since 2.2.0
     */
    private $exchangeInstaller;

    /**
     * @var ConfigPool
     * @since 2.2.0
     */
    private $configPool;

    /**
     * @var \Magento\Framework\Amqp\Topology\QueueInstaller
     * @since 2.2.0
     */
    private $queueInstaller;

    /**
     * @var ConnectionTypeResolver
     * @since 2.2.0
     */
    private $connectionTypeResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
