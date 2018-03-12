<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\App\ResourceConnection;

/**
 * Processes any type of messages except messages implementing MergedMessageInterface.
 */
class MessageProcessor implements MessageProcessorInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\MessageStatusProcessor
     */
    private $messageStatusProcessor;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param MessageStatusProcessor $messageStatusProcessor
     * @param ResourceConnection $resource
     */
    public function __construct(
        MessageStatusProcessor $messageStatusProcessor,
        ResourceConnection $resource
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
            $this->resource->getConnection()->beginTransaction();
            $this->messageStatusProcessor->acknowledgeMessages($queue, $messagesToAcknowledge);
            $this->dispatchMessages($configuration, $mergedMessages);
            $this->resource->getConnection()->commit();
            $this->messageStatusProcessor->acknowledgeMessages($queue, $messages);
        } catch (ConnectionLostException $e) {
            $this->resource->getConnection()->rollBack();
        } catch (\Exception $e) {
            $this->resource->getConnection()->rollBack();
            $this->messageStatusProcessor->rejectMessages($queue, $messages);
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
