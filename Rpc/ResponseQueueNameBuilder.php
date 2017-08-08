<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Rpc;

/**
 * Class \Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder
 *
 * @since 2.2.0
 */
class ResponseQueueNameBuilder
{
    /**
     * Response queue name prefix
     */
    const RESPONSE_QUEUE_PREFIX = 'responseQueue.';

    /**
     * Get response queue name.
     *
     * @param string $topicName
     * @return string
     * @since 2.2.0
     */
    public function getQueueName($topicName)
    {
        return self::RESPONSE_QUEUE_PREFIX . str_replace('-', '_', $topicName);
    }
}
