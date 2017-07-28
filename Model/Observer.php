<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Observer class to monitor outdated messages.
 * @since 2.0.0
 */
class Observer
{
    /**
     * @var \Magento\MysqlMq\Model\QueueManagement
     * @since 2.0.0
     */
    protected $queueManagement;

    /**
     * Create Observer
     * @param QueueManagement $queueManagement
     * @since 2.0.0
     */
    public function __construct(
        \Magento\MysqlMq\Model\QueueManagement $queueManagement
    ) {
        $this->queueManagement = $queueManagement;
    }

    /**
     * Clean up old messages from database
     * @return void
     * @since 2.0.0
     */
    public function cleanupMessages()
    {
        $this->queueManagement->markMessagesForDelete();
    }
}
