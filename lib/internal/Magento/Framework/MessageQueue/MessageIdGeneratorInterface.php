<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Used to generate unique id for queue message.
 *
 * @api
 * @since 103.0.0
 */
interface MessageIdGeneratorInterface
{
    /**
     * Generate unique message id based on topic name.
     *
     * @param string $topicName
     * @return string
     * @since 103.0.0
     */
    public function generate($topicName);
}
