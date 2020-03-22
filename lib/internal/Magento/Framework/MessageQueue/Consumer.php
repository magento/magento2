<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Closure;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface as UsedConsumerConfig;
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
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * @var CallbackInvokerInterface
     */
    private $invoker;

    /**
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @var UsedConsumerConfig
     */
    private $usedConsumerConfig;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param ResourceConnection $resource
     * @param CommunicationConfig $communicationConfig
     * @param CallbackInvokerInterface $invoker
     * @param ConsumerConfig $consumerConfig
     * @param UsedConsumerConfig $usedConsumerConfig
     * @param MessageController $messageController
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @param EnvelopeFactory $envelopeFactory
     * @param QueueRepository $queueRepository
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resource,
        CommunicationConfig $communicationConfig,
        CallbackInvokerInterface $invoker,
        ConsumerConfig $consumerConfig,
        UsedConsumerConfig $usedConsumerConfig,
        MessageController $messageController,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        EnvelopeFactory $envelopeFactory,
        QueueRepository $queueRepository,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->communicationConfig = $communicationConfig;
        $this->invoker = $invoker;
        $this->consumerConfig = $consumerConfig;
        $this->usedConsumerConfig = $usedConsumerConfig;
        $this->messageController = $messageController;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->envelopeFactory = $envelopeFactory;
        $this->logger = $logger;
        $this->queueRepository = $queueRepository;
    }

    /**
     * @inheritdoc
     */
    public function process($maxNumberOfMessages = null): void
    {
        $queue = $this->usedConsumerConfig->getQueue();

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
    protected function getTransactionCallback(QueueInterface $queue): Closure
    {
        return function (EnvelopeInterface $message) use ($queue) {
            /** @var LockInterface $lock */
            $lock = null;
            try {
                $topicName = $message->getProperties()['topic_name'];
                $topicConfig = $this->communicationConfig->getTopic($topicName);
                $lock = $this->messageController->lock($message, $this->usedConsumerConfig->getConsumerName());

                if ($topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]) {
                    $responseBody = $this->dispatchMessage($message, true);
                    $responseMessage = $this->envelopeFactory->create(
                        ['body' => $responseBody, 'properties' => $message->getProperties()]
                    );
                    $this->sendResponse($responseMessage);
                } else {
                    $allowedTopics = $this->usedConsumerConfig->getTopicNames();
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
                    $this->removeLock($lock);
                }
            } catch (NotFoundException $e) {
                $queue->acknowledge($message);
                $this->logger->warning($e->getMessage());
            } catch (Exception $e) {
                $queue->reject($message, false, $e->getMessage());
                if ($lock) {
                    $this->removeLock($lock);
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
    protected function dispatchMessage(EnvelopeInterface $message, $isSync = false): ?string
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $handlers = $this->usedConsumerConfig->getHandlers($topicName);
        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());

        if (isset($decodedMessage)) {
            $messageSchemaType = $this->usedConsumerConfig->getMessageSchemaType($topicName);
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
    protected function processSyncResponse(string $topicName, $result): string
    {
        if (isset($result)) {
            $this->messageValidator->validate($topicName, $result, false);
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
    protected function sendResponse(EnvelopeInterface $envelope): void
    {
        $messageProperties = $envelope->getProperties();
        $connectionName = $this->consumerConfig
            ->getConsumer($this->usedConsumerConfig->getConsumerName())->getConnection();
        $queue = $this->queueRepository->get($connectionName, $messageProperties['reply_to']);
        $queue->push($envelope);
    }

    /**
     * Remove lock.
     *
     * @param LockInterface $lock
     *
     * @return void
     */
    private function removeLock(LockInterface $lock): void
    {
        $this->resource->getConnection()
            ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
    }
}
