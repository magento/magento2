<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Loads message processor depending on the message type.
 */
class MessageProcessorLoader
{
    /**
     * @var \Magento\Framework\MessageQueue\MessageProcessorInterface
     */
    private $mergedMessageProcessor;

    /**
     * @var \Magento\Framework\MessageQueue\MessageProcessorInterface
     */
    private $defaultMessageProcessor;

    /**
     * @param MessageProcessorInterface $mergedMessageProcessor
     * @param MessageProcessorInterface $defaultMessageProcessor
     */
    public function __construct(
        MessageProcessorInterface $mergedMessageProcessor,
        MessageProcessorInterface $defaultMessageProcessor
    ) {
        $this->mergedMessageProcessor = $mergedMessageProcessor;
        $this->defaultMessageProcessor = $defaultMessageProcessor;
    }

    /**
     * Loads message processor depending on the message type.
     *
     * @param array $messages
     * @return MessageProcessorInterface
     */
    public function load(array $messages)
    {
        $message = $this->getMergedMessage($messages);

        return ($message instanceof MergedMessageInterface)
            ? $this->mergedMessageProcessor : $this->defaultMessageProcessor;
    }

    /**
     * Get first message from the list of merged messages.
     *
     * @param array $messages
     * @return mixed|null
     */
    private function getMergedMessage(array $messages)
    {
        $message = null;

        if ($messages) {
            $topicMessages = array_shift($messages);

            if ($topicMessages) {
                $message = array_shift($topicMessages);
            }
        }

        return $message;
    }
}
