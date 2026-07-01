<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Processes any type of messages except messages implementing MergedMessageInterface.
 */
class MessageProcessor implements MessageProcessorInterface
{
    /**
     * Maximum number of transaction retries
     */
    public const MAX_TRANSACTION_RETRIES = 10;

    /**
     * @var \Magento\Framework\MessageQueue\MessageStatusProcessor
     */
    private $messageStatusProcessor;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var int
     */
    private $retryCount = 0;

    /**
     * @param MessageStatusProcessor $messageStatusProcessor
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        MessageStatusProcessor $messageStatusProcessor,
        ResourceConnection $resource,
        private readonly LoggerInterface $logger,
    ) {
        $this->messageStatusProcessor = $messageStatusProcessor;
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function process(
        QueueInterface $queue,
        ConsumerConfigurationInterface $configuration,
        array $messages,
        array $messagesToAcknowledge,
        array $mergedMessages
    ) {
        try {
            $this->messageStatusProcessor->acknowledgeMessages($queue, $messagesToAcknowledge);
        } catch (\Exception $e) {
            $this->logger->critical('Error during acknowledging previously processed messages.', ['exception' => $e]);
        }

        try {
            $this->resource->getConnection()->beginTransaction();
            $this->dispatchMessages($configuration, $mergedMessages);
            $this->resource->getConnection()->commit();

            try {
                $this->messageStatusProcessor->acknowledgeMessages($queue, $messages);
            } catch (\Exception $e) {
                $this->logger->critical('Error during acknowledging processed messages.', ['exception' => $e]);
            }
        } catch (ConnectionLostException $e) {
            $this->resource->getConnection()->rollBack();
        } catch (\Exception $e) {
            $retry = false;
            $this->resource->getConnection()->rollBack();
            if (strpos($e->getMessage(), 'Error while sending QUERY packet') !== false
                && $this->retryCount < self::MAX_TRANSACTION_RETRIES
            ) {
                $retry = true;
                $this->retryCount++;
                $this->resource->closeConnection();
                $this->process($queue, $configuration, $messages, $messagesToAcknowledge, $mergedMessages);
            }
            if (!$retry) {
                $this->messageStatusProcessor->rejectMessages($queue, $messages);
            }
        }
    }

    /**
     * Processes decoded messages, invokes callbacks, changes statuses for messages.
     *
     * @param ConsumerConfigurationInterface $configuration
     * @param array $messageList
     */
    private function dispatchMessages(ConsumerConfigurationInterface $configuration, array $messageList)
    {
        foreach ($messageList as $topicName => $messages) {
            foreach ($messages as $message) {
                $callbacks = $configuration->getHandlers($topicName);
                foreach ($callbacks as $callback) {
                    call_user_func($callback, $message);
                }
            }
        }
    }
}
