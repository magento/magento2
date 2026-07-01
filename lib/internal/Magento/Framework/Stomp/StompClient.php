<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Stomp\Exception\ClientException;
use Stomp\Client;
use Stomp\Exception\ConnectionException;
use Stomp\Exception\ErrorFrameException;
use Stomp\Exception\StompException;
use Stomp\Network\Observer\HeartbeatEmitter;
use Stomp\StatefulStomp;
use Stomp\Transport\Frame;
use Stomp\Transport\Message;

/**
 * Wrapper StompClient class for stomp connection
 */
class StompClient implements StompClientInterface, ResetAfterRequestInterface
{
    /**
     * ACK type to communicate with stomp queue
     *
     * @var string
     */
    public const ACK_TYPE = 'client-individual';

    /**
     * Stomp version
     *
     * @var string
     */
    public const VERSION = '1.2';

    /**
     * Heartbeat sends time
     *
     * @var int
     */
    public const HEARTBEAT_SEND_TIME = 10000;

    /**
     * Stomp version
     *
     * @var int
     */
    public const HEARTBEAT_RECEIVE_TIME = 10000;

    /**
     * Stomp version
     *
     * @var int
     */
    public const READ_TIME_OUT = 250000;

    /**
     * AMQ229014: Did not receive data from X.X.X.X:X within the Xms connection TTL. The connection will now be closed.
     */
    private const string CONNECTION_CLOSED_ERROR_CODE = 'AMQ229014';

    /**
     * @var Config
     */
    private $stompConfig;

    /**
     * @var string|null
     */
    private ?string $brokerName = null;

    /**
     * @var StatefulStomp|null
     */
    private ?StatefulStomp $producer = null;

    /**
     * @var StatefulStomp[]
     */
    private array $consumers = [];

