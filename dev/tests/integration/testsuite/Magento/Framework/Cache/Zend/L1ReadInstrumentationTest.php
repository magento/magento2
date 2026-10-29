<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use PHPUnit\Framework\TestCase;

/**
 * Verifies the legacy L1-first read path for the Zend L1/L2 backend.
 */
class L1ReadInstrumentationTest extends TestCase
{
    public function testL1HitDoesNotReadRemoteDataKey(): void
    {
        $id = 'integration_zend_l1_read_' . uniqid();
        $remoteLoads = [];
        $remote = $this->createStub(\Zend_Cache_Backend_ExtendedInterface::class);
        $local = $this->createStub(\Zend_Cache_Backend_ExtendedInterface::class);

        $local->method('load')->willReturnCallback(
            static fn(string $requestedId) => $requestedId === $id ? 'l1-value' : false
        );
        $remote->method('load')->willReturnCallback(
            function (string $requestedId) use (&$remoteLoads, $id) {
                $remoteLoads[] = $requestedId;
                return $requestedId === $id . ':hash' ? hash('sha256', 'l1-value') : false;
            }
        );

        $backend = new \Magento\Framework\Cache\Backend\RemoteSynchronizedCache([
            'remote_backend' => $remote,
            'local_backend' => $local,
        ]);

        $this->assertSame('l1-value', $backend->load($id));
        $this->assertNotContains($id, $remoteLoads);
        $this->assertContains($id . ':hash', $remoteLoads);
    }
}
