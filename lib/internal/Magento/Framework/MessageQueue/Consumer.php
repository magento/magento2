<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Closure;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Psr\Log\LoggerInterface;
use function call_user_func_array;

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
     * @var CallbackInvokerInterface
     */
    private $invoker;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param CallbackInvokerInterface $invoker
     * @param MessageEncoder $messageEncoder
     * @param ResourceConnection $resource
     * @param ConsumerConfigurationInterface $configuration
     * @param LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CallbackInvokerInterface $invoker,
        MessageEncoder $messageEncoder,
        ResourceConnection $resource,
        ConsumerConfigurationInterface $configuration,
        LoggerInterface $logger = null
    ) {
        $this->invoker = $invoker;
        $this->messageEncoder = $messageEncoder;
        $this->resource = $resource;
        $this->configuration = $configuration;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
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
     * Get transaction callback. This handles the case of both sync and async.
     *
     * @param QueueInterface $queue
     * @return Closure
     */
    protected function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {
            /** @var LockInterface $lock */
            $lock = null;
            try {
                $topicName = $message->getProperties()['topic_name'];
                $topicConfig = $this->getCommunicationConfig()->getTopic($topicName);
                $lock = $this->getMessageController()->lock($message, $this->configuration->getConsumerName());

                if ($topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]) {
                    $responseBody = $this->dispatchMessage($message, true);
                    $responseMessage = $this->getEnvelopeFactory()->create(
                        ['body' => $responseBody, 'properties' => $message->getProperties()]
                    );
                    $this->sendResponse($responseMessage);
                } else {
                    $allowedTopics = $this->configuration->getTopicNames();
                    if (in_array($topicName, $allowedTopics, true)) {
                        $this->dispatchMessage($message);
                    } else {
                        $queue->reject($message);
                        return;
                    }
                }
                $queue->acknowledge($message);
            } catch (MessageLockException $exception) {
                $queue->acknowledge($message);
            } catch (ConnectionLostException $e) {
                if ($lock) {
                    $this->resource->getConnection()
                        ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
                }
            } catch (NotFoundException $e) {
                $queue->acknowledge($message);
                $this->logger->warning($e->getMessage());
            } catch (Exception $e) {
                $queue->reject($message, false, $e->getMessage());
                if ($lock) {
                    $this->resource->getConnection()
                        ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
                }
            }
        };
    }

    /**
     * Decode message and invoke callback method, return reply back for sync processing.
     *
     * @param EnvelopeInterface $message
     * @param boolean $isSync
     * @return string|null
     * @throws LocalizedException
     */
    protected function dispatchMessage(EnvelopeInterface $message, $isSync = false)
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $handlers = $this->configuration->getHandlers($topicName);
        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());

        if (isset($decodedMessage)) {
            $messageSchemaType = $this->configuration->getMessageSchemaType($topicName);
            if ($messageSchemaType === CommunicationConfig::TOPIC_REQUEST_TYPE_METHOD) {
                foreach ($handlers as $callback) {
                    $result = call_user_func_array($callback, $decodedMessage);
                    return $this->processSyncResponse($topicName, $result);
                }
            } else {
                foreach ($handlers as $callback) {
                    $result = $callback($decodedMessage);
                    if ($isSync) {
                        return $this->processSyncResponse($topicName, $result);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Validate and encode synchronous handler output.
     *
     * @param string $topicName
     * @param mixed $result
     * @return string
     * @throws LocalizedException
     */
    protected function processSyncResponse($topicName, $result)
    {
        if (isset($result)) {
            $this->getMessageValidator()->validate($topicName, $result, false);
            return $this->messageEncoder->encode($topicName, $result, false);
        }

        throw new LocalizedException(__('No reply message resulted in RPC.'));
    }

    /**
     * Send RPC response message.
     *
     * @param EnvelopeInterface $envelope
     *
     * @return void
     * @throws LocalizedException
     */
    protected function sendResponse(EnvelopeInterface $envelope)
    {
        $messageProperties = $envelope->getProperties();
        $connectionName = $this->getConsumerConfig()
            ->getConsumer($this->configuration->getConsumerName())->getConnection();
        $queue = $this->getQueueRepository()->get($connectionName, $messageProperties['reply_to']);
        $queue->push($envelope);
    }

    /**
     * Get consumer config.
     *
     * @return ConsumerConfig
     *
     * @deprecated 100.2.0
     */
    private function getConsumerConfig()
    {
        if ($this->consumerConfig === null) {
            $this->consumerConfig = ObjectManager::getInstance()->get(ConsumerConfig::class);
        }
        return $this->consumerConfig;
    }

    /**
     * Get communication config.
     *
     * @return CommunicationConfig
     *
     * @deprecated 100.2.0
     */
    private function getCommunicationConfig()
    {
        if ($this->communicationConfig === null) {
            $this->communicationConfig = ObjectManager::getInstance()
                ->get(CommunicationConfig::class);
        }
        return $this->communicationConfig;
    }

    /**
     * Get queue repository.
     *
     * @return QueueRepository
     *
     * @deprecated 100.2.0
     */
    private function getQueueRepository()
    {
        if ($this->queueRepository === null) {
            $this->queueRepository = ObjectManager::getInstance()->get(QueueRepository::class);
        }
        return $this->queueRepository;
    }

    /**
     * Get message controller.
     *
     * @return MessageController
     *
     * @deprecated 100.1.0
     */
    private function getMessageController()
    {
        if ($this->messageController === null) {
            $this->messageController = ObjectManager::getInstance()
                ->get(MessageController::class);
        }
        return $this->messageController;
    }

    /**
     * Get message validator.
     *
     * @return MessageValidator
     *
     * @deprecated 100.2.0
     */
    private function getMessageValidator()
    {
        if ($this->messageValidator === null) {
            $this->messageValidator = ObjectManager::getInstance()
                ->get(MessageValidator::class);
        }
        return $this->messageValidator;
    }

    /**
     * Get envelope factory.
     *
     * @return EnvelopeFactory
     *
     * @deprecated 100.2.0
     */
    private function getEnvelopeFactory()
    {
        if ($this->envelopeFactory === null) {
            $this->envelopeFactory = ObjectManager::getInstance()
                ->get(EnvelopeFactory::class);
        }
        return $this->envelopeFactory;
    }
}
