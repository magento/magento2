<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Used to generate unique id for queue message.
 *
 * @api
 * @since 102.0.3
 */
interface MessageIdGeneratorInterface
{
    /**
     * Generate unique message id based on topic name.
     *
     * @param string $topicName
     * @return string
     * @since 102.0.3
     */
    public function generate($topicName);
}
