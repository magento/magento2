<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\CallbackInvoker;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Bulk\OperationManagementInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;

/**
 * Class Consumer used to process OperationInterface messages.
 * This could be used for both synchronous and asynchronous processing, depending on topic.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConsumer implements ConsumerInterface
{

    /**
     * @var \Magento\Framework\MessageQueue\CallbackInvoker
     */
    private $invoker;

    /**
     * @var \Magento\Framework\MessageQueue\MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var \Magento\Framework\MessageQueue\MessageValidator
     */
    private $messageValidator;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\MessageQueue\ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Bulk\OperationManagementInterface
     */
    private $operationManagement;

    /**
     * @var \Magento\Framework\MessageQueue\MessageController
     */
    private $messageController;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\CallbackInvoker $invoker
     * @param \Magento\Framework\MessageQueue\MessageValidator $messageValidator
     * @param \Magento\Framework\MessageQueue\MessageEncoder $messageEncoder
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\MessageQueue\ConsumerConfigurationInterface $configuration
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param \Magento\Framework\Bulk\OperationManagementInterface $operationManagement
     * @param \Magento\Framework\MessageQueue\MessageController $messageController
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        CallbackInvoker $invoker,
        MessageValidator $messageValidator,
        MessageEncoder $messageEncoder,
        ResourceConnection $resource,
        ConsumerConfigurationInterface $configuration,
        Json $jsonHelper,
        OperationManagementInterface $operationManagement,
        MessageController $messageController,
        LoggerInterface $logger = null
    ) {
        $this->invoker = $invoker;
        $this->messageValidator = $messageValidator;
        $this->messageEncoder = $messageEncoder;
        $this->resource = $resource;
        $this->configuration = $configuration;
        $this->jsonHelper = $jsonHelper;
        $this->operationManagement = $operationManagement;
        $this->messageController = $messageController;

        $this->logger = $logger ? : \Magento\Framework\App\ObjectManager::getInstance()->get(LoggerInterface::class);
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
     * Get transaction callback. This handles the case of async.
     *
     * @param QueueInterface $queue
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {
            /** @var LockInterface $lock */
            $lock = null;
            try {
                $topicName = $message->getProperties()['topic_name'];
                $lock = $this->messageController->lock($message, $this->configuration->getConsumerName());

                $allowedTopics = $this->configuration->getTopicNames();
                if (in_array($topicName, $allowedTopics)) {
                    $this->dispatchMessage($message);
                } else {
                    $queue->reject($message);
                    return;
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
            } catch (\Exception $e) {
                $queue->reject($message, false, $e->getMessage());
                if ($lock) {
                    $this->resource->getConnection()
                        ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
                }
            }
        };
    }

    /**
     * Decode OperationInterface message and process them.
     * Invokes service contract handler with the input params.
     * Updates the status of the mass operation.
     *
     * @param EnvelopeInterface $message
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function dispatchMessage(EnvelopeInterface $message)
    {
        $operation = $this->messageEncoder->decode(AsyncConfig::SYSTEM_TOPIC_NAME, $message->getBody());
        $this->messageValidator->validate(AsyncConfig::SYSTEM_TOPIC_NAME, $operation);

        $status = OperationInterface::STATUS_TYPE_COMPLETE;
        $errorCode = null;
        $messages = [];
        $topicName = $operation->getTopicName();
        $handlers = $this->configuration->getHandlers($topicName);
        try {
            $data = $this->jsonHelper->unserialize($operation->getSerializedData());
            $entityParams = $this->messageEncoder->decode($topicName, $data['meta_information']);
            $this->messageValidator->validate($topicName, $entityParams);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $messages[] = $e->getMessage();
        }

        if ($errorCode === null) {
            foreach ($handlers as $callback) {
                try {
                    call_user_func_array($callback, $entityParams);
                    $messages[] = sprintf('Service execution success %s::%s', get_class($callback[0]), $callback[1]);
                } catch (\Zend_Db_Adapter_Exception  $e) {
                    $this->logger->critical($e->getMessage());
                    if ($e instanceof LockWaitException
                        || $e instanceof DeadlockException
                        || $e instanceof ConnectionException
                    ) {
                        $status = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
                        $errorCode = $e->getCode();
                        $messages[] = __($e->getMessage());
                    } else {
                        $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                        $errorCode = $e->getCode();
                        $messages[] =
                            __('Sorry, something went wrong during product prices update. Please see log for details.');
                    }
                } catch (NoSuchEntityException $e) {
                    $this->logger->error($e->getMessage());
                    $status = ($e instanceof TemporaryStateExceptionInterface) ?
                        OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED :
                        OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                    $errorCode = $e->getCode();
                    $messages[] = $e->getMessage();
                } catch (LocalizedException $e) {
                    $this->logger->error($e->getMessage());
                    $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                    $errorCode = $e->getCode();
                    $messages[] = $e->getMessage();
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                    $errorCode = $e->getCode();
                    $messages[] = $e->getMessage();
                }
            }
        }

        $serializedData = (isset($errorCode)) ? $operation->getSerializedData() : null;
        $this->operationManagement->changeOperationStatus(
            $operation->getId(),
            $status,
            $errorCode,
            implode('; ', $messages),
            $serializedData
        );
    }
}
