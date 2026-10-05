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

class PreloadTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'redisConfigurations')]
    public function testConfiguredKeysAreAvailableToASeparateFrontend(
        string $configurationName,
        array $configuration
    ): void {
        $key = $this->cacheId($configurationName, 'preload');
        $configuration['backend_options']['preload_keys'] = [$key];
        $writer = $this->createFrontend($configuration, $configurationName, 'COMMON_PRELOAD');
        $reader = $this->createFrontend($configuration, $configurationName, 'COMMON_PRELOAD');

        try {
            $this->assertTrue($writer->save('preloaded-value', $key));
            $this->assertSame('preloaded-value', $reader->load($key));
        } finally {
            $writer->remove($key);
            $reader->remove($key);
        }
    }
}
