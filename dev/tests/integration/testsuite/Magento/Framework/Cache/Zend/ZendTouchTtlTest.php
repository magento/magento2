<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies legacy RemoteSynchronizedCache touch() extends the remaining lifetime.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ZendTouchTtlTest extends CacheFrontendTestCase
{
    public function testTouchExtendsRemainingLifetime(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-l1-l2'];
        $frontend = $this->createFrontend($configuration[1], $configuration[0], 'TOUCH');
        $backend = $frontend->getBackend();
        $id = $this->cacheId('zend-l1-l2', 'touch', 'integration_touch_ttl');

        try {
            // Test the legacy backend directly, matching RemoteSynchronizedCache's native API.
            $this->assertTrue($backend->save('touch-value', $id, [], 30));
            $before = $backend->getMetadatas($id);
            $this->assertIsArray($before);
            $this->assertIsInt($before['expire']);

            sleep(2);
            $this->assertTrue($backend->touch($id, 20));

            $after = $backend->getMetadatas($id);
            $this->assertIsArray($after);
            $this->assertIsInt($after['expire']);
            $this->assertGreaterThanOrEqual($before['expire'] + 17, $after['expire']);
            $this->assertSame('touch-value', $backend->load($id));
        } finally {
            $backend->remove($id);
        }
    }
}
