<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stomp\Jolokia\ClientInterface as JolokiaClient;
use Psr\Log\LoggerInterface;
use Stomp\Transport\Frame;
use Stomp\Transport\FrameFactory;
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
     * @var StompClientFactory
     */
    private $stompClientFactory;

    /**
     * @var CommunicationConfigInterface
     */
    private $communicationConfig;

    /**
     * @var FrameFactory
     */
    private $frameFactory;

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
     * @param StompClientInterface|null $stompClient
     * @param JolokiaClient|null $jolokiaClient
     * @param FrameFactory|null $frameFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Config $stompConfig,
        EnvelopeFactory $envelopeFactory,
        $queueName,
        StompClientFactory $stompClientFactory,
        CommunicationConfigInterface $communicationConfig,
        LoggerInterface $logger,
        $prefetchCount = 100,
        private ?StompClientInterface $stompClient = null,
        private readonly ?JolokiaClient $jolokiaClient = null,
        ?FrameFactory $frameFactory = null,
    ) {
        $this->stompConfig = $stompConfig;
        $this->queueName = $queueName;
        $this->envelopeFactory = $envelopeFactory;
        $this->logger = $logger;
        $this->prefetchCount = (int)$prefetchCount;
        $this->stompClientFactory = $stompClientFactory;
        $this->communicationConfig = $communicationConfig;
        $this->frameFactory = $frameFactory ?? ObjectManager::getInstance()
            ->get(FrameFactory::class);
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
            $stompClient->subscribeQueue($this->queueName);
            $message = $stompClient->readMessage($this->queueName);
        } catch (Exception $exception) {
            throw new ConnectionLostException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );

        }

        if ($message) {
            $properties = $message->getHeaders();
            $properties['command'] = $message->getCommand();
            $properties['is_legacy_mode'] = $message->isLegacyMode();
            $envelope = $this->envelopeFactory->create(['body' => $message->getBody(), 'properties' => $properties]);
        }
        return $envelope;

    }

    /**
     * @inheritdoc
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $stompClient = $this->getStompConsumerClient();
        $frame = $this->transformToFrame($envelope);
        $stompClient->ackMessage($frame);
    }

    /**
     * @inheritdoc
     */
    public function subscribe($callback)
    {
        $stompClient = $this->getStompConsumerClient();
        $stompClient->subscribeQueue($this->queueName);

        while (true) {
            $envelope = $this->dequeue();
            if ($envelope) {
                call_user_func($callback, $envelope);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null) {

        $stompClient = $this->getStompConsumerClient();
        $frame = $this->transformToFrame($envelope);
        $stompClient->nackMessage($frame, $requeue);

        if ($rejectionMessage !== null) {
            $this->logger->critical(
                new Phrase('Message has been rejected: %message', ['message' => $rejectionMessage])
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function push(EnvelopeInterface $envelope)
    {
        $stompClient = $this->getStompProducerClient();
        $message = new Message($envelope->getBody(), $envelope->getProperties());
        try{
            $stompClient->send($this->queueName, $message);
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
    #[\Deprecated('Method does nothing. It returns null or the same message passed to it.')]
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
     * @deprecated
     * @see self::dequeue
     * @see StompClientInterface::readMessage
     */
    #[\Deprecated('Method returns protocol-specific data. Use dequeue() method to read messages.')]
    public function readMessage(): ?Frame
    {
        $message = null;
        $stompClient = $this->getStompConsumerClient();
        /** @var Frame $message */
        try {
            $message = $stompClient->readMessage($this->queueName);
        } catch (Exception $exception) {
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
    #[\Deprecated('This is a detail of protocol implementation. It should not be used directly.')]
    public function subscribeQueue(): void
    {
        $stompClient = $this->getStompConsumerClient();
        $stompClient->subscribeQueue($this->queueName);
    }

    /**
     * @inheritdoc
     *
     * Optimized queue clearing using Artemis REST API
     * Much faster than STOMP protocol and avoids TTL issues
     */
    public function clearQueue(): int
    {
        try {
            $stompClient = $this->getStompConsumerClient();
            $stompClient->unsubscribeQueue($this->queueName);

            if ($this->jolokiaClient) {
                // Use Jolokia API for clearing - much faster and more reliable.
                $clearedCount = $this->jolokiaClient->clearQueue($this->queueName);
            } else {
                $clearedCount = $this->fallbackClearQueue();
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
                    $message = $stompClient->readMessage($this->queueName);
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
        if ($this->stompClient === null) {
            $this->stompClient = $this->stompClientFactory->create(['stompConfig' => $this->stompConfig]);
        }
        return $this->stompClient;
    }

    /**
     * Create object of stomp client as producer
     *
     * @return StompClient
     */
    private function getStompProducerClient(): StompClient
    {
        if ($this->stompClient === null) {
            $this->stompClient = $this->stompClientFactory->create(['stompConfig' => $this->stompConfig]);
        }
        return $this->stompClient;
    }

    /**
     * Transform envelope object to frame.
     *
     * @param EnvelopeInterface $envelope
     * @return Frame
     */
    private function transformToFrame(EnvelopeInterface $envelope): Frame
    {
        $properties = $envelope->getProperties();
        $command = $properties['command'];
        $isLegacyMode = $properties['is_legacy_mode'];
        $headers = array_diff_key($properties, array_flip(['command', 'is_legacy_mode']));

        return $this->frameFactory->createFrame($command, $headers, $envelope->getBody(), $isLegacyMode);
    }

    /**
     * Get connection name
     *
     * @return string
     */
    #[\Deprecated('Connection name is just a config alias. It should not be used outside of loading config data.')]
    public function getConnectionName(): string
    {
        return $this->stompConfig->getConnectionName();
    }
}
