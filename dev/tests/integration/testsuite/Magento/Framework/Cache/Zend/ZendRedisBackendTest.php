<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use Magento\Framework\Cache\Backend\Redis;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies legacy Magento Redis backend-specific APIs.
 */
class ZendRedisBackendTest extends CacheFrontendTestCase
{
    public function testRedisBackendReportsFillingPercentage(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-redis'][1];
        $frontend = $this->createFrontend($configuration, 'zend-redis', 'ZEND_REDIS');
        $backend = $frontend->getBackend();

        $this->assertInstanceOf(Redis::class, $backend);
        $this->assertIsNumeric($backend->getFillingPercentage());
        $this->assertGreaterThanOrEqual(0, (float) $backend->getFillingPercentage());
    }
}
