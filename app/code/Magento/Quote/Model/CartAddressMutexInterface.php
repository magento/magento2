<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

/**
 * Intended to prevent race conditions during quote address processing by concurrent requests.
 */
interface CartAddressMutexInterface
{
    /**
     * Acquires a lock for quote address, executes callable and releases the lock after.
     *
     * @param string $lockName
     * @param callable $callable
     * @param int $result
     * @param array $args
     * @return mixed
     */
    public function execute(string $lockName, callable $callable, int $result, array $args = []);
}
