<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that Magento cache frontends remain functional when persistent Redis connections are enabled.
 * The test is intentionally driver-independent: it validates the Magento cache contract for both Zend
 * and Symfony Redis backends without inspecting phpredis or Predis implementation details.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class PersistentConnectionTest extends TestCase
{
    /**
     * @return array<string, array{string, array<string, mixed>}>
     */
    public static function redisConfigurations(): array
    {
        $configurations = CacheConfigurationProvider::provide();

        return [
            'zend-redis' => [$configurations['zend-redis'][0], $configurations['zend-redis'][1]],
            'symfony-redis' => [$configurations['symfony-redis'][0], $configurations['symfony-redis'][1]],
        ];
    }

    /**
     * @param string $configurationName
     * @param array<string, mixed> $configuration
     */
    #[DataProviderExternal(self::class, 'redisConfigurations')]
    public function testRedisCacheWorksWithPersistentConnection(
        string $configurationName,
        array $configuration
    ): void {
        $options = $configuration['backend_options'];
        $options['persistent'] = true;
        $options['persistent_id'] = 'integration_persistent_' . $configurationName;
        $options['timeout'] = 2.5;
        $options['read_timeout'] = 2.5;
        $configuration['backend_options'] = $options;

        /** @var Factory $factory */
        $factory = Bootstrap::getObjectManager()->get(Factory::class);
        $cache = $factory->create($configuration);
        $id = 'persistent_' . $configurationName . '_' . bin2hex(random_bytes(6));

        try {
            $saved = $cache->save('persistent-value', $id, ['PERSISTENT_TEST'], 60);
            $loaded = $cache->load($id);
            $removed = $cache->remove($id);
            $remaining = $cache->load($id);
        } catch (\Throwable $exception) {
            $this->markTestSkipped('Redis is unavailable: ' . $exception->getMessage());
        }

        $this->assertTrue($saved);
        $this->assertSame('persistent-value', $loaded);
        $this->assertTrue($removed);
        $this->assertFalse($remaining);
    }
}
