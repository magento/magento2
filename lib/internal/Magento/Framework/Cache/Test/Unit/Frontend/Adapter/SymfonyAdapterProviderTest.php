<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\FilesystemTagAdapter;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use Magento\Framework\DB\Adapter\AdapterInterface as DbAdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Exception\CacheException;

/**
 * Test for legacy class-name backend resolution in the Symfony cache adapter factory
 */
class SymfonyAdapterProviderTest extends TestCase
{
    /**
     * @var Filesystem&MockObject
     */
    private $filesystem;

    /**
     * @var ResourceConnection&MockObject
     */
    private $resource;

    /**
     * @var Serialize&MockObject
     */
    private $serializer;

    /**
     * @var SymfonyAdapterProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $cacheDirectory = $this->createMock(ReadInterface::class);
        $cacheDirectory->method('getAbsolutePath')->willReturn(sys_get_temp_dir() . '/symfony-adapter-provider-test');
        $this->filesystem->method('getDirectoryRead')->with(DirectoryList::CACHE)->willReturn($cacheDirectory);

        $this->resource = $this->createMock(ResourceConnection::class);
        $this->serializer = $this->createMock(Serialize::class);

        $this->provider = new SymfonyAdapterProvider($this->filesystem, $this->resource, $this->serializer);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function redisAliasProvider(): array
    {
        return [
            'redis short alias' => ['redis'],
            'redis class name' => ['Magento\Framework\Cache\Backend\Redis'],
            'valkey short alias' => ['valkey'],
            'valkey class name' => ['Magento\Framework\Cache\Backend\Valkey'],
            'legacy Cm_Cache_Backend_Redis class name' => ['Cm_Cache_Backend_Redis'],
        ];
    }

    /**
     * A legacy class-name backend must resolve to the same tag adapter as its short alias.
     */
    #[DataProvider('redisAliasProvider')]
    public function testRedisBackendAliasesProduceRedisTagAdapter(string $backendType): void
    {
        $cachePool = new RedisAdapter(new \Redis());

        $tagAdapter = $this->provider->createTagAdapter($backendType, $cachePool);

        $this->assertInstanceOf(RedisTagAdapter::class, $tagAdapter);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function memcachedAliasProvider(): array
    {
        return [
            'memcached short alias' => ['memcached'],
            'memcached class name' => ['Magento\Framework\Cache\Backend\Memcached'],
        ];
    }

    /**
     * Memcached adapter creation requires the ext-memcached extension, which is not present in the
     * unit test environment; both the short alias and the class name must fail identically, so
     * parity is asserted on the exception Symfony raises before any socket is touched.
     */
    #[DataProvider('memcachedAliasProvider')]
    public function testMemcachedBackendAliasesFailIdentically(string $backendType): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Memcached > 3.1.5 is required.');

        $this->provider->createAdapter($backendType, []);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function databaseAliasProvider(): array
    {
        return [
            'database short alias' => ['database'],
            'database class name' => ['Magento\Framework\Cache\Backend\Database'],
        ];
    }

    /**
     * A resolved (non-filesystem) backend is wrapped in TagAwareAdapter; an unmapped backend falls
     * back to a bare FilesystemAdapter.
     */
    #[DataProvider('databaseAliasProvider')]
    public function testDatabaseBackendAliasesProduceTagAwareAdapter(string $backendType): void
    {
        $dbAdapter = $this->createMock(DbAdapterInterface::class);
        $this->resource->method('getConnection')->willReturn($dbAdapter);
        $this->resource->method('getTableName')->willReturnMap([
            ['cache', 'cache'],
            ['cache_tag', 'cache_tag'],
        ]);

        $adapter = $this->provider->createAdapter($backendType, []);

        $this->assertInstanceOf(TagAwareAdapter::class, $adapter);
    }

    /**
     * An unknown backend type resolves to a bare FilesystemAdapter.
     */
    public function testUnknownBackendTypeStillResolvesToFilesystemAdapter(): void
    {
        $adapter = $this->provider->createAdapter('Some\Unmapped\Backend\ClassName', []);

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    /**
     * The tag adapter side of an unknown backend type must also keep resolving to the filesystem
     * tag adapter, not the Redis one.
     */
    public function testUnknownBackendTypeStillResolvesToFilesystemTagAdapter(): void
    {
        $cachePool = $this->createMock(CacheItemPoolInterface::class);

        $tagAdapter = $this->provider->createTagAdapter('Some\Unmapped\Backend\ClassName', $cachePool);

        $this->assertInstanceOf(FilesystemTagAdapter::class, $tagAdapter);
    }
}
