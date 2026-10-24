<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Lock;

/**
 * Message lock reader interface
 * @api
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
