<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Lock;

/**
 * Message lock reader interface
 */
interface ReaderInterface
{
    /**
     * Get lock from storage
     *
     * @param \Magento\Framework\MessageQueue\LockInterface $lock
     * @param string $code
     * @return void
     */
    public function read(\Magento\Framework\MessageQueue\LockInterface $lock, $code);
}
