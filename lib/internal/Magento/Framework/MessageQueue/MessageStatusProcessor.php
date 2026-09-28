<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Changes messages status.
 */
class MessageStatusProcessor
{
    /**
     * Acknowledge all provided messages.
     *
     * @param QueueInterface $queue
     * @param array $messages
     * @return void
     */
    public function acknowledgeMessages(QueueInterface $queue, array $messages)
    {
        foreach ($messages as $message) {
            $queue->acknowledge($message);
        }
    }

    /**
     * Reject all provided messages.
     *
     * @param QueueInterface $queue
     * @param array $messages
     * @return void
     */
    public function rejectMessages(QueueInterface $queue, array $messages)
    {
        foreach ($messages as $message) {
            $queue->reject($message);
        }
    }
}
