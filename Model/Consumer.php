<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\ConsumerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Amqp\MessageEncoder;
use Magento\Framework\Amqp\ConsumerConfigurationInterface;
use Magento\MysqlMq\Model\QueueManagement;

/**
 * Consumer for MySQL based queue.
 */
class Consumer implements ConsumerInterface
{
    /**
     * Maximum number of trials to process message in case of exceptions during processing.
     */
    const MAX_NUMBER_OF_TRIALS = 3;

    /**
     * @var QueueManagement
     */
    private $queueManagement;

    /**
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * Initialize dependencies.
     *
     * @param QueueManagement $queueManagement
     * @param AmqpConfig $amqpConfig
     * @param MessageEncoder $messageEncoder
     */
    public function __construct(
        QueueManagement $queueManagement,
        AmqpConfig $amqpConfig,
        MessageEncoder $messageEncoder
    ) {
        $this->queueManagement = $queueManagement;
        $this->amqpConfig = $amqpConfig;
        $this->messageEncoder = $messageEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ConsumerConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function process($maxNumberOfMessages = null)
    {
        $queueName = $this->configuration->getQueueName();
        $maxNumberOfMessages = $maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages() ?: null;
        $this->run($queueName, $maxNumberOfMessages);
    }

    /**
     * Run short running process
     *
     * @param string $queueName
     * @param int|null $maxNumberOfMessages
     * @return void
     */
    private function run($queueName, $maxNumberOfMessages)
    {
        $messages = $this->queueManagement->readMessages($queueName, $maxNumberOfMessages);
        $successfullyProcessedIds = [];
        foreach ($messages as $message) {
            if ($this->dispatchMessage($message)) {
                $successfullyProcessedIds[] = $message[QueueManagement::MESSAGE_QUEUE_RELATION_ID];
            }
        }
        $this->queueManagement->changeStatus($successfullyProcessedIds, QueueManagement::MESSAGE_STATUS_COMPLETE);
    }

    /**
     * Decode message and invoke callback method
     *
     * @param array $message
     * @return bool true on successful processing
     */
    private function dispatchMessage($message)
    {
        $callback = $this->configuration->getCallback();
        $relationId = $message[QueueManagement::MESSAGE_QUEUE_RELATION_ID];
        try {
            $decodedMessage = $this->messageEncoder->decode(
                $message[QueueManagement::MESSAGE_TOPIC],
                $message[QueueManagement::MESSAGE_BODY]
            );
            if (isset($decodedMessage)) {
                call_user_func($callback, $decodedMessage);
            }
            return true;
        } catch (\Exception $e) {
            if ($message[QueueManagement::MESSAGE_NUMBER_OF_TRIALS] < self::MAX_NUMBER_OF_TRIALS) {
                $this->queueManagement->pushToQueueForRetry($relationId);
            } else {
                $this->queueManagement->changeStatus([$relationId], QueueManagement::MESSAGE_STATUS_ERROR);
            }
            return false;
        }
    }
}
