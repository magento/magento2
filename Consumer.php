<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class Consumer used to process a single message, unlike batch consumer.
 *
 * This could be used for both synchronous and asynchronous processing, depending on topic.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Consumer implements ConsumerInterface
{
    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var CallbackInvoker
     */
    private $invoker;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * @var \Magento\Framework\MessageQueue\QueueRepository
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\MessageQueue\ConfigInterface
     */
    private $queueConfig;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * Initialize dependencies.
     *
     * @param CallbackInvoker $invoker
     * @param MessageEncoder $messageEncoder
     * @param ResourceConnection $resource
     * @param ConsumerConfigurationInterface $configuration
     * @param \Magento\Framework\MessageQueue\QueueRepository $queueRepository
     * @param ConsumerConfigurationInterface $configuration
     * @param MessageController $messageController
     * @param MessageValidator $messageValidator
     * @param EnvelopeFactory $envelopeFactory
     */
    public function __construct(
        CallbackInvoker $invoker,
        MessageEncoder $messageEncoder,
        ResourceConnection $resource,
        ConsumerConfigurationInterface $configuration,
        \Magento\Framework\MessageQueue\QueueRepository $queueRepository,
        \Magento\Framework\MessageQueue\ConfigInterface $queueConfig,
        MessageController $messageController,
        EnvelopeFactory $envelopeFactory,
        MessageValidator $messageValidator
    ) {
        $this->invoker = $invoker;
        $this->messageEncoder = $messageEncoder;
        $this->resource = $resource;
        $this->configuration = $configuration;
        $this->queueRepository = $queueRepository;
        $this->queueConfig = $queueConfig;
        $this->messageController = $messageController;
        $this->messageValidator = $messageValidator;
        $this->envelopeFactory = $envelopeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process($maxNumberOfMessages = null)
    {
        $queue = $this->configuration->getQueue();

        if (!isset($maxNumberOfMessages)) {
            $queue->subscribe($this->getTransactionCallback($queue));
        } else {
            $this->invoker->invoke($queue, $maxNumberOfMessages, $this->getTransactionCallback($queue));
        }
    }

    /**
     * Decode message and invoke callback method, return reply back for sync processing.
     *
     * @param EnvelopeInterface $message
     * @param boolean $isSync
     * @return string|null
     * @throws LocalizedException
     */
    private function dispatchMessage(EnvelopeInterface $message, $isSync = false)
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $handlers = $this->configuration->getHandlers($topicName);
        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());

        if (isset($decodedMessage)) {
            $messageSchemaType = $this->configuration->getMessageSchemaType($topicName);
            if ($messageSchemaType == QueueConfig::TOPIC_SCHEMA_TYPE_METHOD) {
                foreach ($handlers as $callback) {
                    $result = call_user_func_array($callback, $decodedMessage);
                    return $this->processSyncResponse($topicName, $result);
                }
            } else {
                foreach ($handlers as $callback) {
                    $result = call_user_func($callback, $decodedMessage);
                    if ($isSync) {
                        return $this->processSyncResponse($topicName, $result);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Validate and encode synchrouns handler output.
     *
     * @param string $topicName
     * @param mixed $result
     * @return string
     * @throws LocalizedException
     */
    private function processSyncResponse($topicName, $result)
    {
        if (isset($result)) {
            $this->messageValidator->validate($topicName, $result, false);
            return $this->messageEncoder->encode($topicName, $result, false);
        } else {
            throw new LocalizedException(new Phrase('No reply message resulted in RPC.'));
        }
    }

    /**
     * Send RPC response message.
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    private function sendResponse(EnvelopeInterface $envelope)
    {
        $messageProperties = $envelope->getProperties();
        $connectionName = $this->queueConfig->getConnectionByTopic($messageProperties['topic_name']);
        $queue = $this->queueRepository->get($connectionName, $messageProperties['reply_to']);
        $queue->push($envelope);
    }

    /**
     * Get transaction callback. This handles the case of both sync and async.
     *
     * @param QueueInterface $queue
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {
            try {
                $this->resource->getConnection()->beginTransaction();
                $topicName = $message->getProperties()['topic_name'];
                $consumerType = $this->configuration->getConsumerType($topicName);
                $this->messageController->lock($message, $this->configuration->getConsumerName());

                if ($consumerType == 'sync') {
                    $responseBody = $this->dispatchMessage($message, true);
                    $responseMessage = $this->envelopeFactory->create(
                        ['body' => $responseBody, 'properties' => $message->getProperties()]
                    );
                    $this->sendResponse($responseMessage);
                } else {
                    $allowedTopics = $this->configuration->getTopicNames();
                    if (in_array($topicName, $allowedTopics)) {
                        $this->dispatchMessage($message);
                    } else {
                        $queue->reject($message);
                        return;
                    }
                }
                $this->resource->getConnection()->commit();
                $queue->acknowledge($message);
            } catch (MessageLockException $exception) {
                $this->resource->getConnection()->rollBack();
                $queue->acknowledge($message);
            } catch (\Magento\Framework\MessageQueue\ConnectionLostException $e) {
                $this->resource->getConnection()->rollBack();
            } catch (\Exception $e) {
                $this->resource->getConnection()->rollBack();
                $queue->reject($message, false, $e->getMessage());
            }
        };
    }
}
