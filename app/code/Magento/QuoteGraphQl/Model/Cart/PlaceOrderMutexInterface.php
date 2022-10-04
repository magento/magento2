<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;

/**
 * Intended to prevent race conditions during order place operation by concurrent requests.
 */
interface PlaceOrderMutexInterface
{
    /**
     * Acquires a lock for quote, executes callable and releases the lock after.
     *
     * @param string $maskedId
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws LocalizedException
     */
    public function execute(string $maskedId, callable $callable, array $args = []);
}
