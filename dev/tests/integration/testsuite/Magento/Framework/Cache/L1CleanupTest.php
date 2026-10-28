<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Verifies that expired L1 entries are removed while live entries remain available from L2.
 */
class L1CleanupTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'l1L2Configurations')]
    public function testExpiredL1EntryIsPruned(string $configurationName, array $configuration): void
    {
        $frontend = $this->createFrontend($configuration, $configurationName, 'IT_L1_CLEANUP');
        $liveId = $this->cacheId($configurationName, 'live', 'integration');
        $expiredId = $this->cacheId($configurationName, 'expired', 'integration');

        try {
            $this->assertTrue($frontend->save('live', $liveId, ['INTEGRATION_L1_CLEANUP'], 3600));
            $this->assertTrue($frontend->save('expired', $expiredId, ['INTEGRATION_L1_CLEANUP'], 1));
            $this->assertSame('live', $frontend->load($liveId));
            $this->assertSame('expired', $frontend->load($expiredId));

            sleep(2);
            $this->assertTrue($frontend->getBackend()->clean(CacheConstants::CLEANING_MODE_OLD));
            $this->assertSame('live', $frontend->load($liveId));
            $this->assertFalse($frontend->load($expiredId));
        } finally {
            $frontend->remove($liveId);
            $frontend->remove($expiredId);
        }
    }
}
