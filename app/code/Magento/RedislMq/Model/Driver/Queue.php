<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RedisMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\RedisMq\Model\QueueManagement;
use Psr\Log\LoggerInterface;

/**
 * Queue based on MessageQueue protocol
 */
class Queue implements QueueInterface
{
    /**
     * @var QueueManagement
     */
    private $queueManagement;

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
    private $interval;

    /**
     * @var int
     */
    private $maxNumberOfTrials;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Queue constructor.
     *
     * @param QueueManagement $queueManagement
     * @param EnvelopeFactory $envelopeFactory
     * @param LoggerInterface $logger
     * @param string $queueName
     * @param int $interval
     * @param int $maxNumberOfTrials
     */
    public function __construct(
        QueueManagement $queueManagement,
        EnvelopeFactory $envelopeFactory,
        LoggerInterface $logger,
        $queueName,
        $interval = 5,
        $maxNumberOfTrials = 3
    ) {
        $this->queueManagement = $queueManagement;
        $this->envelopeFactory = $envelopeFactory;
        $this->queueName = $queueName;
        $this->interval = $interval;
        $this->maxNumberOfTrials = $maxNumberOfTrials;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function dequeue()
    {
        $envelope = null;
        $envelopes = $this->queueManagement->readMessages($this->queueName, 1);
        if (isset($envelopes[0])) {
            $properties = $envelopes[0];

            $body = $properties[QueueManagement::MESSAGE_BODY];
            unset($properties[QueueManagement::MESSAGE_BODY]);

            $envelope = $this->envelopeFactory->create(['body' => $body, 'properties' => $properties]);
        }

        return $envelope;
    }

    /**
     * @inheritdoc
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $this->getRedis()->zrem($this->queueName . ':reserved', $envelope->getReservedKey());
    }

    /**
     * @inheritdoc
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null)
    {
//        $properties = $envelope->getProperties();
//        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];
//
//        if ($properties[QueueManagement::MESSAGE_NUMBER_OF_TRIALS] < $this->maxNumberOfTrials && $requeue) {
//            $this->queueManagement->pushToQueueForRetry($relationId);
//        } else {
//            $this->queueManagement->changeStatus([$relationId], QueueManagement::MESSAGE_STATUS_ERROR);
//            if ($rejectionMessage !== null) {
//                $this->logger->critical(__('Message has been rejected: %1', $rejectionMessage));
//            }
//        }

        $this->acknowledge($envelope);

        if ($requeue) {
            $envelope = $this->getContext()->getSerializer()->toMessage($envelope->getReservedKey());
            $envelope->setHeader('attempts', 0);

            if ($envelope->getTimeToLive()) {
                $envelope->setHeader('expires_at', time() + $envelope->getTimeToLive());
            }

            $payload = $this->getContext()->getSerializer()->toString($envelope);

            $this->getRedis()->lpush($this->queueName, $payload);
        }
    }

    /**
     * @inheritdoc
     */
    public function subscribe($callback)
    {
        while (true) {
            while ($envelope = $this->dequeue()) {
                try {
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    call_user_func($callback, $envelope);
                } catch (\Exception $e) {
                    $this->reject($envelope);
                }
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            sleep($this->interval);
        }
    }

    /**
     * @inheritDoc
     */
    public function push(EnvelopeInterface $envelope)
    {
//        $properties = $envelope->getProperties();
//        $this->queueManagement->addMessageToQueues(
//            $properties[QueueManagement::MESSAGE_TOPIC],
//            $envelope->getBody(),
//            [$this->queueName]
//        );

        $envelope->setMessageId(Uuid::uuid4()->toString());
        $envelope->setHeader('attempts', 0);

        if (null !== $this->timeToLive && null === $envelope->getTimeToLive()) {
            $envelope->setTimeToLive($this->timeToLive);
        }

        if (null !== $this->deliveryDelay && null === $envelope->getDeliveryDelay()) {
            $envelope->setDeliveryDelay($this->deliveryDelay);
        }

        if ($envelope->getTimeToLive()) {
            $envelope->setHeader('expires_at', time() + $envelope->getTimeToLive());
        }

        $payload = $this->context->getSerializer()->toString($envelope);

        if ($envelope->getDeliveryDelay()) {
            $deliveryAt = time() + $envelope->getDeliveryDelay() / 1000;
            $this->context->getRedis()->zadd($destination->getName() . ':delayed', $payload, $deliveryAt);
        } else {
            $this->context->getRedis()->lpush($destination->getName(), $payload);
        }
    }
}
