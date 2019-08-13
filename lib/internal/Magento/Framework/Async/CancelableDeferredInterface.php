<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Async;

/**
 * Described deferred operation that can be canceled.
 */
interface CancelableDeferredInterface extends DeferredInterface
{
    /**
     * Cancels the operation.
     *
     * Will not cancel the operation when it has already started and given $force is not true.
     *
     * @param bool $force Cancel operation even if it's already started.
     * @return void
     * @throws CancelingDeferredException When failed to cancel.
     */
    public function cancel(bool $force = false): void;

    /**
     * Whether the operation has been cancelled already.
     *
     * @return bool
     */
    public function isCancelled(): bool;
}
