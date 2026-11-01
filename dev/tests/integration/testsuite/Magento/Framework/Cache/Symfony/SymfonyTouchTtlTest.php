<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies Symfony L1/L2 touch() extends the remaining lifetime.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SymfonyTouchTtlTest extends CacheFrontendTestCase
{
    public function testTouchExtendsRemainingLifetime(): void
    {
        $configuration = CacheConfigurationProvider::provide()['symfony-l1-l2'];
        $frontend = $this->createFrontend($configuration[1], $configuration[0], 'TOUCH');
        $id = $this->cacheId('symfony-l1-l2', 'touch', 'integration_touch_ttl');
        $backend = $frontend->getBackend();

        try {
            $this->assertTrue($frontend->save('touch-value', $id, [], 30));
            $before = $backend->getMetadatas($id);
            $this->assertIsArray($before);
            $this->assertIsInt($before['expire']);

            sleep(2);
            $this->assertTrue($backend->touch($id, 20));

            $after = $backend->getMetadatas($id);
            $this->assertIsArray($after);
            $this->assertIsInt($after['expire']);
            $this->assertGreaterThanOrEqual($before['expire'] + 17, $after['expire']);
            $this->assertSame('touch-value', $frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
