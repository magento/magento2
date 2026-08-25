<?php
/**
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\CacheConstants;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies NOT_MATCHING_TAG behavior for the Symfony Redis frontend.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SymfonyNotMatchingTagTest extends CacheFrontendTestCase
{
    public function testNotMatchingTagPreservesMatchingEntries(): void
    {
        $configuration = CacheConfigurationProvider::provide()['symfony-redis'];
        $frontend = $this->createFrontend($configuration[1], $configuration[0], 'NOT_TAG');
        $keptId = $this->cacheId('symfony-redis', 'kept', 'integration_not_tag');
        $removedId = $this->cacheId('symfony-redis', 'removed', 'integration_not_tag');

        try {
            $frontend->save('kept', $keptId, ['KEEP_TAG'], 3600);
            $frontend->save('removed', $removedId, ['REMOVE_TAG'], 3600);

            $this->assertTrue(
                $frontend->clean(CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG, ['KEEP_TAG'])
            );
            $this->assertSame('kept', $frontend->load($keptId));
            $this->assertFalse($frontend->load($removedId));
        } finally {
            $frontend->remove($keptId);
            $frontend->remove($removedId);
        }
    }
}
