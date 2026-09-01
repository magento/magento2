<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\CacheConstants;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies Symfony L2 re-indexes tags when the payload is unchanged.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SymfonyIdenticalPayloadRetagTest extends CacheFrontendTestCase
{
    public function testIdenticalPayloadWithChangedTagsReindexesRemoteTags(): void
    {
        $configuration = CacheConfigurationProvider::provide()['symfony-l1-l2'];
        $frontend = $this->createFrontend($configuration[1], $configuration[0], 'RETAG');
        $id = $this->cacheId('symfony-l1-l2', 'identical', 'integration_retag');

        try {
            $this->assertTrue($frontend->save('same-value', $id, ['OLD_TAG'], 3600));

            $this->assertTrue($frontend->save('same-value', $id, ['NEW_TAG'], 3600));
            $this->assertSame('same-value', $frontend->load($id));

            $this->assertTrue(
                $frontend->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, ['OLD_TAG'])
            );
            $this->assertSame('same-value', $frontend->load($id));

            $this->assertTrue(
                $frontend->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, ['NEW_TAG'])
            );
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
