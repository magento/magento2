<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

/**
 * Message lock writer
 * @since 2.1.0
 */
interface WriterInterface
{
    /**
     * Save lock
     *
     * @param \Magento\Framework\MessageQueue\LockInterface $lock
     * @return void
     * @since 2.1.0
     */
    public function saveLock(\Magento\Framework\MessageQueue\LockInterface $lock);

    /**
     * Remove outdated locks
     *
     * @return void
     * @since 2.1.0
     */
    public function releaseOutdatedLocks();
}
