<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Shared behaviour for the two-tier (L1/L2) cache profiles, asserting the *distinctive* two-tier
 * semantics — data written to both tiers, an L1 miss self-healing from L2, and invalidation clearing
 * both tiers (so a subsequent load is a regenerate-miss). Concrete subclasses bind it to a single
 * profile (symfony_l2 or zend_l2) and expose that profile's L1/L2 tiers, so each can be
 * enabled/removed independently — dropping legacy zend later is just deleting its subclass.
 *
 * Skipped automatically unless the install runs the subclass's profile.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
abstract class AbstractL1L2CacheProfileTestCase extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Allowed `cache/frontend/default/backend` values that identify this subclass's profile.
     *
     * @return string[]
     */
    abstract protected function getProfileBackends(): array;

    /**
     * Human-readable profile name for skip messages.
     *
     * @return string
     */
    abstract protected function getProfileLabel(): string;

    /**
     * Whether the id is present in the L1 (local) tier of the given two-tier backend.
     *
     * @param object $backend Two-tier backend (SymfonyL2Cache or RemoteSynchronizedCache)
     * @param string $id
     * @return bool
     */
    abstract protected function localHas(object $backend, string $id): bool;

    /**
     * Whether the id is present in the L2 (remote) tier of the given two-tier backend.
     *
     * @param object $backend
     * @param string $id
     * @return bool
     */
    abstract protected function remoteHas(object $backend, string $id): bool;

    /**
     * Evict an id from the L1 (local) tier only, leaving L2 intact (simulates a cold/evicted node).
     *
     * @param object $backend
     * @param string $id
     * @return void
     */
    abstract protected function evictLocal(object $backend, string $id): void;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        if (!in_array($this->getConfiguredBackend(), $this->normalizedProfileBackends(), true)) {
            $this->markTestSkipped(
                sprintf('Applies only to the %s L1/L2 cache profile.', $this->getProfileLabel())
            );
        }

        $this->cache = $this->objectManager->get(CacheInterface::class);
    }

    /**
     * The default cache must be this profile's two-tier backend.
     */
    public function testDefaultCacheUsesExpectedTwoTierBackend(): void
    {
        $this->assertContains(
            $this->getConfiguredBackend(),
            $this->normalizedProfileBackends(),
            'Default cache backend does not match the expected ' . $this->getProfileLabel() . ' backend.'
        );
    }

    /**
     * End-to-end save -> load -> remove through the real application cache (the two-tier default).
     */
    public function testApplicationCacheSaveLoadRemove(): void
    {
        $id = 'l1l2_roundtrip_' . uniqid();
        $value = 'l1l2-value-' . $id;

        try {
            $this->assertTrueSave($this->cache->save($value, $id, ['L1L2_IT'], 3600));
            $this->assertSame($value, $this->cache->load($id), 'Value must load back from the two-tier cache.');
            $this->assertTrue($this->cache->remove($id), 'Remove must succeed.');
            $this->assertFalse($this->cache->load($id), 'Value must be gone after remove.');
        } finally {
            $this->cache->remove($id);
        }
    }

    /**
     * A write must land in BOTH tiers: the fast local L1 and the shared persistent L2.
     */
    public function testWriteIsStoredInBothTiers(): void
    {
        $backend = $this->getTwoTierBackend();
        $id = 'l1l2_bothtiers_' . uniqid();

        try {
            $backend->save('both-tiers', $id, ['L1L2_TIERS'], 3600);
            $this->assertTrue($this->remoteHas($backend, $id), 'Write must be persisted in the L2 (remote) tier.');
            $this->assertTrue($this->localHas($backend, $id), 'Write must be stored in the L1 (local) tier.');
        } finally {
            $backend->remove($id);
        }
    }

    /**
     * Cold/evicted L1: after wiping the local copy, a load must self-heal from L2 and repopulate L1.
     */
    public function testColdLocalSelfHealsFromRemote(): void
    {
        $backend = $this->getTwoTierBackend();
        $id = 'l1l2_selfheal_' . uniqid();

        try {
            $backend->save('from-l2', $id, [], 3600);

            // Simulate a cold node: drop the L1 copy but keep L2.
            $this->evictLocal($backend, $id);
            $this->assertFalse($this->localHas($backend, $id), 'Precondition: L1 copy is gone.');
            $this->assertTrue($this->remoteHas($backend, $id), 'Precondition: L2 copy still present.');

            // Load must fetch from L2 (not return a miss) and repopulate L1.
            $this->assertSame('from-l2', $backend->load($id), 'A cold L1 must self-heal by loading from L2.');
            $this->assertTrue($this->localHas($backend, $id), 'L1 must be repopulated from L2 after the load.');
        } finally {
            $backend->remove($id);
        }
    }

    /**
     * Tag invalidation must drop the entry from the authoritative L2 tier so the next load is a
     * regenerate-miss. (By design the L1 keeps no tag index — it self-heals on read once the L2
     * copy/hash is gone — so functional invalidation is proven by the load returning a miss.)
     */
    public function testInvalidationRemovesFromRemoteAndForcesRegenerate(): void
    {
        $backend = $this->getTwoTierBackend();
        $id = 'l1l2_invalidate_' . uniqid();

        try {
            $backend->save('to-invalidate', $id, ['L1L2_INV'], 3600);
            $this->assertTrue(
                $this->localHas($backend, $id) && $this->remoteHas($backend, $id),
                'Precondition: in both tiers.'
            );

            $backend->clean(CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, ['L1L2_INV']);

            $this->assertFalse(
                $this->remoteHas($backend, $id),
                'Invalidation must remove the entry from the authoritative L2 tier.'
            );
            $this->assertFalse(
                $backend->load($id),
                'After invalidation the load must be a regenerate-miss (L1 self-heals).'
            );
        } finally {
            $backend->remove($id);
        }
    }

    /**
     * The expected two-tier backend FQCN for this profile.
     *
     * @return string
     */
    abstract protected function getTwoTierBackendClass(): string;

    /**
     * The runtime two-tier backend instance used by the default cache. If the default cache is not
     * actually the expected two-tier backend at runtime (e.g. it fell back to a single-tier backend),
     * the tier-level test is skipped with a clear reason instead of failing cryptically.
     *
     * @return object SymfonyL2Cache (symfony_l2) or RemoteSynchronizedCache (zend_l2)
     */
    protected function getTwoTierBackend(): object
    {
        $backend = $this->cache->getFrontend()->getBackend();
        $expected = $this->getTwoTierBackendClass();
        if (!$backend instanceof $expected) {
            $this->markTestSkipped(sprintf(
                'Default cache runtime backend is %s, not the expected two-tier %s; '
                . 'tier-level assertions do not apply.',
                get_class($backend),
                $expected
            ));
        }

        return $backend;
    }

    /**
     * Assert a cache save succeeded (save() returns true on success).
     *
     * @param mixed $result
     * @return void
     */
    private function assertTrueSave($result): void
    {
        $this->assertTrue((bool)$result, 'Save must succeed.');
    }

    /**
     * Configured default cache backend name.
     *
     * @return string
     */
    private function getConfiguredBackend(): string
    {
        $backend = (string)$this->objectManager->get(DeploymentConfig::class)
            ->get('cache/frontend/default/backend');

        return ltrim($backend, '\\');
    }

    /**
     * Backend class/alias forms that count as this profile.
     *
     * @return string[]
     */
    private function normalizedProfileBackends(): array
    {
        return array_map(static fn (string $backend): string => ltrim($backend, '\\'), $this->getProfileBackends());
    }
}
