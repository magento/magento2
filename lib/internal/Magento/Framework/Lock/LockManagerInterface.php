<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock;

/**
 * Interface of a lock manager
 *
 * @api
 */
interface LockManagerInterface
{
    /**
     * Sets a lock
     *
     * @param string $name lock name
     * @param int $timeout How long to wait lock acquisition in seconds, negative value means infinite timeout
     * @return bool
     * @api
     */
    public function lock(string $name, int $timeout = -1): bool;

    /**
     * Releases a lock
     *
     * @param string $name lock name
     * @return bool
     * @api
     */
    public function unlock(string $name): bool;

    /**
     * Tests if lock is set
     *
     * @param string $name lock name
     * @return bool
     * @api
     */
    public function isLocked(string $name): bool;
}
