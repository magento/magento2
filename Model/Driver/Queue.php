<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\Framework\MessageQueue\EnvelopeFactory;
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
     * @param string $queueName
     * @param int $interval
     * @param int $maxNumberOfTrials
     * @param LoggerInterface $logger
     */
    public function __construct(
        QueueManagement $queueManagement,
        EnvelopeFactory $envelopeFactory,
        $queueName,
        $interval = 5,
        $maxNumberOfTrials = 3,
        LoggerInterface $logger
    ) {
        $this->queueManagement = $queueManagement;
        $this->envelopeFactory = $envelopeFactory;
        $this->queueName = $queueName;
        $this->interval = $interval;
        $this->maxNumberOfTrials = $maxNumberOfTrials;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $envelope = null;
        $messages = $this->queueManagement->readMessages($this->queueName, 1);
        if (isset($messages[0])) {
            $properties = $messages[0];

            $body = $properties[QueueManagement::MESSAGE_BODY];
            unset($properties[QueueManagement::MESSAGE_BODY]);

            $envelope = $this->envelopeFactory->create(['body' => $body, 'properties' => $properties]);
        }

        return $envelope;
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        $this->queueManagement->changeStatus($relationId, QueueManagement::MESSAGE_STATUS_COMPLETE);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe($callback)
    {
        while (true) {
            while ($envelope = $this->dequeue()) {
                try {
                    call_user_func($callback, $envelope);
                    $this->acknowledge($envelope);
                } catch (\Exception $e) {
                    $this->reject($envelope);
                }
            }
            sleep($this->interval);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reject(EnvelopeInterface $envelope, $rejectionMessage = null)
    {
        $properties = $envelope->getProperties();
        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        if ($properties[QueueManagement::MESSAGE_NUMBER_OF_TRIALS] < $this->maxNumberOfTrials) {
            $this->queueManagement->pushToQueueForRetry($relationId);
        } else {
            $this->queueManagement->changeStatus([$relationId], QueueManagement::MESSAGE_STATUS_ERROR);
            if ($rejectionMessage !== null) {
                $this->logger->critical(__('Message has been rejected: %1', $rejectionMessage));
            }
        }
    }
}
