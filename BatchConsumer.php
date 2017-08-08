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
 * @since 2.0.0
 */
class BatchConsumer implements ConsumerInterface
{
    /**
     * @var ConsumerConfigurationInterface
     * @since 2.0.0
     */
    private $configuration;

    /**
     * @var MessageEncoder
     * @since 2.0.0
     */
    private $messageEncoder;

    /**
     * @var QueueRepository
     * @since 2.0.0
     */
    private $queueRepository;

    /**
     * @var MergerFactory
     * @since 2.0.0
     */
    private $mergerFactory;

    /**
     * @var int
     * @since 2.0.0
     */
    private $interval;

    /**
     * @var int
     * @since 2.2.0
     */
    private $batchSize;

    /**
     * @var array
     */
    private $messageProcessors;

    /**
     * @var Resource
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var MessageController
     * @since 2.1.0
     */
    private $messageController;

    /**
     * @var ConsumerConfig
     * @since 2.2.0
     */
    private $consumerConfig;

    /**
     * @var string
     */
    private $mergedMessageProcessorKey = 'merged';

    /**
     * @var string
     */
    private $defaultMessageProcessorKey = 'default';

    /**
     * This getter serves as a workaround to add this dependency to this class without breaking constructor structure.
     *
     * @return MessageController
     *
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getMessageController()
    {
        if ($this->messageController === null) {
            $this->messageController = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\MessageQueue\MessageController::class);
        }
        return $this->messageController;
    }

    /**
     * @param ConfigInterface $messageQueueConfig
     * @param MessageEncoder $messageEncoder
     * @param QueueRepository $queueRepository
     * @param MergerFactory $mergerFactory
     * @param ResourceConnection $resource
     * @param ConsumerConfigurationInterface $configuration
     * @param int $interval [optional]
     * @param int $batchSize [optional]
     * @param array $messageProcessors [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
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
        array $messageProcessors = []
    ) {
        $this->messageEncoder = $messageEncoder;
        $this->queueRepository = $queueRepository;
        $this->mergerFactory = $mergerFactory;
        $this->interval = $interval;
        $this->batchSize = $batchSize;
        $this->resource = $resource;
        $this->configuration = $configuration;
        $this->messageProcessors = $messageProcessors;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function run(QueueInterface $queue, MergerInterface $merger, $maxNumberOfMessages)
    {
        $count = $maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages() ?: 1;
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function getTransactionCallback(QueueInterface $queue, MergerInterface $merger)
    {
        return function (array $messages) use ($queue, $merger) {
            list($messages, $messagesToAcknowledge) = $this->lockMessages($messages);
            $decodedMessages = $this->decodeMessages($messages);
            $mergedMessages = $merger->merge($decodedMessages);

            /**
             * @var \Magento\Framework\MessageQueue\MessageProcessorInterface $messageProcessor
             */
            if ($this->getMergedMessage($mergedMessages) instanceof MergedMessageInterface) {
                $messageProcessor = $this->messageProcessors[$this->mergedMessageProcessorKey];
            } else {
                $messageProcessor = $this->messageProcessors[$this->defaultMessageProcessorKey];
            }

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
     * @since 2.1.0
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
     * Get first merged message from the list of merged messages.
     *
     * @param array $mergedMessages
     * @return object|null
     */
    private function getMergedMessage(array $mergedMessages)
    {
        $mergedMessage = null;

        if ($mergedMessages) {
            $topicMessages = array_shift($mergedMessages);

            if ($topicMessages) {
                $mergedMessage = array_shift($topicMessages);
            }
        }

        return $mergedMessage;
    }

    /**
     * Get consumer config.
     *
     * @return ConsumerConfig
     *
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getConsumerConfig()
    {
        if ($this->consumerConfig === null) {
            $this->consumerConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ConsumerConfig::class);
        }
        return $this->consumerConfig;
    }
}
