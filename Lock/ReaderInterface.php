<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

/**
 * Message lock reader interface
 * @since 2.1.0
 */
interface ReaderInterface
{
    /**
     * Get lock from storage
     *
     * @param \Magento\Framework\MessageQueue\LockInterface $lock
     * @param string $code
     * @return void
     * @since 2.1.0
     */
    public function read(\Magento\Framework\MessageQueue\LockInterface $lock, $code);
}
