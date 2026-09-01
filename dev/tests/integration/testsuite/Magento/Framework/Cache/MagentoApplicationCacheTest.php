<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies the application cache service through Magento's public API.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class MagentoApplicationCacheTest extends TestCase
{
    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = Bootstrap::getObjectManager()->get(CacheInterface::class);
    }

    public function testApplicationCacheStoresAndRemovesData(): void
    {
        $this->assertInstanceOf(FrontendInterface::class, $this->cache->getFrontend());
        $id = 'integration_application_cache_' . uniqid();
        $value = 'application-cache-value';

        try {
            $this->assertTrue($this->cache->save($value, $id, ['INTEGRATION_APPLICATION'], 3600));
            $this->assertSame($value, $this->cache->load($id));
            $this->assertTrue($this->cache->remove($id));
            $this->assertFalse($this->cache->load($id));
        } finally {
            $this->cache->remove($id);
        }
    }
}
