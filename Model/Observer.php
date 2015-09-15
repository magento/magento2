<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Class Observer is used for cron processing
 *
 * @package Magento\MysqlMq\Model
 */
class Observer
{
    /**
     * @var \Magento\MysqlMq\Model\QueueManagement
     */
    protected $queueManagement;

    public function __construct(
        \Magento\MysqlMq\Model\QueueManagement $queueManagement
    ) {
        $this->queueManagement = $queueManagement;
    }

    /**
     * Clean up old messages from database
     */
    public function cleanupMessages()
    {
        $this->queueManagement->markMessagesForDelete();
    }
}
