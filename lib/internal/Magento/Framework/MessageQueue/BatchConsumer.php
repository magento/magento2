<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;

/**
 * Class BatchConsumer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BatchConsumer implements ConsumerInterface
{
    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

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
     * @var int
     */
    private $interval;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var MessageProcessorLoader
     */
    private $messageProcessorLoader;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @param ConfigInterface $messageQueueConfig
     * @param MessageEncoder $messageEncoder
     * @param QueueRepository $queueRepository
     * @param MergerFactory $mergerFactory
     * @param ResourceConnection $resource
     * @param ConsumerConfigurationInterface $configuration
     * @param int $interval [optional]
     * @param int $batchSize [optional]
     * @param MessageProcessorLoader $messageProcessorLoader [optional]
     * @param MessageController $messageController [optional]
     * @param ConsumerConfig $consumerConfig [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        MessageQueueConfig $messageQueueConfig,
        MessageEncoder $messageEncoder,
        QueueRepository $queueRepository,
        MergerFactory $mergerFactory,
        ResourceConnection $resource,
        ConsumerConfigurationInterface $configuration,
        $interval = 5,
        $batchSize = 0,
        MessageProcessorLoader $messageProcessorLoader = null
    ) {
        $this->messageEncoder = $messageEncoder;
        $this->queueRepository = $queueRepository;
        $this->mergerFactory = $mergerFactory;
        $this->interval = $interval;
        $this->batchSize = $batchSize;
        $this->resource = $resource;
        $this->configuration = $configuration;
        $this->messageProcessorLoader = $messageProcessorLoader
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(MessageProcessorLoader::class);
    }

    /**
     * {@inheritdoc}
     */
    public function process($maxNumberOfMessages = null)
    {
        $queueName = $this->configuration->getQueueName();
        $consumerName = $this->configuration->getConsumerName();
        $connectionName = $this->getConsumerConfig()->getConsumer($consumerName)->getConnection();

        $queue = $this->queueRepository->get($connectionName, $queueName);
        $merger = $this->mergerFactory->create($consumerName);

        if (!isset($maxNumberOfMessages)) {
            $this->runDaemonMode($queue, $merger);
        } else {
            $this->run($queue, $merger, $maxNumberOfMessages);
        }
    }

    /**
     * Run process in a daemon mode.
     *
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @return void
     */
    private function runDaemonMode(QueueInterface $queue, MergerInterface $merger)
    {
        $transactionCallback = $this->getTransactionCallback($queue, $merger);

        while (true) {
            $messages = $this->batchSize > 0
                ? $this->getMessages($queue, $this->batchSize)
                : $this->getAllMessages($queue);
            $transactionCallback($messages);
            sleep($this->interval);
        }
    }

    /**
     * Run short running process.
     *
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @param int $maxNumberOfMessages
     * @return void
     */
    private function run(QueueInterface $queue, MergerInterface $merger, $maxNumberOfMessages)
    {
        $count = ($maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages()) ?: 1;
        $transactionCallback = $this->getTransactionCallback($queue, $merger);

        if ($this->batchSize) {
            while ($count > 0) {
                $messages = $this->getMessages($queue, $count > $this->batchSize ? $this->batchSize : $count);
                $transactionCallback($messages);
                $count -= $this->batchSize;
            }
        } else {
            $messages = $this->getMessages($queue, $count);
            $transactionCallback($messages);
        }
    }

    /**
     * Get all messages from a queue.
     *
     * @param QueueInterface $queue
     * @return EnvelopeInterface[]
     */
    private function getAllMessages(QueueInterface $queue)
    {
        $messages = [];
        while ($message = $queue->dequeue()) {
            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Get $count messages from a queue.
     *
     * @param QueueInterface $queue
     * @param int $count
     * @return EnvelopeInterface[]
     */
    private function getMessages(QueueInterface $queue, $count)
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
     * Decode provided messages.
     *
     * @param EnvelopeInterface[] $messages
     * @return object[]
     */
    private function decodeMessages(array $messages)
    {
        $decodedMessages = [];
        foreach ($messages as $messageId => $message) {
            $properties = $message->getProperties();
            $topicName = $properties['topic_name'];
            $decodedMessages[$topicName][$messageId] = $this->messageEncoder->decode($topicName, $message->getBody());
        }

        return $decodedMessages;
    }

    /**
     * Get transaction callback.
     *
     * @param QueueInterface $queue
     * @param MergerInterface $merger
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue, MergerInterface $merger)
    {
        return function (array $messages) use ($queue, $merger) {
            list($messages, $messagesToAcknowledge) = $this->lockMessages($messages);
            $decodedMessages = $this->decodeMessages($messages);
            $mergedMessages = $merger->merge($decodedMessages);
            $messageProcessor = $this->messageProcessorLoader->load($mergedMessages);
            $messageProcessor->process(
                $queue,
                $this->configuration,
                $messages,
                $messagesToAcknowledge,
                $mergedMessages
            );
        };
    }

    /**
     * Create lock for the messages.
     *
     * @param array $messages
     * @return array
     */
    private function lockMessages(array $messages)
    {
        $toProcess = [];
        $toAcknowledge = [];
        foreach ($messages as $message) {
            try {
                $this->getMessageController()->lock($message, $this->configuration->getConsumerName());
                $toProcess[] = $message;
            } catch (MessageLockException $exception) {
                $toAcknowledge[] = $message;
            }
        }
        return [$toProcess, $toAcknowledge];
    }

    /**
     * Get consumer config.
     *
     * This getter serves as a workaround to add this dependency to this class without breaking constructor structure
     *
     * @return ConsumerConfig
     *
     * @deprecated 103.0.0
     */
    private function getConsumerConfig()
    {
        if ($this->consumerConfig === null) {
            $this->consumerConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ConsumerConfig::class);
        }
        return $this->consumerConfig;
    }

    /**
     * Get message controller.
     *
     * This getter serves as a workaround to add this dependency to this class without breaking constructor structure
     *
     * @return MessageController
     *
     * @deprecated 103.0.0
     */
    private function getMessageController()
    {
        if ($this->messageController === null) {
            $this->messageController = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\MessageQueue\MessageController::class);
        }
        return $this->messageController;
    }
}
