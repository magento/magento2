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
use Magento\Framework\Amqp\QueueRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Amqp\MessageEncoder;
use Magento\Framework\Amqp\ConsumerConfigurationInterface;
use Magento\RabbitMq\Model\Config;

/**
 * A RabbitMQ Consumer to handle receiving a message.
 * @codingStandardsIgnoreFile
 */
class Consumer implements ConsumerInterface
{
    const CONTENT_TYPE_JSON = 'application/json';

    /**
     * @var Config
     */
    private $rabbitMqConfig;

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
     * @param Config $rabbitMqConfig
     * @param AmqpConfig $amqpConfig
     * @param MessageEncoder $messageEncoder
     * @param QueueRepository $queueRepository
     * @param EnvelopeFactory $envelopeFactory
     */
    public function __construct(
        Config $rabbitMqConfig,
        AmqpConfig $amqpConfig,
        MessageEncoder $messageEncoder,
        QueueRepository $queueRepository,
        EnvelopeFactory $envelopeFactory
    ) {
        $this->rabbitMqConfig = $rabbitMqConfig;
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

        if (!isset($maxNumberOfMessages)) {
            $this->runDaemonMode($queueName);
        } else {
            $this->run($queueName, $maxNumberOfMessages);
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
        $decodedMessage = null;

        if (isset($properties['content_type'])) {
            $contentType = $properties['content_type'];
            switch ($contentType) {
                case self::CONTENT_TYPE_JSON:
                    $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());
                    break;
            }
        } else {
            $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());
        }

        if (isset($decodedMessage)) {
            call_user_func($callback, $decodedMessage);
        }
    }

    /**
     * Run short running process
     *
     * @param string $queueName
     * @param int $maxNumberOfMessages
     * @return void
     */
    private function run($queueName, $maxNumberOfMessages)
    {
        $count = $maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages() ?: 1;

        /** @var Queue $queue */
        $queue = $this->queueRepository->getByQueueName($queueName);
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
     * @param string $queueName
     * @return void
     */
    private function runDaemonMode($queueName)
    {
        $callback = [$this, 'dispatchMessage'];

        /** @var Queue $queue */
        $queue = $this->queueRepository->getByQueueName($queueName);
        $queue->subscribe($callback);
    }
}
