<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Backend;

use Magento\Framework\Cache\FrontendInterface;

/**
 * Implementation of the lock manager on the basis of the caching system.
 */
class Cache implements \Magento\Framework\Lock\LockManagerInterface
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }
    /**
     * @inheritdoc
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        return $this->cache->save('1', $name, [], $timeout);
    }

    /**
     * @inheritdoc
     */
    public function unlock(string $name): bool
    {
        return $this->cache->remove($name);
    }

    /**
     * @inheritdoc
     */
    public function isLocked(string $name): bool
    {
        return (bool)$this->cache->test($name);
    }
}
