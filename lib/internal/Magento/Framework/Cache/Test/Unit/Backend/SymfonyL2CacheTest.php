<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\SymfonyL2Cache;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Exception\CacheException;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for SymfonyL2Cache
 */
class SymfonyL2CacheTest extends TestCase
{
    /**
     * @var FrontendInterface|MockObject
     */
    private $localCacheMock;

    /**
     * @var FrontendInterface|MockObject
     */
    private $remoteCacheMock;

    /**
     * @var SymfonyL2Cache
     */
    private $cache;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->localCacheMock = $this->createMock(FrontendInterface::class);
        $this->remoteCacheMock = $this->createMock(FrontendInterface::class);
    }

    /**
     * Test that cache can be initialized with valid options
     *
     * @return void
     */
    public function testConstructorWithValidOptions(): void
    {
        $cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true, 'cleanup_percentage' => 90]
        );

        $this->assertInstanceOf(SymfonyL2Cache::class, $cache);
    }

    /**
     * Test that exception is thrown with invalid cleanup percentage
     *
     * @return void
     */
    public function testConstructorWithInvalidCleanupPercentage(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('cleanup_percentage must be between 1 and 100');

        new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['cleanup_percentage' => 150]
        );
    }

    /**
     * Test load when data exists in local cache and hash matches
     *
     * @return void
     */
    public function testLoadWithValidLocalCache(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $cacheId = 'test_id';
        $cacheData = 'test_data';
        $cacheHash = hash('sha256', $cacheData);

        // Local cache has data (called twice: once for data, once for invalid marker check)
        $this->localCacheMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnCallback(function ($id) use ($cacheId, $cacheData) {
                if ($id === $cacheId) {
                    return $cacheData;
                }
                if ($id === '__invalid::' . $cacheId) {
                    return false; // Not marked as invalid
                }
                return false;
            });

        // Remote hash matches local hash
        $this->remoteCacheMock->expects($this->once())
            ->method('load')
            ->with($cacheId . ':hash')
            ->willReturn($cacheHash);

        $result = $this->cache->load($cacheId);

        $this->assertEquals($cacheData, $result);
    }

    /**
     * Test load when local cache is stale and remote has fresh data
     *
     * @return void
     */
    public function testLoadWithStaleLocalCache(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => false]
        );

        $cacheId = 'test_id';
        $localData = 'old_data';
        $remoteData = 'new_data';
        $remoteHash = hash('sha256', $remoteData);

        // Local cache has old data
        $this->localCacheMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnCallback(function ($id) use ($cacheId, $localData) {
                if ($id === $cacheId) {
                    return $localData;
                }
                return false; // invalid marker check
            });

        // Remote hash is different
        $this->remoteCacheMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnMap([
                [$cacheId . ':hash', $remoteHash],
                [$cacheId, $remoteData]
            ]);

        // Should save fresh data to local cache
        $this->localCacheMock->expects($this->once())
            ->method('save')
            ->with($remoteData, $cacheId);

        $result = $this->cache->load($cacheId);

        $this->assertEquals($remoteData, $result);
    }

    /**
     * Test save when remote is available
     *
     * @return void
     */
    public function testSaveWhenRemoteAvailable(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $cacheId = 'test_id';
        $cacheData = 'test_data';
        $cacheHash = hash('sha256', $cacheData);

        // Remote save succeeds data first then hash
        $this->remoteCacheMock->expects($this->exactly(2))
            ->method('save')
            ->willReturnMap([
                [$cacheData, $cacheId, [], null, true],
                [$cacheHash, $cacheId . ':hash', [], null, true]
            ]);

        // Local save
        $this->localCacheMock->expects($this->once())
            ->method('save')
            ->with($cacheData, $cacheId, [], null);

        // Should clear invalid marker (mark as valid)
        $this->localCacheMock->expects($this->once())
            ->method('remove')
            ->with('__invalid::' . $cacheId);

        $result = $this->cache->save($cacheData, $cacheId);

        $this->assertTrue($result);
    }

    /**
     * Test save when remote is unavailable and stale cache is enabled
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSaveWhenRemoteUnavailableWithStaleCache(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $cacheId = 'test_id';
        $cacheData = 'test_data';

        // Remote save fails (returns false)
        $this->remoteCacheMock->expects($this->once())
            ->method('save')
            ->willReturn(false);

        // Local save (called twice: once for data, once for invalid marker)
        $this->localCacheMock->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function ($data, $id, $_tags = [], $lifetime = null) use ($cacheData, $cacheId) {
                // First call: save data
                if ($data === $cacheData && $id === $cacheId) {
                    return true;
                }
                // Second call: mark as invalid
                if ($data === '1' && $id === '__invalid::' . $cacheId && $lifetime === 86400) {
                    return true;
                }
                return false;
            });

        $result = $this->cache->save($cacheData, $cacheId);

        $this->assertFalse($result);
    }

    /**
     * Test load when key is marked as invalid
     *
     * @return void
     */
    public function testLoadWithInvalidKeyMarker(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $cacheId = 'test_id';

        // Local cache returns data
        $this->localCacheMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnCallback(function ($id) use ($cacheId) {
                if ($id === $cacheId) {
                    return 'old_data';
                }
                if ($id === '__invalid::' . $cacheId) {
                    return '1'; // Invalid marker exists
                }
                return false;
            });

        // Should clean from remote (called twice)
        $this->remoteCacheMock->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function ($id) use ($cacheId) {
                return $id === $cacheId . ':hash' || $id === $cacheId;
            });

        // Should remove from local cache and clear invalid marker
        $this->localCacheMock->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function ($id) use ($cacheId) {
                return $id === $cacheId || $id === '__invalid::' . $cacheId;
            });

        $result = $this->cache->load($cacheId);

        $this->assertFalse($result);
    }

    /**
     * Test remove when remote is available
     *
     * @return void
     */
    public function testRemoveWhenRemoteAvailable(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => false]
        );

        $cacheId = 'test_id';

        // Remote remove succeeds
        $this->remoteCacheMock->expects($this->exactly(2))
            ->method('remove')
            ->willReturn(true);

        // Local remove (stale cache disabled, called twice: data + invalid marker)
        $this->localCacheMock->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function ($id) use ($cacheId) {
                return $id === $cacheId || $id === '__invalid::' . $cacheId;
            });

        $result = $this->cache->remove($cacheId);

        $this->assertTrue($result);
    }

    /**
     * Test remove when remote is unavailable with stale cache
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testRemoveWhenRemoteUnavailableWithStaleCache(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $cacheId = 'test_id';

        // Remote remove fails
        $this->remoteCacheMock->expects($this->exactly(2))
            ->method('remove')
            ->willReturn(false);

        // Should mark as invalid
        $this->localCacheMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($data, $id, $_tags = [], $lifetime = null) use ($cacheId) {
                return $data === '1' && $id === '__invalid::' . $cacheId && $lifetime === 86400;
            });

        $result = $this->cache->remove($cacheId);

        $this->assertFalse($result);
    }

    /**
     * Test clean operation
     *
     * @return void
     */
    public function testClean(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $mode = CacheConstants::CLEANING_MODE_ALL;
        $tags = ['tag1', 'tag2'];

        // Local clean
        $this->localCacheMock->expects($this->once())
            ->method('clean')
            ->with($mode, $tags);

        // Remote clean
        $this->remoteCacheMock->expects($this->once())
            ->method('clean')
            ->with($mode, $tags)
            ->willReturn(true);

        $result = $this->cache->clean($mode, $tags);

        $this->assertTrue($result);
    }

    /**
     * Test clean when remote is unavailable
     *
     * @return void
     */
    public function testCleanWhenRemoteUnavailable(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        // Local clean
        $this->localCacheMock->expects($this->once())
            ->method('clean');

        // Remote clean fails
        $this->remoteCacheMock->expects($this->once())
            ->method('clean')
            ->willReturn(false);

        $result = $this->cache->clean();

        $this->assertFalse($result);
    }

    /**
     * Test load returns stale data when remote unavailable and stale cache enabled
     *
     * @return void
     */
    public function testLoadReturnsStaleDataWhenRemoteUnavailable(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $cacheId = 'test_id';
        $localData = 'stale_data';

        // Local cache has data
        $this->localCacheMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnCallback(function ($id) use ($cacheId, $localData) {
                if ($id === $cacheId) {
                    return $localData;
                }
                return false; // invalid marker check
            });

        // Remote is unavailable (returns false)
        $this->remoteCacheMock->expects($this->once())
            ->method('load')
            ->with($cacheId . ':hash')
            ->willReturn(false);

        $result = $this->cache->load($cacheId);

        // Should return stale data
        $this->assertEquals($localData, $result);
    }

    /**
     * Test test() method
     *
     * @return void
     */
    public function testTestMethod(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => false]
        );

        $cacheId = 'test_id';

        $this->remoteCacheMock->expects($this->once())
            ->method('test')
            ->with($cacheId)
            ->willReturn(123456789);

        $result = $this->cache->test($cacheId);

        $this->assertEquals(123456789, $result);
    }

    /**
     * Test test() method with stale cache enabled
     *
     * @return void
     */
    public function testTestMethodWithStaleCache(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock,
            ['use_stale_cache' => true]
        );

        $cacheId = 'test_id';

        // Local test returns timestamp
        $this->localCacheMock->expects($this->once())
            ->method('test')
            ->with($cacheId)
            ->willReturn(123456789);

        $result = $this->cache->test($cacheId);

        $this->assertEquals(123456789, $result);
    }

    /**
     * Test getCapabilities
     *
     * @return void
     */
    public function testGetCapabilities(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock
        );

        $capabilities = $this->cache->getCapabilities();

        $this->assertIsArray($capabilities);
        $this->assertArrayHasKey('tags', $capabilities);
        $this->assertTrue($capabilities['tags']);
    }

    /**
     * Test getRemote and getLocal methods
     *
     * @return void
     */
    public function testGetRemoteAndGetLocal(): void
    {
        $this->cache = new SymfonyL2Cache(
            $this->remoteCacheMock,
            $this->localCacheMock
        );

        $this->assertSame($this->remoteCacheMock, $this->cache->getRemote());
        $this->assertSame($this->localCacheMock, $this->cache->getLocal());
    }
}
