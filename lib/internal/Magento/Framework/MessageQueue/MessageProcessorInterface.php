<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface for processing queue messages.
 * @api
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
