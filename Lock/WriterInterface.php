<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

/**
 * Message lock writer
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
     * @return void
     */
    public function releaseOutdatedLocks();
}
