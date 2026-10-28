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
 * Verifies behavior when the remote tier is unavailable.
 */
class RemoteFailureTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'l1L2Configurations')]
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

        $this->assertTrue(
            $threw || $result === false,
            $configurationName . ' must throw or return a cache miss when Redis is unavailable'
        );
    }
}
