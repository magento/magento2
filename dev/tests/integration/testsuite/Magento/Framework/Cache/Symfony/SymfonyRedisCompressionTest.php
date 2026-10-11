<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Verifies Symfony Redis compression round trips payloads without corruption.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SymfonyRedisCompressionTest extends CacheFrontendTestCase
{
    public function testCompressedPayloadsRoundTrip(): void
    {
        $configuration = CacheConfigurationProvider::provide()['symfony-redis'][1];
        $configuration['backend_options']['compress_data'] = 1;
        $configuration['backend_options']['compression_lib'] = 'gzip';
        $frontend = $this->createFrontend($configuration, 'symfony-redis', 'IT_SYMFONY_COMP');

        $serializer = Bootstrap::getObjectManager()->get(\Magento\Framework\Serialize\SerializerInterface::class);
        $payloads = [
            'small' => 'hello world',
            'large' => str_repeat('The quick brown fox. ', 8000),
            'binary' => random_bytes(50000),
            'serialized' => base64_encode($serializer->serialize([
                'values' => range(1, 500),
                'text' => str_repeat('x', 2000),
            ])),
            'unicode' => str_repeat('héllo—wörld✓ ', 2000),
        ];

        foreach ($payloads as $name => $payload) {
            $id = $this->cacheId('symfony-redis', 'compression_' . $name, 'integration');
            try {
                $this->assertTrue($frontend->save($payload, $id, [], 3600), $name);
                $this->assertSame($payload, $frontend->load($id), $name);
            } finally {
                $frontend->remove($id);
            }
        }
    }
}
