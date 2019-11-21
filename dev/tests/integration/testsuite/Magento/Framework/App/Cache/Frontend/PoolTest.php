<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache\Frontend;

use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * This superfluous comment can be removed as soon as the sniffs have been updated to match the coding guide lines.
 */
class PoolTest extends TestCase
{
    public function testPageCacheNotSameAsDefaultCacheDirectory(): void
    {
        /** @var ObjectManagerConfig $diConfig */
        $diConfig = ObjectManager::getInstance()->get(ObjectManagerConfig::class);
        $argumentConfig = $diConfig->getArguments(\Magento\Framework\App\Cache\Frontend\Pool::class);

        $pageCacheDir = $argumentConfig['frontendSettings']['page_cache']['backend_options']['cache_dir'] ?? null;
        $defaultCacheDir = $argumentConfig['frontendSettings']['default']['backend_options']['cache_dir'] ?? null;

        $noPageCacheMessage = "No default page_cache directory set in di.xml: \n" . var_export($argumentConfig, true);
        $this->assertNotEmpty($pageCacheDir, $noPageCacheMessage);

        $sameCacheDirMessage = 'The page_cache and default cache storages share the same cache directory';
        $this->assertNotSame($pageCacheDir, $defaultCacheDir, $sameCacheDirMessage);
    }

    /**
     * @covers  \Magento\Framework\App\Cache\Frontend\Pool::_getCacheSettings
     * @depends testPageCacheNotSameAsDefaultCacheDirectory
     */
    public function testCleaningDefaultCachePreservesPageCache()
    {
        $testData = 'test data';
        $testKey = 'test-key';

        /** @var \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool */
        $cacheFrontendPool = ObjectManager::getInstance()->get(\Magento\Framework\App\Cache\Frontend\Pool::class);

        $pageCache = $cacheFrontendPool->get('page_cache');
        $pageCache->save($testData, $testKey);

        $defaultCache = $cacheFrontendPool->get('default');
        $defaultCache->clean();

        $this->assertSame($testData, $pageCache->load($testKey));
    }
}
