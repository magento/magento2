<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Amqp\Model;

use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\ConsumerInterface;
use Magento\Framework\Amqp\EnvelopeInterface;
use Magento\Framework\Amqp\QueueInterface;
use Magento\Framework\Amqp\QueueRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Amqp\MessageEncoder;
use Magento\Framework\Amqp\ConsumerConfigurationInterface;

/**
 * A RabbitMQ Consumer to handle receiving a message.
 * @codingStandardsIgnoreFile
 */
class Consumer implements ConsumerInterface
{
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
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * Initialize dependencies.
     *
     * @param AmqpConfig $amqpConfig
     * @param MessageEncoder $messageEncoder
     * @param QueueRepository $queueRepository
     * @param EnvelopeFactory $envelopeFactory
     */
    public function __construct(
        AmqpConfig $amqpConfig,
        MessageEncoder $messageEncoder,
        QueueRepository $queueRepository,
        EnvelopeFactory $envelopeFactory
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->messageEncoder = $messageEncoder;
        $this->queueRepository = $queueRepository;
        $this->envelopeFactory = $envelopeFactory;
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
        $consumerName = $this->configuration->getConsumerName();
        $connectionName = $this->amqpConfig->getConnectionByConsumer($consumerName);
        $queue = $this->queueRepository->get($connectionName, $queueName);

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
    public function dispatchMessage(EnvelopeInterface $message)
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $callback = $this->configuration->getCallback();

        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());

        if (isset($decodedMessage)) {
            call_user_func($callback, $decodedMessage);
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
            $this->dispatchMessage($message);
            $queue->acknowledge($message);
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
}
