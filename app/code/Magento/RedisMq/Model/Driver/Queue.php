<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RedisMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Psr\Log\LoggerInterface;

/**
 * Queue based on MessageQueue protocol
 */
class Queue implements QueueInterface
{
    /**
     * MILLISECONDS in one SECOND
     */
    private const MILLISECONDS = 1000;

    /**
     * Default group name
     *
     */
    private const GROUP_NAME = 'magento';

    /**
     * message id
     */
    private const FIELD_ID = "#id";

    /**
     * message payload
     */
    private const FIELD_PAYLOAD = "#p";


    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var int
     */
    private $interval = 200; //ms

    private $visibilityWindow = 2; //s

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var ExtClient
     */
    private $connection;

    /**
     * @var string
     */
    private $groupName;

    /**
     * @var null
     */
    private $group = null;

    /**
     * Queue constructor.
     * @param string $queueName
     * @param ExtClient $redisClient
     * @param EnvelopeFactory $envelopeFactory
     * @param LoggerInterface $logger
     * @param string $groupName
     */
    public function __construct(
        string $queueName,
        ExtClient $redisClient,
        EnvelopeFactory $envelopeFactory,
        LoggerInterface $logger,
        string $groupName = self::GROUP_NAME
    ) {
        $this->connection = $redisClient;
        $this->envelopeFactory = $envelopeFactory;
        $this->queueName = $queueName;
        $this->logger = $logger;
        $this->groupName = $groupName;
    }

    private function getGroup()
    {
        if ($this->group === null) {
            try {
                $this->connection->xGroup('CREATE', $this->queueName, $this->groupName, '0', true);
            } catch (\RedisException $e) {
                throw new TransportException($e->getMessage(), 0, $e);
            }

            // group might already exist, ignore
            if ($this->connection->getLastError()) {
                $this->connection->clearLastError();
            }
            $this->group = $this->groupName;
        }
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function dequeue()
    {
        if ($this->connection->xLen($this->queueName) === 0) {
            return null;
        }
        $consumerName = $this->getConsumerName();
        $messages = $this->connection->xReadGroup(
            $this->getGroup(),
            $this->getConsumerName(),
            [$this->queueName => '>'],
            1
        );

        if (isset($messages[$this->queueName])) {
            foreach ($messages[$this->queueName] as $id => $message) {
                $body = $message[self::FIELD_PAYLOAD];
                unset($message[self::FIELD_PAYLOAD]);
                $message[self::FIELD_ID] = $id;

                return $this->envelopeFactory->create(['body' => $body, 'properties' => $message]);
            }
        } else {
            // try reprocess pending element
            if ($this->connection->xLen($this->queueName) > 0) {
                $pendingMessages = $this->connection->xPending(
                    $this->queueName,
                    $this->getGroup(),
                    '-',
                    '+', //(time() - $this->visibilityWindow) * 1000, // in ms
                    1
                );


                $claimableIds = [];
                foreach ($pendingMessages as $pendingMessage) {
                    list($id, $pendingConsumer, $idleTimeout, $countCount) = $pendingMessage;

                    if ($idleTimeout > $this->visibilityWindow) {
                        $claimableIds[] = $id;
                    }
                }

                if (\count($claimableIds) > 0) {
                    $messages = $this->connection->xclaim(
                        $this->queueName,
                        $this->getGroup(),
                        $consumerName,
                        $this->visibilityWindow * self::MILLISECONDS,
                        $claimableIds
                    );

                    if (!empty($messages)) {
                        foreach ($messages as $id => $message) {
                            $body = $message[self::FIELD_PAYLOAD];
                            unset($message[self::FIELD_PAYLOAD]);
                            $message[self::FIELD_ID] = $id;

                            return $this->envelopeFactory->create(['body' => $body, 'properties' => $message]);
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $id = $envelope->getProperties()[self::FIELD_ID];
        try {
            $acknowledged = $this->connection->xAck($this->queueName, $this->getGroup(), [$id]);
            $acknowledged &= $this->connection->xDel($this->queueName, [$id]);
        } catch (\RedisException $e) {
            throw new TransportException($e->getMessage(), 0, $e);
        }

        if (!$acknowledged) {
            if ($error = $this->connection->getLastError() ?: null) {
                $this->connection->clearLastError();
            }
            throw new TransportException($error ?? sprintf('Could not acknowledge redis message "%s".', $id));
        }
    }

    /**
     * @inheritdoc
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null)
    {
        $id = $envelope->getProperties()[self::FIELD_ID];
        if ($requeue) {

        } else {
            $this->connection->xDel($this->queueName, [$id]);
            $this->connection->xAck($this->queueName, $this->getGroup(), [$id]);
        }
        // todo verify xclaim for reject
    }

    /**
     * @inheritdoc
     */
    public function subscribe($callback)
    {
        while (true) {
            while ($envelope = $this->dequeue()) {
                try {
                    $callback($envelope);
                } catch (\Exception $e) {
                    $this->reject($envelope);
                }
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            usleep($this->interval * 1000000);
        }
    }

    /**
     * @inheritDoc
     */
    public function push(EnvelopeInterface $envelope)
    {
        $message = array_replace($envelope->getProperties(), [
            self::FIELD_PAYLOAD => $envelope->getBody(),
        ]);

        return $this->connection->xAdd($this->queueName, '*', $message);
    }

    /**
     * @return string
     */
    private function getConsumerName(): string
    {
        //@todo use $this->connection->rawCommand('CLIENT' 'ID')
        return \gethostname() . ':' . getmypid();
    }
}
