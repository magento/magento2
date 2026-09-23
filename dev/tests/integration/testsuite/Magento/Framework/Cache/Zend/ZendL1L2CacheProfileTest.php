<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use Magento\Framework\Cache\AbstractL1L2CacheProfileTestCase;
use Magento\Framework\Cache\Backend\RemoteSynchronizedCache;

/**
 * L1/L2 cache behaviour for the legacy Zend two-tier profile (cache-backend=zend_l2).
 * Skipped on every other backend (symfony_l2, single-tier redis/valkey/file, legacy redis/valkey/file).
 *
 * This subclass is self-contained: when the legacy zend_l2 profile is dropped, delete this file only.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ZendL1L2CacheProfileTest extends AbstractL1L2CacheProfileTestCase
{
    /**
     * @inheritDoc
     */
    protected function getProfileBackends(): array
    {
        // zend_l2 profile stores the RemoteSynchronizedCache FQCN in cache/frontend/default/backend.
        return [RemoteSynchronizedCache::class];
    }

    /**
     * @inheritDoc
     */
    protected function getProfileLabel(): string
    {
        return 'zend_l2';
    }

    /**
     * @inheritDoc
     */
    protected function localHas(object $backend, string $id): bool
    {
        return $this->tier($backend, 'local')->load($id) !== false;
    }

    /**
     * @inheritDoc
     */
    protected function remoteHas(object $backend, string $id): bool
    {
        return $this->tier($backend, 'remote')->load($id) !== false;
    }

    /**
     * @inheritDoc
     */
    protected function evictLocal(object $backend, string $id): void
    {
        $this->tier($backend, 'local')->remove($id);
    }

    /**
     * RemoteSynchronizedCache keeps its L1/L2 tiers in private properties; read them via reflection.
     *
     * @param object $backend
     * @param string $property 'local' or 'remote'
     * @return \Zend_Cache_Backend_ExtendedInterface
     */
    private function tier(object $backend, string $property)
    {
        // Non-public members are reflection-accessible without setAccessible() since PHP 8.1.
        return (new \ReflectionProperty($backend, $property))->getValue($backend);
    }

    /**
     * @inheritDoc
     */
    protected function getTwoTierBackendClass(): string
    {
        return RemoteSynchronizedCache::class;
    }
}
