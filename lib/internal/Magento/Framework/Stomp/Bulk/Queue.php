<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Bulk;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Bulk\Queue\QueueInterface;
use Magento\Framework\MessageQueue\QueueInterface as BaseQueueInterface;
use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\StompClient;
use Magento\Framework\Stomp\StompClientFactory;
use Stomp\Exception\StompException;
use Stomp\Transport\Message;

/**
 * @api
 */
class Queue implements QueueInterface
{

    /**
     * @var Config
     */
    private $stompConfig;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var StompClientFactory
     */
    private StompClientFactory $stompClientFactory;

    /**
     * @var StompClient
     */
    private ?StompClient $stompClient = null;

    /**
     * @var CommunicationConfigInterface
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param Config $stompConfig
     * @param string $queueName
     * @param StompClientFactory $stompClientFactory
     * @param CommunicationConfigInterface $communicationConfig
     */
    public function __construct(
        Config             $stompConfig,
        string             $queueName,
        StompClientFactory $stompClientFactory,
        CommunicationConfigInterface $communicationConfig
    ) {
        $this->stompConfig = $stompConfig;
        $this->queueName = $queueName;
        $this->communicationConfig = $communicationConfig;
        $this->stompClientFactory = $stompClientFactory;
    }

    /**
     * Push a message to queue
     *
     * @param BaseQueueInterface $queue
     * @param string $topic
     * @param array $envelopes
     * @return array|null
     * @throws LocalizedException
     * @throws StompException
     * @inheritdoc
     */
    public function push(BaseQueueInterface $queue, string $topic, array $envelopes): ?array
    {
        $topicData = $this->communicationConfig->getTopic($topic);
        $isSync = $topicData[CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS];

        if ($isSync) {
            $responses = [];
            foreach ($envelopes as $envelope) {
                $responses[] = $queue->callRpc($envelope);
            }
            return $responses;
        }

        $stompClient = $this->getStompClient();
        $stompClient->transactionBegin();
        foreach ($envelopes as $envelope) {
            // Send a persistent message - StompClient now handles retries internally
            $message = new Message($envelope->getBody(), $envelope->getProperties());

            try {

                $stompClient->send($this->queueName, $message);
            } catch (\Exception $e) {
                // StompClient has already performed retries, this is a final failure
                error_log("Bulk queue failed to send message to '{$this->queueName}': {$e->getMessage()}");
                throw $e;
            }
        }
        $stompClient->transactionCommit();
        $stompClient->disconnect();

        return null;
    }

    /**
     * Get StompClient object
     *
     * @return StompClient
     */
    private function getStompClient(): StompClient
    {
        if ($this->stompClient === null || !$this->stompClient->isConnected()) {
            $this->stompClient = $this->stompClientFactory->create(['clientId' => 'producer']);
        }
        return $this->stompClient;
    }

    /**
     * Get connection name
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->stompConfig->getConnectionName();
    }
}
