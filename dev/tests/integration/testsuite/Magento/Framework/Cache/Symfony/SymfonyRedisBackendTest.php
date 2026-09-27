<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\Backend\SymfonyL2Cache;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies remote Redis utilization reporting for the Symfony L1/L2 backend.
 */
class SymfonyRedisBackendTest extends CacheFrontendTestCase
{
    public function testL2BackendReportsRemoteFillingPercentage(): void
    {
        $configuration = CacheConfigurationProvider::provide()['symfony-l1-l2'][1];
        $frontend = $this->createFrontend($configuration, 'symfony-l1-l2', 'SYMFONY_REDIS');
        $backend = $frontend->getBackend();

        $this->assertInstanceOf(SymfonyL2Cache::class, $backend);
        $this->assertIsNumeric($backend->getFillingPercentage());
        $this->assertGreaterThanOrEqual(0, (float) $backend->getFillingPercentage());
    }
}
