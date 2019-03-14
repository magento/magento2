<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\QueueInterface
 *
 * @api
 * @since 102.0.1
 */
interface QueueFactoryInterface
{
    /**
     * Create queue instance.
     *
     * @param string $queueName
     * @param string $connectionName
     * @return QueueInterface
     * @since 102.0.1
     */
    public function create($queueName, $connectionName);
}
