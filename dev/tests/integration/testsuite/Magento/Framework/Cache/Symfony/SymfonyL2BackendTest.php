<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\Backend\BackendInterface;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies the Symfony L1/L2 backend wrapper through Magento's backend contract.
 */
class SymfonyL2BackendTest extends CacheFrontendTestCase
{
    public function testBackendWrapperStoresLoadsAndRemovesData(): void
    {
        $configuration = CacheConfigurationProvider::provide()['symfony-l1-l2'][1];
        $frontend = $this->createFrontend($configuration, 'symfony-l1-l2', 'SYMFONY_BACKEND');
        $backend = $frontend->getBackend();
        $id = $this->cacheId('symfony-l1-l2', 'backend_wrapper');

        try {
            $this->assertInstanceOf(BackendInterface::class, $backend);
            $this->assertTrue($frontend->save('symfony-backend-value', $id, [], 3600));
            $this->assertSame('symfony-backend-value', $frontend->load($id));
            $this->assertTrue($frontend->remove($id));
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
