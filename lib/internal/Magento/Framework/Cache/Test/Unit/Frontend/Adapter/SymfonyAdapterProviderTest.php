<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\GenericTagAdapter;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Unit test for SymfonyAdapterProvider.
 *
 * Covers the backend-type resolution and the non-Redis adapter/tag-adapter selection logic.
 * Redis/Valkey/Memcached/APCu paths require the corresponding PHP extensions or a live server and
 * are exercised by integration tests instead.
 */
class SymfonyAdapterProviderTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var SymfonyAdapterProvider
     */
    private SymfonyAdapterProvider $provider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->provider = new SymfonyAdapterProvider(
            $this->filesystem,
            $this->resource,
            $this->serializer
        );
    }

    /**
     * The file backend must produce a Symfony FilesystemAdapter (a PSR-6 pool), returned as-is
     * because it has native tag support.
     */
    public function testCreateAdapterReturnsFilesystemAdapterForFileBackend(): void
    {
        $adapter = $this->provider->createAdapter(
            'file',
            ['cache_dir' => sys_get_temp_dir()],
            'unit_test_',
            0
        );

        $this->assertInstanceOf(CacheItemPoolInterface::class, $adapter);
        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    /**
     * An unknown backend type falls back to the filesystem adapter (no silent second guess).
     */
    public function testCreateAdapterFallsBackToFilesystemForUnknownBackend(): void
    {
        $adapter = $this->provider->createAdapter(
            'totally-unknown-backend',
            ['cache_dir' => sys_get_temp_dir()],
            'unit_test_',
            0
        );

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    /**
     * Backend type resolution must be case-insensitive.
     */
    public function testCreateAdapterResolvesBackendTypeCaseInsensitively(): void
    {
        $adapter = $this->provider->createAdapter(
            'FILE',
            ['cache_dir' => sys_get_temp_dir()],
            'unit_test_',
            0
        );

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    /**
     * Non-Redis / non-Filesystem backends use the generic tag adapter.
     *
     * @param string $backendType
     */
    #[DataProvider('genericTagAdapterBackendProvider')]
    public function testCreateTagAdapterReturnsGenericForNonSpecializedBackends(string $backendType): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);

        $adapter = $this->provider->createTagAdapter($backendType, $pool, 'unit_test_');

        $this->assertInstanceOf(GenericTagAdapter::class, $adapter);
    }

    /**
     * @return array<string, array{0:string}>
     */
    public static function genericTagAdapterBackendProvider(): array
    {
        return [
            'database' => ['database'],
            'apcu' => ['apcu'],
            'memcached' => ['memcached'],
            'two levels' => ['two_levels'],
        ];
    }

    /**
     * createTagAdapter always returns a TagAdapterInterface implementation.
     */
    public function testCreateTagAdapterAlwaysReturnsTagAdapterInterface(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);

        $adapter = $this->provider->createTagAdapter(
            'file',
            $pool,
            'unit_test_',
            false,
            ['cache_dir' => sys_get_temp_dir()]
        );

        $this->assertInstanceOf(TagAdapterInterface::class, $adapter);
    }

    /**
     * _resetState() must clear pooled state without raising errors so it is safe to call between
     * Application Server requests.
     */
    public function testResetStateDoesNotThrow(): void
    {
        $this->assertNull($this->provider->_resetState());
    }

    /**
     * Test base DSN construction for TCP hosts and unix socket paths
     *
     * @param string $host
     * @param int $port
     * @param int $database
     * @param string|null $password
     * @param string $expectedDsn
     */
    #[DataProvider('redisBaseDsnDataProvider')]
    public function testBuildRedisBaseDsn(
        string $host,
        int $port,
        int $database,
        ?string $password,
        string $expectedDsn,
    ): void {
        $dsn = $this->invokePrivateMethod(
            'buildRedisBaseDsn',
            [$host, $port, $database, $password]
        );

        $this->assertSame($expectedDsn, $dsn);
    }

    /**
     * Data provider for testBuildRedisBaseDsn
     *
     * @return array
     */
    public static function redisBaseDsnDataProvider(): array
    {
        return [
            'tcp host without auth' => [
                '127.0.0.1',
                6379,
                2,
                null,
                'redis://127.0.0.1:6379/2',
            ],
            'tcp host with auth' => [
                'redis.internal',
                6380,
                1,
                'secret',
                'redis://secret@redis.internal:6380/1',
            ],
            'tcp host with empty password' => [
                '127.0.0.1',
                6379,
                0,
                '',
                'redis://127.0.0.1:6379/0',
            ],
            'password with reserved characters is url-encoded' => [
                '127.0.0.1',
                6379,
                0,
                'p@ss:w/rd 1',
                'redis://p%40ss%3Aw%2Frd%201@127.0.0.1:6379/0',
            ],
            'unix socket without auth' => [
                '/var/run/redis/redis.sock',
                0,
                1,
                null,
                'redis:///var/run/redis/redis.sock/1',
            ],
            'unix socket with auth' => [
                '/var/run/redis/redis.sock',
                0,
                3,
                'secret',
                'redis://secret@/var/run/redis/redis.sock/3',
            ],
            'unix socket ignores configured port' => [
                '/var/run/redis-multi.redis/redis.sock',
                6379,
                0,
                null,
                'redis:///var/run/redis-multi.redis/redis.sock/0',
            ],
        ];
    }

    /**
     * Test Predis connection parameters for TCP hosts and unix socket paths
     *
     * @param string $host
     * @param int $port
     * @param int $database
     * @param string|null $password
     * @param array $expectedParams
     */
    #[DataProvider('predisConnectionParametersDataProvider')]
    public function testBuildPredisConnectionParameters(
        string $host,
        int $port,
        int $database,
        ?string $password,
        array $expectedParams,
    ): void {
        $params = $this->invokePrivateMethod(
            'buildPredisConnectionParameters',
            [$host, $port, $database, $password]
        );

        $this->assertSame($expectedParams, $params);
    }

    /**
     * Data provider for testBuildPredisConnectionParameters
     *
     * @return array
     */
    public static function predisConnectionParametersDataProvider(): array
    {
        return [
            'tcp host without auth' => [
                '127.0.0.1',
                6379,
                2,
                null,
                [
                    'scheme' => 'tcp',
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'database' => 2,
                ],
            ],
            'tcp host with auth' => [
                'redis.internal',
                6380,
                1,
                'secret',
                [
                    'scheme' => 'tcp',
                    'host' => 'redis.internal',
                    'port' => 6380,
                    'database' => 1,
                    'password' => 'secret',
                ],
            ],
            'unix socket without auth' => [
                '/var/run/redis/redis.sock',
                0,
                1,
                null,
                [
                    'scheme' => 'unix',
                    'path' => '/var/run/redis/redis.sock',
                    'database' => 1,
                ],
            ],
            'unix socket with auth' => [
                '/var/run/redis/redis.sock',
                0,
                0,
                'secret',
                [
                    'scheme' => 'unix',
                    'path' => '/var/run/redis/redis.sock',
                    'database' => 0,
                    'password' => 'secret',
                ],
            ],
        ];
    }

    /**
     * Invoke a private method on the provider under test
     *
     * @param string $methodName
     * @param array $arguments
     * @return mixed
     */
    private function invokePrivateMethod(string $methodName, array $arguments): mixed
    {
        $method = new \ReflectionMethod($this->provider, $methodName);

        return $method->invokeArgs($this->provider, $arguments);
    }
}
