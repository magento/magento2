<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\QueueInterface
 *
 * @api
 * @since 103.0.0
 */
interface QueueFactoryInterface
{
    /**
     * Create queue instance.
     *
     * @param string $queueName
     * @param string $connectionName
     * @return QueueInterface
     * @since 103.0.0
     */
    public function create($queueName, $connectionName);
}
