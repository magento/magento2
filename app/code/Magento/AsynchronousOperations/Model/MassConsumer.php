<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\MessageQueue\CallbackInvokerInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Registry;

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
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var MassConsumerEnvelopeCallbackFactory
     */
    private $massConsumerEnvelopeCallback;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Initialize dependencies.
     *
     * @param CallbackInvokerInterface $invoker
     * @param ConsumerConfigurationInterface $configuration
     * @param MassConsumerEnvelopeCallbackFactory $massConsumerEnvelopeCallback
     * @param Registry $registry
     */
    public function __construct(
        CallbackInvokerInterface $invoker,
        ConsumerConfigurationInterface $configuration,
        MassConsumerEnvelopeCallbackFactory $massConsumerEnvelopeCallback,
        Registry $registry
    ) {
        $this->invoker = $invoker;
        $this->configuration = $configuration;
        $this->massConsumerEnvelopeCallback = $massConsumerEnvelopeCallback;
        $this->registry = $registry;
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
        $callbackInstance =  $this->massConsumerEnvelopeCallback->create(
            [
                'configuration' => $this->configuration,
                'queue' => $queue,
            ]
        );
        return function (EnvelopeInterface $message) use ($callbackInstance) {
            $callbackInstance->execute($message);
        };
    }
}
