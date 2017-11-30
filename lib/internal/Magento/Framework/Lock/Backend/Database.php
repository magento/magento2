<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\Framework\lock\Backend;

use Magento\Framework\App\ResourceConnection;

class Database implements \Magento\Framework\Lock\LockManagerInterface
{
    /** @var ResourceConnection */
    private $resource;

    public function __construct(
        ResourceConnection $resource
    )
    {
        $this->resource = $resource;
    }

    /**
     * Sets a lock for name
     *
     * @param string $name lock name
     * @param int $timeout How long to wait lock acquisition in seconds, negative value means infinite timeout
     * @return bool
     */
    public function setLock(string $name, int $timeout = -1): bool
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
    public function releaseLock(string $name): bool
    {
        return (bool)$this->resource->getConnection()->query("SELECT RELEASE_LOCK(?);", array((string)$name))->fetchColumn();
    }

    /**
     * Tests of lock is set for name
     *
     * @param string $name lock name
     * @return bool
     */
    public function isLocked(string $name): bool
    {
        return (bool)$this->resource->getConnection()->query("SELECT IS_USED_LOCK(?);", array((string)$name))->fetchColumn();
    }
}
