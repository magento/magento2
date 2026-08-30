<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Verifies behavior when the remote tier is unavailable.
 */
class RemoteFailureTest extends CacheFrontendTestCase
{
    /**
     * @return array<string, array{string, array<string, mixed>}> 
     */
    public static function l1L2ConfigurationProvider(): array
    {
        return array_filter(
            CacheConfigurationProvider::provide(),
            static fn(array $case): bool => str_ends_with($case[0], '-l1-l2')
        );
    }

    #[DataProvider('l1L2ConfigurationProvider')]
    public function testUnavailableRemoteFailsFrontendCreation(
        string $configurationName,
        array $configuration
    ): void {
        $configuration['backend_options']['remote_backend_options']['port'] = 6399;
        $threw = false;
        $result = null;
        try {
            $frontend = $this->createFrontend($configuration, $configurationName, 'IT_REMOTE_FAILURE');
            try {
                $result = $frontend->load($this->cacheId($configurationName, 'remote_failure', 'integration'));
            } catch (\Throwable) {
                $threw = true;
            }
        } catch (\Throwable) {
            $threw = true;
        }

        if ($configurationName === 'symfony-l1-l2') {
            $this->assertTrue($threw, 'Symfony must detect the unavailable Redis connection');
        } else {
            $this->assertTrue(
                $threw || $result === false,
                'Legacy must throw or return a cache miss when Redis is unavailable'
            );
        }
    }
}
