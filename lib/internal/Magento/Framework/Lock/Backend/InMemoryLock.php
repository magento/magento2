<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Lock\Backend;

use Magento\Framework\Lock\LockManagerInterface;

/**
 * Implementation of in memory locking mechanism
 *
 * Useful for testing
 */
class InMemoryLock implements LockManagerInterface
{
    /**
     * List of locks
     *
     * @var string[]
     */
    private $locks = [];


    /**
     * Sets a lock
     *
     * @param string $name lock name
     * @param int $timeout How long to wait lock acquisition in seconds, negative value means infinite timeout
     * @return bool
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        if (!$this->isLocked($name)) {
            $this->locks[$name] = $timeout;

            return true;
        }

        return false;
    }

    /**
     * Releases a lock
     *
     * @param string $name lock name
     * @return bool
     */
    public function unlock(string $name): bool
    {
        $wasLocked = $this->isLocked($name);
        unset($this->locks[$name]);
        return $wasLocked;
    }

    /**
     * Tests if lock is set
     *
     * @param string $name lock name
     * @return bool
     */
    public function isLocked(string $name): bool
    {
        return isset($this->locks[$name]);
    }
}
