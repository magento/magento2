<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Cron;

/**
 * Class RemoveOutdatedLocks to remove outdated messages (set by interval, default 1 day) using cron from lock table.
 *
 */
class RemoveOutdatedLocks
{
    /**
     * @var \Magento\Framework\MessageQueue\ResourceModel\Lock
     */
    private $resource;

    /**
     * @var int
     */
    private $interval;

    /**
     * Inject dependencies
     *
     * @param \Magento\Framework\MessageQueue\ResourceModel\Lock $resource
     * @param int $interval
     */
    public function __construct(\Magento\Framework\MessageQueue\ResourceModel\Lock $resource, $interval = 86400)
    {
        $this->resource = $resource;
        $this->interval = $interval;
    }

    public function execute()
    {
        $this->resource->deleteOutdated($this->interval);
    }
}
