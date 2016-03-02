<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

/**
 * Class Lock to handle message lock transactions.
 */
interface WriterInterface
{
    /**
     * Save lock
     *
     * @param \Magento\Framework\MessageQueue\LockInterface $lock
     * @return void
     */
    public function saveLock(\Magento\Framework\MessageQueue\LockInterface $lock);

    /**
     * Remove outdated locks
     *
     * @param int $interval
     * @return mixed
     */
    public function releaseOutdatedLocks($interval);
}
