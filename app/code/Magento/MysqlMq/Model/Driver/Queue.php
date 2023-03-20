<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\CountableQueueInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\MysqlMq\Model\ResourceModel\Queue as QueueResourceModel;
use Psr\Log\LoggerInterface;

/**
 * Queue based on MessageQueue protocol
 */
class Queue implements CountableQueueInterface
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
     * @var QueueResourceModel
     */
    private $queueResourceModel;

    /**
     * Queue constructor.
     *
     * @param QueueManagement $queueManagement
     * @param EnvelopeFactory $envelopeFactory
     * @param LoggerInterface $logger
     * @param string $queueName
     * @param int $interval
     * @param int $maxNumberOfTrials
     * @param QueueResourceModel|null $queueResourceModel
     */
    public function __construct(
        QueueManagement $queueManagement,
        EnvelopeFactory $envelopeFactory,
        LoggerInterface $logger,
        $queueName,
        $interval = 5,
        $maxNumberOfTrials = 3,
        ?QueueResourceModel $queueResourceModel = null
    ) {
        $this->queueManagement = $queueManagement;
        $this->envelopeFactory = $envelopeFactory;
        $this->queueName = $queueName;
        $this->interval = $interval;
        $this->maxNumberOfTrials = $maxNumberOfTrials;
        $this->logger = $logger;
        $this->queueResourceModel = $queueResourceModel
            ?? ObjectManager::getInstance()->get(QueueResourceModel::class);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        $this->queueManagement->changeStatus($relationId, QueueManagement::MESSAGE_STATUS_COMPLETE);
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
     * @inheritdoc
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null)
    {
        $properties = $envelope->getProperties();
        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        if ($properties[QueueManagement::MESSAGE_NUMBER_OF_TRIALS] < $this->maxNumberOfTrials && $requeue) {
            $this->queueManagement->pushToQueueForRetry($relationId);
        } else {
            $this->queueManagement->changeStatus([$relationId], QueueManagement::MESSAGE_STATUS_ERROR);
            if ($rejectionMessage !== null) {
                $this->logger->critical(__('Message has been rejected: %1', $rejectionMessage));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function push(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $this->queueManagement->addMessageToQueues(
            $properties[QueueManagement::MESSAGE_TOPIC],
            $envelope->getBody(),
            [$this->queueName]
        );
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->queueResourceModel->getMessagesCount($this->queueName);
    }
}
