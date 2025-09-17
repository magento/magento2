<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use Magento\Framework\Stomp\StompClient;
use Magento\Framework\Stomp\StompClientFactory;
use Psr\Log\LoggerInterface;
use Stomp\Transport\Message;

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
     * @var StompClientFactory
     */
    private StompClientFactory $stompClient;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var StompClient
     */
    private $stompProducerClient;

    /**
     * @var StompClient
     */
    private $stompConsumerClient;

    /**
     * Initialize dependencies.
     *
     * @param StompClientFactory $stompClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        StompClientFactory $stompClient,
        LoggerInterface $logger
    ) {
        $this->stompClient = $stompClient;
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
            // Queue creation with blank message.
            $properties = [
                'destination-type' => self::DESTINATION_TYPE,
                'expires' => (string)((int)(microtime(true) * 100))
            ];
            $message = new Message('queue-created', $properties);
            $stompProducerClient = $this->getStompProducerClient();
            $stompProducerClient->send($queue->getName(), $message);

            // Read and acknowledge the blank message to delete
            $stompConsumerClient = $this->getStompConsumerClient();
            $stompConsumerClient->subscribeQueue($queue->getName());
            $stompConsumerClient->readMessage();
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Queue installation failed for "%s": %s', $queue->getName(), $e->getMessage()),
                ['exception' => $e]
            );
        }
    }

    /**
     * Create object of stomp client as producer
     *
     * @return StompClient
     */
    private function getStompProducerClient(): StompClient
    {
        if ($this->stompProducerClient === null) {
            $this->stompProducerClient = $this->stompClient->create(['clientId' => 'producer']);
        }
        return $this->stompProducerClient;
    }

    /**
     * Create object of stomp client as consumer
     *
     * @return StompClient
     */
    private function getStompConsumerClient(): StompClient
    {
        if ($this->stompConsumerClient === null) {
            $this->stompConsumerClient = $this->stompClient->create(['clientId' => 'consumer']);
        }
        return $this->stompConsumerClient;
    }
}
