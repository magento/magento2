<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\lock\Backend;

class Database implements \Magento\Framework\Lock\LockManagerInterface
{
    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    private $adapter;

    public function __construct(
        \Magento\Framework\DB\Adapter\AdapterInterface $adapter
    )
    {
        $this->adapter = $adapter;
    }

    /**
     * Sets a lock for name
     *
     * @param string $name lock name
     * @return bool
     */
    public function setLock($name, $timeout = -1)
    {
        return (bool) $this->adapter->query("SELECT GET_LOCK(?, ?);", array((string)$name, (int)$timeout))
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
        return (bool) $this->adapter->query("SELECT RELEASE_LOCK(?);", array((string)$name))->fetchColumn();
    }

    /**
     * Tests of lock is set for name
     *
     * @param string $name lock name
     * @return bool
     */
    public function isLocked($name)
    {
        return (bool) $this->adapter->query("SELECT IS_USED_LOCK(?);", array((string)$name))->fetchColumn();
    }
}