    /**
     * @param Config $stompConfig
     * @param array $subscriptionHeaders
     */
    public function __construct(
        Config $stompConfig,
        private readonly array $subscriptionHeaders = [],
    ) {
        $this->stompConfig = $stompConfig;
        try {
            $this->brokerName = $this->getBrokerName();
        } catch (StompException) {
            $this->brokerName = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function send(string $queue, Message $message): void
    {
        $producer = $this->getProducer();
        try {
            $this->safeExecute($producer, 'send', [$queue, $message], fn () => $producer->send($queue, $message));
        } catch (StompException $e) {
            $producer->getClient()->disconnect();
            throw new ClientException("Failed to send message to '$queue' queue.", previous: $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function sendBatch(string $queue, array $messages): void
    {
        $producer = $this->getProducer();
        try {
            $this->safeExecute($producer, 'begin', onRetry: $producer->begin(...));
            foreach ($messages as $message) {
                $producer->send($queue, $message);
            }
            $producer->commit();
        } catch (StompException $e) {
            $producer->getClient()->disconnect();
            // To eliminate transaction state of producer.
            $this->producer = null;
            throw new ClientException("Failed to send batch of messages to '$queue' queue.", previous: $e);
        }
    }

    /**
     * Determine if an error is retryable
     *
     * @param string $errorMessage
     * @return bool
     */
    private function isRetryableError(string $errorMessage): bool
    {
        return str_starts_with($errorMessage, self::CONNECTION_CLOSED_ERROR_CODE);
    }

    /**
     * Get subscription for the queue if exists.
     *
     * @param string $queue
     * @return int|null
     */
    private function getQueueSubscriptionId(string $queue): ?int
    {
        $stompConsumer = $this->getConsumer($queue);
        foreach ($stompConsumer->getSubscriptions() as $subscription) {
            if ($subscription->getDestination() === $queue) {
                return $subscription->getSubscriptionId();
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function subscribeQueue(string $queue): void
    {
        $this->getProducer()->getClient()->disconnect();
        $subscriptionId = $this->getQueueSubscriptionId($queue);
        if ($subscriptionId) {
            // Already subscribed to the requested queue.
            return;
        }

        $stompConsumer = $this->getConsumer($queue);
        try {
            $headers = $this->subscriptionHeaders[$this->brokerName] ?? [];
            $args = ['destination' => $queue, 'ack' => self::ACK_TYPE, 'header' => $headers];
            $this->safeExecute(
                $stompConsumer,
                'subscribe',
                $args,
                fn () => $this->getConsumer($queue)->subscribe(...$args)
            );
        } catch (StompException $e) {
            throw new ClientException("Failed to subscribe to '$queue' queue.", previous: $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function unsubscribeQueue(string $queue): void
    {
        $subscriptionId = $this->getQueueSubscriptionId($queue);
        if (!$subscriptionId) {
            return;
        }

        $stompConsumer = $this->getConsumer($queue);
        try {
            $stompConsumer->unsubscribe($subscriptionId);
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (StompException) {
        }
        $stompConsumer->getClient()->disconnect();
    }

    /**
     * @inheritdoc
     */
    public function readMessage(string $queue): ?Frame
    {
        $stompConsumer = $this->getConsumer($queue);
        try {
            $frame = $this->safeExecute(
                $stompConsumer,
                'read',
                onRetry: function () use ($queue) {
                    $this->subscribeQueue($queue);
                    $consumer = $this->getConsumer($queue);

                    return $consumer->read();
                }
            );
        } catch (StompException $e) {
            throw new ClientException("Unable to read message from '$queue' queue.", previous: $e);
        }

        return $frame ?: null;
    }

    /**
     * @inheritdoc
     */
    public function ackMessage(Frame $lastFrame): void
    {
        $queue = $lastFrame['destination'];
        $stompConsumer = $this->getConsumer($queue);
        try {
            $this->sendFrame($lastFrame, $stompConsumer->getClient()->getProtocol()->getAckFrame(...));
        } catch (StompException $e) {
            throw new ClientException("Unable to ACK message from '$queue' queue.", previous: $e);
        }
    }

    /**
     * @inheritdoc
     *
     * STOMP PHP client doesn't support requeue.
     * @see https://github.com/stomp-php/stomp-php/blob/5.1.0/src/States/IStateful.php#L34
     *
     * Sending a NACK frame to ActiveMQ Artemis is equal to acknowledging.
     * @see https://github.com/apache/artemis/blob/2.42.0/artemis-protocols/artemis-stomp-protocol/src/main/java/org/apache/activemq/artemis/core/protocol/stomp/v11/StompFrameHandlerV11.java#L224-L227
     *
     * To return the message back to the queue client should unsubscribe, considering the used acknowledgment type.
     * @see self::ACK_TYPE
     * @see https://stomp.github.io/stomp-specification-1.2.html#SUBSCRIBE_ack_Header
     */
    public function nackMessage(Frame $lastFrame, ?bool $requeue = null): void
    {
        $queue = $lastFrame['destination'];
        $stompConsumer = $this->getConsumer($queue);
        $subscription = $stompConsumer->getSubscriptions()->getSubscription($lastFrame);
        if ($requeue) {
            if (!$subscription) {
                // Already requeued.
                return;
            }

            try {
                $stompConsumer->unsubscribe($subscription->getSubscriptionId());
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (StompException) {
            }
            $stompConsumer->getClient()->disconnect();
        } else {
            try {
                $this->sendFrame($lastFrame, $stompConsumer->getClient()->getProtocol()->getNackFrame(...));
            } catch (StompException $e) {
                throw new ClientException("Unable to NACK message from '$queue' queue.", previous: $e);
            }
        }
    }

    /**
     * Try to re-read the same message from the queue after connection failure.
     *
     * @param Frame $frame
     * @return Frame|null
     */
    private function reReadMessage(Frame $frame): ?Frame
    {
        $messageId = $frame['message_id'] ?? null;
        if (!$messageId) {
            // No message identifier, can't ensure that the same message is read.
            return null;
        }

        $queue = $frame['destination'];
        try {
            // Resubscribe and try to get the message.
            $this->subscribeQueue($queue);
            // Get fresh consumer instance after possible reconnect.
            $stompConsumer = $this->getConsumer($queue);
            $newFrame = $stompConsumer->read();
            // Double check that the correct message is received.
            if (!$newFrame || !isset($newFrame['message_id']) || $newFrame['message_id'] !== $messageId) {
                // It's not the needed message.
                $this->unsubscribeQueue($queue);
                $newFrame = null;
            }
        } catch (StompException) {
            $stompConsumer = $this->getConsumer($queue);
            $stompConsumer->getClient()->disconnect();
            $newFrame = null;
        }

        return $newFrame;
    }

    /**
     * Try to execute operation through active connection.
     *
     * In case of connection failure, it will be disconnected and onRetry callback will be executed if provided.
     *
     * @param StatefulStomp $clientWrapper
     * @param string $method
     * @param array $args
     * @param callable|null $onRetry
     * @return mixed
     * @throws StompException
     */
    private function safeExecute(
        StatefulStomp $clientWrapper,
        string $method,
        array $args = [],
        ?callable $onRetry = null
    ): mixed {
        try {
            $result = $clientWrapper->$method(...$args);
        } catch (ErrorFrameException $e) {
            $errorFrame = $e->getFrame();
            if (!$this->isRetryableError($errorFrame['message'])) {
                throw $e;
            }
            $clientWrapper->getClient()->disconnect();
            $result = $onRetry ? call_user_func($onRetry) : null;
        } catch (ConnectionException) {
            $clientWrapper->getClient()->disconnect();
            $result = $onRetry ? call_user_func($onRetry) : null;
        }

        return $result;
    }

    /**
     * Send ACK or NACK frame with retry logic in case of connection failure.
     *
     * @param Frame $message
     * @param callable $frameFactory
     * @return void
     * @throws StompException
     */
    private function sendFrame(Frame $message, callable $frameFactory): void
    {
        $queue = $message['destination'];
        $stompConsumer = $this->getConsumer($queue);
        $client = $stompConsumer->getClient();
        if (!$client->isConnected() || !$stompConsumer->getSubscriptions()->getSubscription($message)) {
            $newMessage = $this->reReadMessage($message);
            if (!$newMessage) {
                return;
            }

            $message = $newMessage;
            $stompConsumer = $this->getConsumer($queue);
        }

        $isSuccessful = false;
        $client = $stompConsumer->getClient();
        $frame = $frameFactory($message);
        try {
            $isSuccessful = $client->sendFrame($frame, true);
        } catch (ErrorFrameException $e) {
            $errorFrame = $e->getFrame();
            if (!$this->isRetryableError($errorFrame['message'])) {
                throw $e;
            }
            $client->disconnect();
        } catch (ConnectionException) {
            $client->disconnect();
        }

        if (!$isSuccessful) {
            $newMessage = $this->reReadMessage($message);
            if (!$newMessage) {
                return;
            }

            $stompConsumer = $this->getConsumer($queue);
            $client = $stompConsumer->getClient();
            $frame = $frameFactory($newMessage);
            $client->sendFrame($frame, true);
        }
    }

    /**
     * Create new client instance.
     *
     * @return Client
     * @throws ConnectionException
     */
    private function createClient(): Client
    {
        $connection = clone $this->stompConfig->getConnection();
        $readTimeout = $this->stompConfig->getValue('read_timeout') ?? self::READ_TIME_OUT;
        $connection->setReadTimeout(0, $readTimeout);
        $emitter = new HeartbeatEmitter($connection);
        $connection->getObservers()->addObserver($emitter);

        $client = new Client($connection);
        $client->setVersions([self::VERSION]);
        $client->setLogin(
            $this->stompConfig->getValue(Config::USERNAME),
            $this->stompConfig->getValue(Config::USERNAME)
        );
        $heartbeatSend = $this->stompConfig->getValue('heartbeat_send') ?? self::HEARTBEAT_SEND_TIME;
        $heartbeatReceive = $this->stompConfig->getValue('heartbeat_receive') ?? self::HEARTBEAT_RECEIVE_TIME;
        $client->setHeartbeat($heartbeatSend, $heartbeatReceive);

        return $client;
    }

    /**
     * Get producer instance.
     *
     * @return StatefulStomp
     */
    private function getProducer(): StatefulStomp
    {
        $this->producer ??= new StatefulStomp($this->createClient());

        return $this->producer;
    }

    /**
     * Get consumer instance.
     *
     * @param string $queue
     * @return StatefulStomp
     */
    private function getConsumer(string $queue): StatefulStomp
    {
        $consumer = $this->consumers[$queue] ?? null;
        if (!$consumer?->getClient()->isConnected()) {
            // Recreate object as it might have active subscription from inactive connection.
            $consumer = $this->consumers[$queue] = new StatefulStomp($this->createClient());
        }

        return $consumer;
    }

    /**
     * Get broker name the client is connecting to.
     *
     * @return string
     * @throws StompException
     */
    private function getBrokerName(): string
    {
        $client = $this->createClient();
        try {
            $client->connect();
            $server = $client->getProtocol()->getServer();
        } finally {
            $client->disconnect();
        }

        preg_match('~^([^/]+)/~', $server, $matches);
        $brokerName = $matches[1] ?? $server;

        return $brokerName;
    }

    /**
     * Close all active connections.
     *
     * @return void
     */
    private function closeConnections(): void
    {
        $this->producer?->getClient()->disconnect();
        foreach ($this->consumers as $consumer) {
            $consumer->getClient()->disconnect();
        }
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->closeConnections();
    }

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        $this->closeConnections();
    }
}
