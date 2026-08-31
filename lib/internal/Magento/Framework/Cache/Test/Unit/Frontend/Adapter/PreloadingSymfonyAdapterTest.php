<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\Cache\Frontend\Adapter\PreloadingSymfonyAdapter;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for the preloading wrapper of the Symfony cache adapter
 */
class PreloadingSymfonyAdapterTest extends TestCase
{
    /**
     * @var FrontendInterface&MockObject
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(FrontendInterface::class);
    }

    /**
     * Preloading is lazy (runs on first load()); trigger it directly for tests that only inspect stats.
     */
    private function triggerPreload(PreloadingSymfonyAdapter $adapter): void
    {
        (new \ReflectionMethod($adapter, 'ensurePreloaded'))->invoke($adapter);
    }

    /**
     * Preload keys that include the ID prefix (as documented) must be looked up with the logical
     * identifier so the underlying Symfony adapter, which applies the prefix itself, resolves them.
     */
    public function testPrefixedPreloadKeysAreLoadedWithLogicalIdentifier(): void
    {
        $this->adapter->expects($this->exactly(2))
            ->method('load')
            ->willReturnMap([
                ['EAV_ENTITY_TYPES', 'eav'],
                ['SYSTEM_DEFAULT', 'system'],
            ]);

        $adapter = new PreloadingSymfonyAdapter(
            $this->adapter,
            ['061_EAV_ENTITY_TYPES', '061_SYSTEM_DEFAULT'],
            '061_'
        );
        $this->triggerPreload($adapter);

        $stats = $adapter->getPreloadStats();
        $this->assertSame(2, $stats['preload_keys_configured']);
        $this->assertSame(2, $stats['preload_keys_cached']);
        $this->assertSame(['EAV_ENTITY_TYPES', 'SYSTEM_DEFAULT'], $stats['cached_keys']);
    }

    /**
     * A preloaded value is served from local memory without hitting the underlying adapter again.
     */
    public function testLoadServesPreloadedValueFromLocalCache(): void
    {
        $this->adapter->expects($this->once())
            ->method('load')
            ->with('EAV_ENTITY_TYPES')
            ->willReturn('eav');

        $adapter = new PreloadingSymfonyAdapter($this->adapter, ['061_EAV_ENTITY_TYPES'], '061_');

        $this->assertSame('eav', $adapter->load('EAV_ENTITY_TYPES'));
    }

    /**
     * Keys configured without the prefix (the previously documented workaround) keep working.
     */
    public function testUnprefixedPreloadKeysAreLoadedUnchanged(): void
    {
        $this->adapter->expects($this->once())
            ->method('load')
            ->with('EAV_ENTITY_TYPES')
            ->willReturn('eav');

        $adapter = new PreloadingSymfonyAdapter($this->adapter, ['EAV_ENTITY_TYPES'], '061_');
        $this->triggerPreload($adapter);

        $this->assertSame(['EAV_ENTITY_TYPES'], $adapter->getPreloadStats()['cached_keys']);
    }

    /**
     * Keys that are not cached in the backend are not stored locally.
     */
    public function testMissingKeysAreNotCachedLocally(): void
    {
        $this->adapter->expects($this->once())
            ->method('load')
            ->with('EAV_ENTITY_TYPES')
            ->willReturn(false);

        $adapter = new PreloadingSymfonyAdapter($this->adapter, ['061_EAV_ENTITY_TYPES'], '061_');
        $this->triggerPreload($adapter);

        $stats = $adapter->getPreloadStats();
        $this->assertSame(1, $stats['preload_keys_configured']);
        $this->assertSame(0, $stats['preload_keys_cached']);
    }

    /**
     * Writing a normalized preload key refreshes its local copy.
     */
    public function testSaveUpdatesLocalCacheForNormalizedPreloadKey(): void
    {
        $this->adapter->expects($this->once())
            ->method('load')
            ->with('EAV_ENTITY_TYPES')
            ->willReturn('old');
        $this->adapter->expects($this->once())
            ->method('save')
            ->with('new', 'EAV_ENTITY_TYPES', [], null)
            ->willReturn(true);

        $adapter = new PreloadingSymfonyAdapter($this->adapter, ['061_EAV_ENTITY_TYPES'], '061_');
        $this->assertSame('old', $adapter->load('EAV_ENTITY_TYPES'));

        $adapter->save('new', 'EAV_ENTITY_TYPES');

        $this->assertSame('new', $adapter->load('EAV_ENTITY_TYPES'));
    }
}
