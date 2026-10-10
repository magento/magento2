<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\Backend\SymfonyL2Cache;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that an unchanged L1 hit does not read the cache payload from L2.
 */
class L1ReadInstrumentationTest extends TestCase
{
    public function testL1HitDoesNotReadRemoteDataKey(): void
    {
        $id = 'integration_l1_read_' . uniqid();
        $local = $this->createStub(FrontendInterface::class);
        $remote = $this->createStub(FrontendInterface::class);
        $remoteLoads = [];

        $local->method('load')->willReturnCallback(
            static fn(string $requestedId) => $requestedId === $id ? 'l1-value' : false
        );
        $remote->method('load')->willReturnCallback(function (string $requestedId) use (&$remoteLoads, $id) {
            $remoteLoads[] = $requestedId;
            return $requestedId === $id . ':hash' ? hash('sha256', 'l1-value') : false;
        });

        $backend = new SymfonyL2Cache($remote, $local);

        $this->assertSame('l1-value', $backend->load($id));
        $this->assertNotContains($id, $remoteLoads);
        $this->assertContains($id . ':hash', $remoteLoads);
    }
}
