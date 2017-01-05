<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Observer class to monitor outdated messages.
 */
class Observer
{
    /**
     * @var \Magento\MysqlMq\Model\QueueManagement
     */
    protected $queueManagement;

    /**
     * Create Observer
     * @param QueueManagement $queueManagement
     */
    public function __construct(
        \Magento\MysqlMq\Model\QueueManagement $queueManagement
    ) {
        $this->queueManagement = $queueManagement;
    }

    /**
     * Clean up old messages from database
     * @return void
     */
    public function cleanupMessages()
    {
        $this->queueManagement->markMessagesForDelete();
    }
}
