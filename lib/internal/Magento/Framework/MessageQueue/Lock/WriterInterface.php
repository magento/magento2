<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

use Magento\Framework\MessageQueue\LockInterface;

/**
 * Message lock writer
 */
interface WriterInterface
{
    /**
     * Save lock
     *
     * @param LockInterface $lock
     * @return void
     */
    public function saveLock(LockInterface $lock): void;

    /**
     * Remove outdated locks
     *
     * @return void
     */
    public function releaseOutdatedLocks(): void;
}
