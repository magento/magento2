<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

use Magento\Framework\MessageQueue\LockInterface;

/**
 * Message lock reader interface
 */
interface ReaderInterface
{
    /**
     * Get lock from storage
     *
     * @param LockInterface $lock
     * @param string $code
     * @return void
     */
    public function read(LockInterface $lock, string $code): void;
}
