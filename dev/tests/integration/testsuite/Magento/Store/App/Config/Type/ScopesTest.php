<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\App\Config\Type;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Cache;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify persistent scopes caching and invalidation by existing entity lifecycle callbacks.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppIsolation(true)]
#[DbIsolation(false)]
#[Cache('config', true)]
class ScopesTest extends TestCase
{
    /**
     * Integration application services.
     *
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * Shared configuration cache frontend.
     *
     * @var Config
     */
    private Config $cache;

    /**
     * Remove any scopes entry left by another test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cache = $this->objectManager->get(Config::class);
        $this->cache->remove(Scopes::CACHE_ID);
    }

    /**
     * Discard scopes data read during the test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->cache->remove(Scopes::CACHE_ID);
        parent::tearDown();
    }

    /**
     * A fresh instance reads warm scopes without issuing database queries.
     *
     * @return void
     */
    public function testWarmCacheDoesNotQueryDatabase(): void
    {
        $connection = $this->objectManager->get(ResourceConnection::class)->getConnection();
        $profiler = $connection->getProfiler();
        $wasEnabled = $profiler->getEnabled();
        $profiler->setEnabled(true);
        try {
            $beforeColdRead = $profiler->getTotalNumQueries();
            $scopes = $this->objectManager->create(Scopes::class);
            $expected = $scopes->get();
            $this->assertGreaterThanOrEqual(3, $profiler->getTotalNumQueries() - $beforeColdRead);
            $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));
            $scopes->clean();
            $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));

            $beforeWarmRead = $profiler->getTotalNumQueries();
            $freshScopes = $this->objectManager->create(Scopes::class);
            $this->assertSame($expected, $freshScopes->get());
            $this->assertSame($beforeWarmRead, $profiler->getTotalNumQueries());
        } finally {
            $profiler->setEnabled($wasEnabled);
        }
    }

    /**
     * Store creation and deletion invalidate an already populated scopes entry.
     *
     * @return void
     */
    public function testStoreSaveAndDeleteInvalidateWarmCache(): void
    {
        $defaultStore = $this->objectManager->get(StoreManagerInterface::class)->getDefaultStoreView();
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'scopes_cache_store',
            'name' => 'Scopes cache store',
            'website_id' => $defaultStore->getWebsiteId(),
            'group_id' => $defaultStore->getStoreGroupId(),
            'is_active' => 1,
        ]);
        $resource = $this->objectManager->get(StoreResource::class);
        $deleted = false;
        try {
            $path = 'stores/' . $store->getCode();
            $this->assertNull($this->objectManager->create(Scopes::class)->get($path));
            $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));
            $resource->save($store);
            $data = $this->objectManager->create(Scopes::class)->get($path);
            $this->assertSame($store->getCode(), $data['code']);
            $this->assertEquals($store->getId(), $data['store_id']);

            $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));
            $this->deleteStore($store);
            $deleted = true;
            $this->assertNull($this->objectManager->create(Scopes::class)->get($path));
        } finally {
            if (!$deleted && $store->getId()) {
                $this->deleteStore($store);
            }
        }
    }

    /**
     * A committed group rename is reflected in a fresh scopes instance.
     *
     * @return void
     */
    public function testStoreGroupSaveInvalidatesWarmCache(): void
    {
        $defaultStore = $this->objectManager->get(StoreManagerInterface::class)->getDefaultStoreView();
        $resource = $this->objectManager->get(GroupResource::class);
        $group = $this->objectManager->create(Group::class);
        $resource->load($group, $defaultStore->getStoreGroupId());
        $originalName = $group->getName();
        $path = 'groups/' . $group->getId() . '/name';
        try {
            $this->assertSame($originalName, $this->objectManager->create(Scopes::class)->get($path));
            $group->setName('Updated scopes cache group');
            $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));
            $resource->save($group);

            $this->assertSame($group->getName(), $this->objectManager->create(Scopes::class)->get($path));
        } finally {
            $group->setName($originalName);
            $resource->save($group);
        }
    }

    /**
     * Updating an existing website invalidates its cached scope through model cache tags.
     *
     * @return void
     */
    public function testExistingWebsiteUpdateInvalidatesWarmCache(): void
    {
        $defaultStore = $this->objectManager->get(StoreManagerInterface::class)->getDefaultStoreView();
        $resource = $this->objectManager->get(WebsiteResource::class);
        $website = $this->objectManager->create(Website::class);
        $resource->load($website, $defaultStore->getWebsiteId());
        $originalName = $website->getName();
        $path = 'websites/' . $website->getCode() . '/name';
        try {
            $this->assertSame($originalName, $this->objectManager->create(Scopes::class)->get($path));
            $website->setName('Updated scopes cache website');
            $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));
            $resource->save($website);

            $this->assertSame($website->getName(), $this->objectManager->create(Scopes::class)->get($path));
        } finally {
            $website->setName($originalName);
            $resource->save($website);
        }
    }

    /**
     * Cleaning the config cache removes and allows rebuilding the scopes entry.
     *
     * @return void
     */
    public function testConfigCacheCleanInvalidatesScopes(): void
    {
        $expected = $this->objectManager->create(Scopes::class)->get();
        $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));

        $this->cache->clean();

        $this->assertFalse($this->cache->load(Scopes::CACHE_ID));
        $this->assertSame($expected, $this->objectManager->create(Scopes::class)->get());
        $this->assertNotFalse($this->cache->load(Scopes::CACHE_ID));
    }

    /**
     * Delete a store while preserving the registry's previous secure-area value.
     *
     * @param Store $store
     * @return void
     */
    private function deleteStore(Store $store): void
    {
        $registry = $this->objectManager->get(Registry::class);
        $previousValue = $registry->registry('isSecureArea');
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        try {
            $this->objectManager->get(StoreResource::class)->delete($store);
        } finally {
            $registry->unregister('isSecureArea');
            if ($previousValue !== null) {
                $registry->register('isSecureArea', $previousValue);
            }
        }
    }
}
