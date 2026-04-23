<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Closure;
use Exception;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;
use Stomp\Transport\Frame;
use Stomp\Transport\Message;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The prefetch value is used to specify how many messages that are being sent to the consumer at the same time.
     * @see https://www.rabbitmq.com/consumer-prefetch.html
     * @var int
     */
    private $prefetchCount;

    /**
     * @var StompClient
     */
    private $stompProducerClient;

    /**
     * @var StompClient
     */
    private $stompConsumerClient;

    /**
     * @var StompClientFactory
     */
    private $stompClientFactory;

    /**
     * @var Frame
     */
    private $lastMessage;

    /**
     * @var CommunicationConfigInterface
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param Config $stompConfig
     * @param EnvelopeFactory $envelopeFactory
     * @param string $queueName
     * @param StompClientFactory $stompClientFactory
     * @param CommunicationConfigInterface $communicationConfig
     * @param LoggerInterface $logger
     * @param int $prefetchCount
     */
    public function __construct(
        Config $stompConfig,
        EnvelopeFactory $envelopeFactory,
        $queueName,
        StompClientFactory $stompClientFactory,
        CommunicationConfigInterface $communicationConfig,
        LoggerInterface $logger,
        $prefetchCount = 100
    ) {
        $this->stompConfig = $stompConfig;
        $this->queueName = $queueName;
        $this->envelopeFactory = $envelopeFactory;
        $this->logger = $logger;
        $this->prefetchCount = (int)$prefetchCount;
        $this->stompClientFactory = $stompClientFactory;
        $this->communicationConfig = $communicationConfig;
    }

    /**
     * @inheritdoc
     */
    public function dequeue()
    {
        $stompClient = $this->getStompConsumerClient();
        $envelope = null;
        $message = null;

        // @codingStandardsIgnoreStart
        /** @var Frame $message */
        try {
            $message = $this->readMessage();
            $this->lastMessage = $message;
        } catch (Exception $exception) {
            if ($message) {
                $stompClient->nackMessage($message);
            }
            throw new ConnectionLostException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );

        }

        if ($message) {
            $properties = $message->getHeaders();
            $envelope = $this->envelopeFactory->create(['body' => $message->getBody(), 'properties' => $properties]);
            $stompClient->ackMessage($message);
            $this->lastMessage = null;
        }
        return $envelope;

    }

    /**
     * @inheritdoc
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $stompClient = $this->getStompConsumerClient();
        if ($this->lastMessage) {
            $stompClient->ackMessage($this->lastMessage);
        }
    }

    /**
     * @inheritdoc
     */
    public function subscribe($callback)
    {
        $stompClient = $this->getStompConsumerClient();
        $stompClient->subscribeQueue($this->queueName);

        while (true) {
            $envelope = null;
            $message = $this->readMessage();

            if ($message) {
                $properties = $message->getHeaders();
                if($message->getBody()!=='') {
                    $envelope = $this->envelopeFactory->create(['body' => $message->getBody(), 'properties' => $properties]);
                    if ($callback instanceof Closure) {
                        $callback($envelope);
                    } else {
                        call_user_func($callback, $envelope);
                    }
                }
                $stompClient->ackMessage($message);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null) {

        $stompClient = $this->getStompConsumerClient();
        if($this->lastMessage){
            $stompClient->nackMessage($this->lastMessage);
        }

        if ($rejectionMessage !== null) {
            $this->logger->critical(
                new Phrase('Message has been rejected: %message', ['message' => $rejectionMessage])
            );
        }
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     * @throws ConnectionLostException
     */
    public function push(EnvelopeInterface $envelope)
    {
        $stompClient = $this->getStompProducerClient();
        $message = new Message($envelope->getBody(), $envelope->getProperties());
        try{
            $stompClient->send($this->queueName, $message);
            $stompClient->disconnect();
        }catch (\Stomp\Exception\StompException $e){
            $this->logger->info("Stomp message push failed: '{$this->queueName}' error: {$e->getMessage()}");
        }
    }

    /**
     * Push message to queue and read message from queue
     *
     * @param EnvelopeInterface $envelope
     * @return string|null
     * @throws ConnectionLostException
     * @throws LocalizedException
     */
    public function callRpc(EnvelopeInterface $envelope)
    {
        $stompClient = $this->getStompProducerClient();
        $properties = $envelope->getProperties();
        $topic = $properties['topic_name'];
        $topicData = $this->communicationConfig->getTopic($topic);
        $isSync = $topicData[CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS];

        $message = new Message($envelope->getBody(), $envelope->getProperties());
        try{
            $stompClient->send($this->queueName, $message);
            $stompClient->disconnect();
            if ($isSync){
                $stompConsumerClient = $this->getStompConsumerClient();
                $stompConsumerClient->subscribeQueue($this->queueName);
                $message = $this->readMessage();
                $stompConsumerClient->ackMessage($message);
                return $message->getBody();
            }
        }catch (\Stomp\Exception\StompException $e){
            $this->logger->info("Stomp rpc message push failed: '{$this->queueName}' error: '{$e->getMessage()}'");
        }

        return null;
    }

    /**
     * @return Frame|null
     * @throws ConnectionLostException
     */
    public function readMessage(): ?Frame
    {
        $message = null;
        $stompClient = $this->getStompConsumerClient();
        /** @var Frame $message */
        try {
            $message = $stompClient->readMessage();
            $this->lastMessage = $message;
        } catch (Exception $exception) {
            if ($message) {
                $stompClient->nackMessage($message);
            }
            throw new ConnectionLostException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
        return $message;
    }

    /**
     * @inheritdoc
     */
    public function subscribeQueue(): void
    {
        $stompClient = $this->getStompConsumerClient();
        $stompClient->subscribeQueue($this->queueName);
    }

    /**
     * Optimized queue clearing using Artemis REST API
     * Much faster than STOMP protocol and avoids TTL issues
     */
    public function clearQueue(): int
    {
        try {
            $stompClient = $this->getStompConsumerClient();

            // Use REST API for clearing - much faster and more reliable
            $clearedCount = $stompClient->clearQueue($this->queueName);

            if ($clearedCount > 0) {
                $this->logger->info("Successfully cleared {$clearedCount} messages from queue '{$this->queueName}' via REST API");
            }

            return $clearedCount;

        } catch (\Exception $e) {
            // Fallback to STOMP protocol if REST API fails
            $this->logger->warning("REST API failed, falling back to STOMP protocol: " . $e->getMessage());
            return $this->fallbackClearQueue();
        }
    }

    /**
     * Fallback queue clearing using STOMP protocol
     */
    private function fallbackClearQueue(): int
    {
        $clearedCount = 0;
        $maxMessages = 100; // Reduced limit for fallback

        try {
            $stompClient = $this->getStompConsumerClient();
            $stompClient->subscribeQueue($this->queueName);

            for ($i = 0; $i < $maxMessages; $i++) {
                try {
                    $message = $stompClient->readMessage();
                    if ($message === null) {
                        break; // No more messages
                    }
                    $stompClient->ackMessage($message);
                    $clearedCount++;
                } catch (\Exception $e) {
                    $this->logger->warning('Error reading message during fallback queue clear: ' . $e->getMessage());
                    break;
                }
            }

        } catch (\Exception $e) {
            $this->logger->error('Fallback queue clear also failed: ' . $e->getMessage());
        }

        return $clearedCount;
    }

    /**
     * Create object of stomp client as consumer
     *
     * @return StompClient
     */
    private function getStompConsumerClient(): StompClient
    {
        if($this->stompConsumerClient === null) {
            $this->stompConsumerClient = $this->stompClientFactory->create(['clientId' => 'consumer']);
        }
        return $this->stompConsumerClient;
    }

    /**
     * Create object of stomp client as producer
     *
     * @return StompClient
     */
    private function getStompProducerClient(): StompClient
    {
        if($this->stompProducerClient === null) {
            $this->stompProducerClient = $this->stompClientFactory->create(['clientId' => 'producer']);
        }
        return $this->stompProducerClient;
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
