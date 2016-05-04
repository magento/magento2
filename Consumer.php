<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

/**
 * A MessageQueue Consumer to handle receiving a message.
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
     * This getter serves as a workaround to add this dependency to this class without breaking constructor structure.
     *
     * @return MessageController
     *
     * @deprecated
     */
    private function getMessageController()
    {
        if ($this->messageController === null) {
            $this->messageController = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\MessageQueue\MessageController');
        }
        return $this->messageController;
    }

    /**
     * Initialize dependencies.
     *
     * @param CallbackInvoker $invoker
     * @param MessageEncoder $messageEncoder
     * @param ResourceConnection $resource
     * @param ConsumerConfigurationInterface $configuration
     */
    public function __construct(
        CallbackInvoker $invoker,
        MessageEncoder $messageEncoder,
        ResourceConnection $resource,
        ConsumerConfigurationInterface $configuration
    ) {
        $this->invoker = $invoker;
        $this->messageEncoder = $messageEncoder;
        $this->resource = $resource;
        $this->configuration = $configuration;
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
     * @return void
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
                    call_user_func_array($callback, $decodedMessage);
                }
            } else {
                foreach ($handlers as $callback) {
                    call_user_func($callback, $decodedMessage);
                }
            }
        }
    }

    /**
     * Get transaction callback
     *
     * @param QueueInterface $queue
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {
            try {
                $topicName = $message->getProperties()['topic_name'];
                $allowedTopics = $this->configuration->getTopicNames();
                $this->resource->getConnection()->beginTransaction();
                $this->getMessageController()->lock($message, $this->configuration->getConsumerName());
                if (in_array($topicName, $allowedTopics)) {
                    $this->dispatchMessage($message);
                    $this->resource->getConnection()->commit();
                    $queue->acknowledge($message);
                } else {
                    $queue->reject($message); //push message back to the queue
                }
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
