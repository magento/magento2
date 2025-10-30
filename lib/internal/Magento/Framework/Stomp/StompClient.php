<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Psr\Log\LoggerInterface;
use Stomp\Client;
use Stomp\Exception\StompException;
use Stomp\Network\Observer\HeartbeatEmitter;
use Stomp\StatefulStomp;
use Stomp\Transport\Frame;
use Stomp\Transport\Message;

/**
 * Wrapper StompClient class for stomp connection
 */
class StompClient implements StompClientInterface
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
     * @var Config
     */
    private $stompConfig;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var StatefulStomp
     */
    private $stompProducer;

    /**
     * @var StatefulStomp
     */
    private $stompConsumer;

    /**
     * @var Frame
     */
    private $lastFrame;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array
     */
    protected array $acknowledged;

    /**
     * @var array
     */
    public static $persistentclient;

    /**
     * @var string
     */
    private string $clientId;

    /**
     * @param Config $stompConfig
     * @param LoggerInterface $logger
     * @param string $clientId
     * @throws StompException
     */
    public function __construct(
        Config $stompConfig,
        LoggerInterface $logger,
        string $clientId
    ) {
        $this->stompConfig = $stompConfig;
        $this->logger = $logger;
        $this->clientId = $clientId;
        $this->connect();
    }

    /**
     * @inheritdoc
     *
     * @throws StompException
     */
    public function send(string $queue, Message $message): void
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                // Ensure we have a healthy connection
                $this->ensureHealthyConnection();

                // Initialize stomp if needed
                if (!$this->stompProducer) {
                    $this->stompProducer = new StatefulStomp($this->client);
                }

                $this->stompProducer->send($queue, $message);
                return; // Success, exit retry loop

            } catch (\Exception $e) {
                $retryCount++;
                $this->handleSendError($e, $retryCount, $maxRetries, $queue, $message);

                // Reset connection state for retry
                $this->resetConnectionState();

                if ($retryCount < $maxRetries) {
                    // Progressive backoff: 100ms, 200ms, 400ms
                    usleep(100000 * $retryCount);
                }
            }
        }
    }

    /**
     * Ensure we have a healthy connection
     *
     * @throws StompException
     */
    private function ensureHealthyConnection(): void
    {
        if (!$this->client || !$this->client->isConnected()) {
            $this->logger->info('STOMP connection lost, attempting to reconnect');
            $this->stompProducer = null;
            $this->stompConsumer = null;
            $this->lastFrame = null;
            $this->connect();
        }

        // Additional health check - verify we can get the underlying connection
        try {
            $connection = $this->client->getConnection();
            if (!$connection || !$connection->isConnected()) {
                throw new StompException('Connection is not healthy');
            }
        } catch (\Exception $e) {
            $this->logger->warning('Connection health check failed: ' . $e->getMessage());
            $this->connect();
        }
    }

    /**
     * Handle send operation errors
     *
     * @param \Exception $e
     * @param int $retryCount
     * @param int $maxRetries
     * @param string $queue
     * @param Message $message
     * @throws StompException
     */
    private function handleSendError(
        \Exception $e,
        int $retryCount,
        int $maxRetries,
        string $queue,
        Message $message
    ): void {
        $errorMessage = $e->getMessage();
        $isRetryableError = $this->isRetryableError($errorMessage);
        $body = null;
        $headers = null;
        if ($message) {
            $body = $message->getBody();
            $headers = $message->getHeaders();
        }
        $this->logger->warning(
            "STOMP send attempt {$retryCount}/{$maxRetries} failed: {$errorMessage}",
            [
                'queue' => 'current_queue-'.$queue,
                'retryable' => $isRetryableError,
                'exception' => $e,
                'message' => $body,
                'headers' => $headers,
                'trace' => $e->getTraceAsString(),
            ]
        );

        if ($retryCount >= $maxRetries) {
            $this->logger->error(
                "Failed to send STOMP message after {$maxRetries} attempts: {$errorMessage}",
                ['exception' => $e]
            );
            throw new StompException(
                "Failed to send message after {$maxRetries} attempts: {$errorMessage}",
                $e->getCode(),
                $e
            );
        }

        if (!$isRetryableError && $retryCount < $maxRetries) {
            $this->logger->info('Non-retryable error encountered, but retrying anyway due to connection issues');
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
        $retryablePatterns = [
            'connection',
            'write frame',
            'broken pipe',
            'network',
            'timeout',
            'AMQ229014', // ActiveMQ specific error
            'AMQ229031', // Connection lost
            'disconnected',
            'socket',
            'not connected'
        ];

        $lowerMessage = strtolower($errorMessage);
        foreach ($retryablePatterns as $pattern) {
            if (strpos($lowerMessage, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset connection state for retry
     */
    private function resetConnectionState(): void
    {
        $this->stompProducer = null;
        $this->stompConsumer = null;
        $this->lastFrame = null;

        // Close existing connection
        if ($this->client && $this->client->isConnected()) {
            try {
                $this->client->disconnect();
            } catch (\Exception $e) {
                $this->logger->debug('Error disconnecting client during reset: ' . $e->getMessage());
            }
        }
        $this->client = null;
    }

    /**
     * @inheritdoc
     *
     * @throws StompException
     */
    public function subscribeQueue(string $queue): void
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $this->ensureHealthyConnection();
                $this->stompConsumer = new StatefulStomp($this->client);
                $this->stompConsumer->subscribe($queue, null, self::ACK_TYPE);
                return; // Success, exit retry loop

            } catch (\Exception $e) {
                $retryCount++;
                $errorMessage = $e->getMessage();
                $isRetryableError = $this->isRetryableError($errorMessage) ||
                                   strpos($errorMessage, 'Missing receipt') !== false ||
                                   strpos($errorMessage, 'receipt Frame') !== false;

                $this->logger->warning(
                    "STOMP subscribe attempt {$retryCount}/{$maxRetries} failed for queue '{$queue}': {$errorMessage}",
                    [
                        'queue' => $queue,
                        'retryable' => $isRetryableError,
                        'exception' => $e
                    ]
                );

                if ($retryCount >= $maxRetries) {
                    $this->logger->error(
                        "Failed to subscribe to queue '{$queue}' after {$maxRetries} attempts: {$errorMessage}",
                        ['exception' => $e]
                    );
                    throw new StompException(
                        "Failed to subscribe to queue '{$queue}' after {$maxRetries} attempts: {$errorMessage}",
                        $e->getCode(),
                        $e
                    );
                }

                // Reset connection state for retry
                $this->resetConnectionState();

                if ($retryCount < $maxRetries) {
                    // Progressive backoff: 200ms, 400ms, 800ms
                    usleep(200000 * $retryCount);
                }
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @throws StompException
     */
    public function readMessage(): ?Frame
    {
        try {
            $this->ensureHealthyConnection();

            // Initialize stomp if not already done
            if (!$this->stompConsumer) {
                $this->stompConsumer = new StatefulStomp($this->client);
            }

            $this->lastFrame = $this->stompConsumer->read();
            if ($this->lastFrame) {
                return $this->lastFrame;
            }
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Failed to read STOMP message: %s', $e->getMessage()),
                ['exception' => $e,
                 'trace' => $e->getTraceAsString()
                ]
            );

            // For read operations, we might want to reset connection state
            // but not throw an exception immediately as null return is valid
            if ($this->isRetryableError($e->getMessage())) {
                $this->resetConnectionState();
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function ackMessage(Frame $lastFrame): void
    {
        try {
            $properties = $lastFrame->getHeaders();
            if (isset($properties['message-id'])) {
                $messageId = $properties['message-id'];
                if (!isset($this->acknowledged[$messageId])) {
                    $this->stompConsumer->ack($lastFrame);
                    $this->acknowledged[$properties['message-id']] = true;
                    $this->lastFrame = null;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Failed to acknowledge STOMP message: %s', $e->getMessage()),
                ['exception' => $e]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function nackMessage(Frame $lastFrame): void
    {
        $properties = $lastFrame->getHeaders();
        if (isset($properties['message-id'])) {
            $messageId = $properties['message-id'];
            if (!isset($this->acknowledged[$messageId])) {
                $this->stompConsumer->nack($lastFrame);
                $this->acknowledged[$properties['message-id']] = true;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function readFrame(): Frame
    {
        return  $this->client->readFrame();
    }

    /**
     * Clear queue using Artemis REST API (much faster than STOMP protocol)
     *
     * @param string $queueName
     * @return int Number of messages cleared
     */
    public function clearQueue(string $queueName): int
    {
        try {
            $host = $this->stompConfig->getValue(Config::HOST);
            $user = $this->stompConfig->getValue(Config::USERNAME);
            $password = $this->stompConfig->getValue(Config::PASSWORD);

            // First get message count
            $messageCount = $this->getQueueMessageCount($queueName, $host, $user, $password);

            if ($messageCount === 0) {
                $this->logger->info("Queue '{$queueName}' is already empty");
                return 0;
            }

            // Clear the queue using management API
            $body = [
                'type' => 'exec',
                'mbean' => 'org.apache.activemq.artemis:broker="0.0.0.0",component=addresses,address="' .
                    $queueName . '",subcomponent=queues,routing-type="anycast",queue="' . $queueName . '"',
                'operation' => 'removeAllMessages()',
                'arguments' => []
            ];

            $response = $this->executeJolokiaRequest($body, $host, $user, $password);

            if (isset($response['value'])) {
                $clearedCount = (int)$response['value'];
                $this->logger->info("Cleared {$clearedCount} messages from queue '{$queueName}' via REST API");
                return $clearedCount;
            }

            return 0;

        } catch (\Exception $e) {
            $this->logger->error("Failed to clear queue '{$queueName}' via REST API: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get message count for a queue using REST API
     *
     * @param string $queueName
     * @param string $host
     * @param string $user
     * @param string $password
     * @return int
     */
    private function getQueueMessageCount(string $queueName, string $host, string $user, string $password): int
    {
        $body = [
            'type' => 'read',
            'mbean' => 'org.apache.activemq.artemis:broker="0.0.0.0",component=addresses,address="' .
                $queueName . '",subcomponent=queues,routing-type="anycast",queue="' . $queueName . '"',
            'attribute' => 'MessageCount'
        ];

        $response = $this->executeJolokiaRequest($body, $host, $user, $password);

        if (isset($response['value'])) {
            return (int)$response['value'];
        }

        return 0;
    }

    /**
     * Execute Jolokia request to Artemis management interface
     *
     * @param array $body
     * @param string $host
     * @param string $user
     * @param string $password
     * @return array
     * @throws \Exception
     */
    private function executeJolokiaRequest(array $body, string $host, string $user, string $password): array
    {
        $url = "http://{$host}:8161/console/jolokia/";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($user . ':' . $password)
            ],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("CURL error: " . $error);
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException("HTTP error: " . $httpCode);
        }

        $decoded = json_decode($response, true);

        if (!$decoded) {
            throw new \RuntimeException("Invalid JSON response");
        }

        if (isset($decoded['status']) && $decoded['status'] !== 200) {
            throw new \RuntimeException("Jolokia error: " . ($decoded['error'] ?? 'Unknown error'));
        }

        return $decoded;
    }

    /**
     * Create connection with stomp
     *
     * @throws StompException
     */
    protected function connect(): void
    {
        try {
            if (!isset(self::$persistentclient[$this->clientId])) {
                $connection = $this->stompConfig->getConnection();
                $this->client = new Client($connection);
                $this->client->setVersions([self::VERSION]);
                $this->client->setLogin(
                    $this->stompConfig->getValue(Config::USERNAME),
                    $this->stompConfig->getValue(Config::PASSWORD)
                );

                // Read timeout configurations from env.php
                $heartbeatSend = $this->stompConfig->getValue('heartbeat_send') ?? self::HEARTBEAT_SEND_TIME;
                $heartbeatReceive = $this->stompConfig->getValue('heartbeat_receive') ?? self::HEARTBEAT_RECEIVE_TIME;
                $readTimeout = $this->stompConfig->getValue('read_timeout') ?? self::READ_TIME_OUT;

                $this->client->setHeartbeat($heartbeatSend, $heartbeatReceive);
                $connection->setReadTimeout(0, $readTimeout);

                $emitter = new HeartbeatEmitter($this->client->getConnection());
                $this->client->getConnection()->getObservers()->addObserver($emitter);
                $this->client->connect();
                self::$persistentclient[$this->clientId] = $this->client;
                $this->createStatefulStompInstance();
            } else {
                $this->client =self::$persistentclient[$this->clientId];
                $this->createStatefulStompInstance();
                if (!self::$persistentclient[$this->clientId]->isConnected()) {
                    $this->retryConnection();
                }
            }
        } catch (StompException $e) {
            // Retry connection with same timeout configurations
            $this->logger->warning('STOMP connection failed, retrying: ' . $e->getMessage());
            $this->retryConnection();
        }
    }

    /**
     * Retry connection with the help of Client class
     */
    protected function retryConnection(): void
    {
        if (!isset(self::$persistentclient[$this->clientId])) {
            $host = $this->stompConfig->getValue(Config::HOST);
            $port = $this->stompConfig->getValue(Config::PORT);
            $heartbeatSend = $this->stompConfig->getValue('heartbeat_send') ?? self::HEARTBEAT_SEND_TIME;
            $heartbeatReceive = $this->stompConfig->getValue('heartbeat_receive') ?? self::HEARTBEAT_RECEIVE_TIME;
            $readTimeout = $this->stompConfig->getValue('read_timeout') ?? self::READ_TIME_OUT;

            $this->client = new Client('tcp://' . $host . ':' . $port);
            $this->client->setVersions(['1.2']);
            $this->client->setLogin(
                $this->stompConfig->getValue(Config::USERNAME),
                $this->stompConfig->getValue(Config::PASSWORD)
            );
            $this->client->setHeartbeat($heartbeatSend, $heartbeatReceive);
            $this->client->getConnection()->setReadTimeout(0, $readTimeout);

            $emitter = new HeartbeatEmitter($this->client->getConnection());
            $this->client->getConnection()->getObservers()->addObserver($emitter);
            $this->client->connect();
            self::$persistentclient[$this->clientId] = $this->client;
            $this->createStatefulStompInstance();

        } else {
            $this->client =self::$persistentclient[$this->clientId];
            $this->createStatefulStompInstance();
        }
    }

    /**
     * Create stateful stomp instance
     *
     * @return void
     */
    private function createStatefulStompInstance(): void
    {
        if ($this->clientId === 'producer') {
            $this->stompProducer = new StatefulStomp($this->client);
        } elseif ($this->clientId === 'consumer') {
            $this->stompConsumer = new StatefulStomp($this->client);
        }
    }

    /**
     * Stomp transaction begins
     *
     * @return void
     */
    public function transactionBegin(): void
    {
        $this->stompProducer?->begin();
    }

    /**
     * Stomp transaction commit
     *
     * @return void
     */
    public function transactionCommit(): void
    {
        $this->stompProducer?->commit();
    }

    /**
     * Check stomp is connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->client->isConnected();
    }

    /**
     * Disconnect stomp
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->client->disconnect();
        self::$persistentclient = [];
    }
}
