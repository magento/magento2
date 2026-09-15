<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RegenerationLockTest extends CacheFrontendTestCase
{
    public static function configurations(): array
    {
        return ['symfony-l1-l2' => [
            'symfony-l1-l2',
            CacheConfigurationProvider::provide()['symfony-l1-l2'][1],
        ]];
    }

    #[DataProvider('configurations')]
    public function testLockIsReleasedAfterRegeneration(string $name, array $configuration): void
    {
        $configuration['backend_options']['use_stale_cache'] = true;
        $frontend = $this->createFrontend($configuration, $name, 'IT_REGEN_LOCK');
        $id = $this->cacheId($name, 'regeneration', 'integration');
        $remote = $frontend->getBackend()->getRemote();
        $invalidate = static function () use ($remote, $id): void {
            $remote->remove($id);
            $remote->remove($id . ':hash');
        };
        try {
            $this->assertTrue($frontend->save('value-1', $id, ['INTEGRATION_REGEN_LOCK'], 3600));
            $invalidate();
            $this->assertFalse($frontend->load($id));
            $this->assertTrue($frontend->save('value-2', $id, ['INTEGRATION_REGEN_LOCK'], 3600));
            $invalidate();
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
