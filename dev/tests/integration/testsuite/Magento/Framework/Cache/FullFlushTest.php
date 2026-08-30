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
 * Verifies that a backend-level full flush removes entries from both L1 and L2.
 */
class FullFlushTest extends CacheFrontendTestCase
{
    /**
     * @return array<string, array{string, array<string, mixed>}> 
     */
    public static function l1L2Configurations(): array
    {
        return array_filter(
            CacheConfigurationProvider::provide(),
            static fn(array $case): bool => str_ends_with($case[0], '-l1-l2')
        );
    }

    #[DataProviderExternal(self::class, 'l1L2Configurations')]
    public function testFullFlushRemovesEntriesFromBothTiers(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName, 'IT_FULL_FLUSH');
        $ids = [
            $this->cacheId($configurationName, 'flush_a', 'integration'),
            $this->cacheId($configurationName, 'flush_b', 'integration'),
        ];

        try {
            foreach ($ids as $id) {
                $this->assertTrue($frontend->save('value', $id, ['INTEGRATION_FULL_FLUSH'], 3600));
                $this->assertSame('value', $frontend->load($id));
            }

            $this->assertTrue($frontend->getBackend()->clean(CacheConstants::CLEANING_MODE_ALL));

            foreach ($ids as $id) {
                $this->assertFalse($frontend->load($id), $configurationName . ': ' . $id);
            }
        } finally {
            foreach ($ids as $id) {
                $frontend->remove($id);
            }
        }
    }
}
