<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\AbstractL1L2CacheProfileTestCase;
use Magento\Framework\Cache\Backend\SymfonyL2Cache;

/**
 * L1/L2 cache behaviour for the modern Symfony two-tier profile (cache-backend=symfony_l2).
 * Skipped on every other backend (zend_l2, single-tier redis/valkey/file, legacy redis/valkey/file).
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SymfonyL1L2CacheProfileTest extends AbstractL1L2CacheProfileTestCase
{
    /**
     * @inheritDoc
     */
    protected function getProfileBackends(): array
    {
        // symfony_l2 profile stores the short backend id in cache/frontend/default/backend.
        return ['symfony_l2'];
    }

    /**
     * @inheritDoc
     */
    protected function getProfileLabel(): string
    {
        return 'symfony_l2';
    }

    /**
     * @inheritDoc
     */
    protected function localHas(object $backend, string $id): bool
    {
        return $backend->getLocal()->load($id) !== false;
    }

    /**
     * @inheritDoc
     */
    protected function remoteHas(object $backend, string $id): bool
    {
        return $backend->getRemote()->load($id) !== false;
    }

    /**
     * @inheritDoc
     */
    protected function evictLocal(object $backend, string $id): void
    {
        $backend->getLocal()->remove($id);
    }

    /**
     * @inheritDoc
     */
    protected function getTwoTierBackendClass(): string
    {
        return SymfonyL2Cache::class;
    }
}
