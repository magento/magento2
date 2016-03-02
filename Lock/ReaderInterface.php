<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param string $code
     * @return void
     */
    public function read(\Magento\Framework\MessageQueue\LockInterface $lock, $code);
}
