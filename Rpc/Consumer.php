<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\CallbackInvoker;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageLockException;

/**
 * A MessageQueue Consumer to handle receiving, processing and replying to an RPC message.
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
     * @var MessageController
     */
    private $messageController;

    /**
     * Initialize dependencies.
     *
     * @param CallbackInvoker $invoker
     * @param MessageEncoder $messageEncoder
     * @param ResourceConnection $resource
     * @param ConsumerConfigurationInterface $configuration
     * @param \Magento\Framework\MessageQueue\QueueRepository $queueRepository
     * @param QueueConfig $queueConfig
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageValidator $messageValidator
     * @param MessageController $messageController
     */
    public function __construct(
        CallbackInvoker $invoker,
        MessageEncoder $messageEncoder,
        ResourceConnection $resource,
        ConsumerConfigurationInterface $configuration,
        \Magento\Framework\MessageQueue\QueueRepository $queueRepository,
        \Magento\Framework\MessageQueue\ConfigInterface $queueConfig,
        EnvelopeFactory $envelopeFactory,
        MessageValidator $messageValidator,
        MessageController $messageController
    ) {
        $this->invoker = $invoker;
        $this->messageEncoder = $messageEncoder;
        $this->resource = $resource;
        $this->configuration = $configuration;
        $this->queueRepository = $queueRepository;
        $this->queueConfig = $queueConfig;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageValidator = $messageValidator;
        $this->messageController = $messageController;
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
     * Decode message and invoke callback method
     *
     * @param EnvelopeInterface $message
     * @return string
     * @throws LocalizedException
     */
    private function dispatchMessage(EnvelopeInterface $message)
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
                    if (isset($result)) {
                        $this->messageValidator->validate($topicName, $result, false);
                        return $this->messageEncoder->encode($topicName, $result, false);
                    } else {
                        throw new LocalizedException(new Phrase('No reply message resulted in RPC.'));
                    }
                }
            } else {
                foreach ($handlers as $callback) {
                    $result = call_user_func($callback, $decodedMessage);
                    if (isset($result)) {
                        $this->messageValidator->validate($topicName, $result, false);
                        return $this->messageEncoder->encode($topicName, $result, false);
                    } else {
                        throw new LocalizedException(new Phrase('No reply message resulted in RPC.'));
                    }
                }
            }
        }
        return null;
    }

    /**
     * Send RPC response message
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
     * Get callback which can be used to process messages within a transaction.
     *
     * @param QueueInterface $queue
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {
            try {
                $this->resource->getConnection()->beginTransaction();
                $this->messageController->lock($message, $this->configuration->getConsumerName());
                $responseBody = $this->dispatchMessage($message);
                $responseMessage = $this->envelopeFactory->create(
                    ['body' => $responseBody, 'properties' => $message->getProperties()]
                );
                $this->sendResponse($responseMessage);
                $this->resource->getConnection()->commit();
                $queue->acknowledge($message);
            } catch (MessageLockException $exception) {
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
