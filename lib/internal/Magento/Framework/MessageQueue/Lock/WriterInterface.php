<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

/**
 * Message lock writer
 * @api
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
