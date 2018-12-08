<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface for processing queue messages.
 */
interface MessageProcessorInterface
{
    /**
     * Processing decoded messages, invoking callbacks, changing statuses for messages.
     *
     * @param QueueInterface $queue
     * @param ConsumerConfigurationInterface $configuration
     * @param array $messages
     * @param array $messagesToAcknowledge
     * @param array $mergedMessages
     * @return void
     */
    public function process(
        QueueInterface $queue,
        ConsumerConfigurationInterface $configuration,
        array $messages,
        array $messagesToAcknowledge,
        array $mergedMessages
    );
}
