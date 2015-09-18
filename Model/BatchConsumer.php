<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\Amqp\ConsumerConfigurationInterface;
use Magento\Framework\Amqp\ConsumerInterface;
use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\EnvelopeInterface;
use Magento\Framework\Amqp\MergerFactory;
use Magento\Framework\Amqp\MergerInterface;
use Magento\Framework\Amqp\MessageEncoder;
use Magento\Framework\Amqp\QueueInterface;
use Magento\Framework\Amqp\QueueRepository;
use Magento\Framework\Exception\LocalizedException;

class BatchConsumer implements ConsumerInterface
{
    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var MergerFactory
     */
    private $mergerFactory;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var int
     */
    private $interval;

    /**
     * @param AmqpConfig $amqpConfig
     * @param MessageEncoder $messageEncoder
     * @param QueueRepository $queueRepository
     * @param MergerFactory $mergerFactory
     * @param EnvelopeFactory $envelopeFactory
     * @param int $interval
     */
    public function __construct(
        AmqpConfig $amqpConfig,
        MessageEncoder $messageEncoder,
        QueueRepository $queueRepository,
        MergerFactory $mergerFactory,
        EnvelopeFactory $envelopeFactory,
        $interval = 5
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->messageEncoder = $messageEncoder;
        $this->queueRepository = $queueRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->mergerFactory = $mergerFactory;
        $this->interval = $interval;
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
        $merger = $this->mergerFactory->create($consumerName);

        if (!isset($maxNumberOfMessages)) {
            $this->runDaemonMode($queue, $merger);
        } else {
            $this->run($queue, $merger, $maxNumberOfMessages);
        }
    }

    /**
     * Decode message and invoke callback method
     *
     * @param object[] $messages
     * @return void
     * @throws LocalizedException
     */
    public function dispatchMessage($messages)
    {
        $callback = $this->configuration->getCallback();
        foreach ($messages as $message) {
            call_user_func($callback, $message);
        }
    }

    /**
     * Run process in the daemon mode
     *
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @return void
     */
    private function runDaemonMode($queue, $merger)
    {
        while (true) {
            $messages = $this->getAllMessages($queue);
            $this->processMessages($queue, $merger, $messages);

            sleep($this->interval);
        }
    }

    /**
     * Run short running process
     *
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @param int $maxNumberOfMessages
     * @return void
     */
    private function run($queue, $merger, $maxNumberOfMessages)
    {
        $count = $maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages() ?: 1;

        $messages = $this->getMessages($queue, $count);

        $this->processMessages($queue, $merger, $messages);
    }

    /**
     * @param QueueInterface $queue
     * @param EnvelopeInterface[] $messages
     * @return void
     */
    private function acknowledgeAll($queue, $messages)
    {
        foreach ($messages as $message) {
            $queue->acknowledge($message);
        }
    }

    /**
     * @param QueueInterface $queue
     * @return EnvelopeInterface[]
     */
    private function getAllMessages($queue)
    {
        $messages = [];
        while ($message = $queue->dequeue()) {
            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @param QueueInterface $queue
     * @param int $count
     * @return EnvelopeInterface[]
     */
    private function getMessages($queue, $count)
    {
        $messages = [];
        for ($i = $count; $i > 0; $i--) {
            $message = $queue->dequeue();
            if ($message === null) {
                break;
            }
            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @param QueueInterface $queue
     * @param EnvelopeInterface[] $messages
     * @return void
     */
    private function rejectAll($queue, $messages)
    {
        foreach ($messages as $message) {
            $queue->reject($message);
        }
    }

    /**
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @param EnvelopeInterface[] $messages
     * @return void
     */
    private function processMessages($queue, $merger, $messages)
    {
        if (!empty($messages)) {
            try {
                $decodedMessages = $this->decodeMessages($messages);
                $mergedMessages = $merger->merge($decodedMessages);
                $this->dispatchMessage($mergedMessages);
                $this->acknowledgeAll($queue, $messages);
            } catch (\Exception $e) {
                $this->rejectAll($queue, $messages);
            }
        }
    }

    /**
     * @param EnvelopeInterface[] $messages
     * @return object[]
     */
    private function decodeMessages(array $messages)
    {
        $decodedMessages = [];
        foreach ($messages as $message) {
            $properties = $message->getProperties();
            $topicName = $properties['topic_name'];

            $decodedMessages[] = $this->messageEncoder->decode($topicName, $message->getBody());
        }

        return $decodedMessages;
    }
}
