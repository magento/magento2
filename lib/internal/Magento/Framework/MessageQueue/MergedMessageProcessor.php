<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Processing messages implementing MergedMessageInterface.
 */
class MergedMessageProcessor implements MessageProcessorInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\MessageStatusProcessor
     */
    private $messageStatusProcessor;

    /**
     * @param MessageStatusProcessor $messageStatusProcessor
     */
    public function __construct(MessageStatusProcessor $messageStatusProcessor)
    {
        $this->messageStatusProcessor = $messageStatusProcessor;
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
        $this->messageStatusProcessor->acknowledgeMessages($queue, $messagesToAcknowledge);
        $this->dispatchMessages($queue, $configuration, $mergedMessages, $messages);
    }

    /**
     * Processing decoded messages, invoking callbacks, changing statuses for messages.
     *
     * @param QueueInterface $queue
     * @param ConsumerConfigurationInterface $configuration
     * @param array $messageList
     * @param array $originalMessages
     */
    private function dispatchMessages(
        QueueInterface $queue,
        ConsumerConfigurationInterface $configuration,
        array $messageList,
        array $originalMessages
    ) {
        $originalMessagesIds = [];

        try {
            foreach ($messageList as $topicName => $messages) {
                foreach ($messages as $message) {
                    /**
                     * @var \Magento\Framework\MessageQueue\MergedMessageInterface $message
                     */
                    $callbacks = $configuration->getHandlers($topicName);
                    $originalMessagesIds = $message->getOriginalMessagesIds();

                    foreach ($callbacks as $callback) {
                        call_user_func($callback, $message->getMergedMessage());
                    }

                    $originalMessages = $this->getOriginalMessages($originalMessages, $originalMessagesIds);
                    $this->messageStatusProcessor->acknowledgeMessages($queue, $originalMessages);
                }
            }
        } catch (\Exception $e) {
            $originalMessages = $this->getOriginalMessages($originalMessages, $originalMessagesIds);
            $this->messageStatusProcessor->rejectMessages($queue, $originalMessages);
        }
    }

    /**
     * Get original messages by messages ids.
     *
     * @param array $messages
     * @param array $messagesIds
     * @return array
     */
    private function getOriginalMessages(array $messages, array $messagesIds)
    {
        $originalMessages = [];

        foreach ($messagesIds as $messageId) {
            if (isset($messages[$messageId])) {
                $originalMessages[] = $messages[$messageId];
            }
        }

        return $originalMessages;
    }
}
