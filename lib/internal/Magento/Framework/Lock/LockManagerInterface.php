<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Lock;

/**
 * Interface of a lock manager
 *
 * @api
 */
interface LockManagerInterface
{
    /**
     * Sets a lock for name
     *
     * @param string $name lock name
     * @param int $timeout Timeout in seconds, negative value means infinite timeout
     * @return bool
     */
    public function setLock($name, $timeout = -1);

    /**
     * Releases a lock for name
     *
     * @param string $name lock name
     * @return bool
     * @api
     */
    public function releaseLock($name);

    /**
     * Tests of lock is set for name
     *
     * @param string $name lock name
     * @return bool
     * @api
     */
    public function isLocked($name);
}
