<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

use Magento\Framework\Amqp\EnvelopeInterface;
use Magento\Framework\Amqp\QueueInterface;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\Amqp\Model\EnvelopeFactory;

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

    public function __construct(
        QueueManagement $queueManagement,
        EnvelopeFactory $envelopeFactory,
        $queueName,
        $interval = 5,
        $maxNumberOfTrials = 3
    ) {
        $this->queueManagement = $queueManagement;
        $this->envelopeFactory = $envelopeFactory;
        $this->queueName = $queueName;
        $this->interval = $interval;
        $this->maxNumberOfTrials = $maxNumberOfTrials;
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
                try{
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
    public function reject(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $relationId = $properties[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        if ($properties[QueueManagement::MESSAGE_NUMBER_OF_TRIALS] < $this->maxNumberOfTrials) {
            $this->queueManagement->pushToQueueForRetry($relationId);
        } else {
            $this->queueManagement->changeStatus([$relationId], QueueManagement::MESSAGE_STATUS_ERROR);
        }
    }
}
