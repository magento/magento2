<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerInterface;
use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\CallbackInvokerInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallbackFactory;

/**
 * Class Consumer used to process OperationInterface messages.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConsumer implements ConsumerInterface
{
    /**
     * @var CallbackInvokerInterface
     */
    private $invoker;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\MessageQueue\ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var \Magento\Framework\MessageQueue\MessageController
     */
    private $messageController;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OperationProcessor
     */
    private $operationProcessor;

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var MassConsumerEnvelopeCallbackFactory
     */
    private $massConsumerEnvelopeCallback;

    /**
     * Initialize dependencies.
     *
     * @param CallbackInvokerInterface $invoker
     * @param ResourceConnection $resource
     * @param MessageController $messageController
     * @param ConsumerConfigurationInterface $configuration
     * @param OperationProcessorFactory $operationProcessorFactory
     * @param LoggerInterface $logger
     * @param MassConsumerEnvelopeCallbackFactory $massConsumerEnvelopeCallback
     * @param Registry $registry
     */
    public function __construct(
        CallbackInvokerInterface $invoker,
        ResourceConnection $resource,
        MessageController $messageController,
        ConsumerConfigurationInterface $configuration,
        OperationProcessorFactory $operationProcessorFactory,
        LoggerInterface $logger,
        MassConsumerEnvelopeCallbackFactory $massConsumerEnvelopeCallback,
        Registry $registry = null
    ) {
        $this->invoker = $invoker;
        $this->resource = $resource;
        $this->messageController = $messageController;
        $this->configuration = $configuration;
        $this->operationProcessor = $operationProcessorFactory->create([
            'configuration' => $configuration
        ]);
        $this->logger = $logger;
        $this->massConsumerEnvelopeCallback = $massConsumerEnvelopeCallback;
        $this->registry = $registry ?? \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    public function process($maxNumberOfMessages = null)
    {
        $this->registry->register('isSecureArea', true, true);

        $queue = $this->configuration->getQueue();

        if (!isset($maxNumberOfMessages)) {
            $queue->subscribe($this->getTransactionCallback($queue));
        } else {
            $this->invoker->invoke($queue, $maxNumberOfMessages, $this->getTransactionCallback($queue));
        }

        $this->registry->unregister('isSecureArea');
    }

    /**
     * Get transaction callback. This handles the case of async.
     *
     * @param QueueInterface $queue
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        $callbackInstance =  $this->massConsumerEnvelopeCallback->create([
            'resource' => $this->resource,
            'messageController' => $this->messageController,
            'configuration' => $this->configuration,
            'operationProcessor' => $this->operationProcessor,
            'logger' => $this->logger,
            'registry' => $this->registry,
            'queue' => $queue,
        ]);
        return function (EnvelopeInterface $message) use ($callbackInstance) {
            $callbackInstance->execute($message);
        };
    }
}
