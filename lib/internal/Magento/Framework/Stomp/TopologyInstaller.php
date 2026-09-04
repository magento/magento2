<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\MessageQueue\Topology\ConfigInterface;
use Magento\Framework\Stomp\Topology\QueueInstaller;

/**
 * Class Topology creates topology for Stomp messaging
 */
class TopologyInstaller
{
    /**
     * @var ConfigInterface
     */
    private $topologyConfig;

    /**
     * @var \Magento\Framework\Stomp\Topology\QueueInstaller
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
     * @param QueueInstaller $queueInstaller
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $topologyConfig,
        QueueInstaller $queueInstaller,
        ConnectionTypeResolver $connectionTypeResolver,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->topologyConfig = $topologyConfig;
        $this->queueInstaller = $queueInstaller;
        $this->connectionTypeResolver = $connectionTypeResolver;
        $this->logger = $logger;
    }

    /**
     * Install Stomp Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install(): void
    {
        try {
            foreach ($this->topologyConfig->getQueues() as $queue) {
                if ($this->connectionTypeResolver->getConnectionType($queue->getConnection()) != 'stomp') {
                    continue;
                }
                $this->queueInstaller->install($queue);
            }
        } catch (\Exception $e) {
            $this->logger->error("STOMP topology installation failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }
    }
}
