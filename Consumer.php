<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\Config\Data as MessageQueueConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\Config\Converter as MessageQueueConfigConverter;

/**
 * A MessageQueue Consumer to handle receiving a message.
 */
class Consumer implements ConsumerInterface
{
    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * Initialize dependencies.
     *
     * @param MessageQueueConfig $messageQueueConfig
     * @param MessageEncoder $messageEncoder
     * @param QueueRepository $queueRepository
     */
    public function __construct(
        MessageQueueConfig $messageQueueConfig,
        MessageEncoder $messageEncoder,
        QueueRepository $queueRepository
    ) {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->messageEncoder = $messageEncoder;
        $this->queueRepository = $queueRepository;
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
        $queue = $this->getQueue();

        if (!isset($maxNumberOfMessages)) {
            $this->runDaemonMode($queue);
        } else {
            $this->run($queue, $maxNumberOfMessages);
        }
    }

    /**
     * Decode message and invoke callback method
     *
     * @param EnvelopeInterface $message
     * @return void
     * @throws LocalizedException
     */
    private function dispatchMessage(EnvelopeInterface $message)
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $callback = $this->configuration->getCallback();

        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());

        if (isset($decodedMessage)) {
            if ($this->messageQueueConfig->getMessageSchemaType($topicName) == MessageQueueConfigConverter::TOPIC_SCHEMA_TYPE_METHOD) {
                call_user_func_array($callback, $decodedMessage);
            } else {
                call_user_func($callback, $decodedMessage);
            }
        }
    }

    /**
     * Run short running process
     *
     * @param QueueInterface $queue
     * @param int $maxNumberOfMessages
     * @return void
     */
    private function run($queue, $maxNumberOfMessages)
    {
        $count = $maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages() ?: 1;

        for ($i = $count; $i > 0; $i--) {
            $message = $queue->dequeue();
            if ($message === null) {
                break;
            }
            try{
                $this->dispatchMessage($message);
                $queue->acknowledge($message);
            } catch (\Exception $e) {
                $queue->reject($message);
            }
        }
    }

    /**
     * Run process in the daemon mode
     *
     * @param QueueInterface $queue
     * @return void
     */
    private function runDaemonMode($queue)
    {
        $callback = [$this, 'dispatchMessage'];

        $queue->subscribe($callback);
    }

    /**
     * @return QueueInterface
     * @throws LocalizedException
     */
    private function getQueue()
    {
        $queueName = $this->configuration->getQueueName();
        $consumerName = $this->configuration->getConsumerName();
        $connectionName = $this->messageQueueConfig->getConnectionByConsumer($consumerName);
        $queue = $this->queueRepository->get($connectionName, $queueName);

        return $queue;
    }
}
