<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\lock\Backend;

class Database implements \Magento\Framework\Lock\LockManagerInterface
{
    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->resource = $resource;
    }

    /**
     * Sets a lock for name
     *
     * @param string $name lock name
     * @return bool
     */
    public function setLock($name, $timeout = -1)
    {
        return (bool)$this->resource->getConnection()->query("SELECT GET_LOCK(?, ?);", array((string)$name, (int)$timeout))
            ->fetchColumn();
    }

    /**
     * Releases a lock for name
     *
     * @param string $name lock name
     * @return bool
     */
    public function releaseLock($name)
    {
        return (bool)$this->resource->getConnection()->query("SELECT RELEASE_LOCK(?);", array((string)$name))->fetchColumn();
    }

    /**
     * Tests of lock is set for name
     *
     * @param string $name lock name
     * @return bool
     */
    public function isLocked($name)
    {
        return (bool)$this->resource->getConnection()->query("SELECT IS_USED_LOCK(?);", array((string)$name))->fetchColumn();
    }
}
