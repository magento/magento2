<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use Magento\Framework\Stomp\StompClientProvider;
use Psr\Log\LoggerInterface;

/**
 * Queue installer to install queues in ActiveMq.
 */
class QueueInstaller
{
    /**
     * Destination type to create queue in ActiveMq
     */
    public const DESTINATION_TYPE = 'ANYCAST';

    /**
     * @var StompClientProvider
     */
    private StompClientProvider $stompClientProvider;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Initialize dependencies.
     *
     * @param StompClientProvider $stompClientProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        StompClientProvider $stompClientProvider,
        LoggerInterface $logger
    ) {
        $this->stompClientProvider = $stompClientProvider;
        $this->logger = $logger;
    }

    /**
     * Install queue.
     *
     * @param QueueConfigItemInterface $queue
     * @return void
     */
    public function install(QueueConfigItemInterface $queue): void
    {
        try {
            $this->stompClientProvider->get($queue->getConnection())->createQueue($queue->getName());
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Queue installation failed for "%s": %s', $queue->getName(), $e->getMessage()),
                ['exception' => $e]
            );
        }
    }
}
