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
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Predis\Client;

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
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var SymfonyAdapterProvider
     */
    private SymfonyAdapterProvider $provider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->filesystem = $this->createStub(Filesystem::class);
        $this->resource = $this->createStub(ResourceConnection::class);
        $this->serializer = $this->createStub(SerializerInterface::class);

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
        $pool = $this->createStub(CacheItemPoolInterface::class);

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
        $pool = $this->createStub(CacheItemPoolInterface::class);

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
     * Socket paths and unix:// socket URLs must resolve to the underlying absolute path.
     *
     * @param string $server
     * @param string|null $expectedPath
     */
    #[DataProvider('unixSocketPathDataProvider')]
    public function testResolveUnixSocketPath(string $server, ?string $expectedPath): void
    {
        $this->assertSame(
            $expectedPath,
            $this->invokePrivateMethod('resolveUnixSocketPath', [$server])
        );
    }

    /**
     * @return array<string, array{0:string,1:string|null}>
     */
    public static function unixSocketPathDataProvider(): array
    {
        return [
            'absolute socket path' => ['/var/run/redis/redis.sock', '/var/run/redis/redis.sock'],
            'unix URL socket path' => ['unix:///var/run/redis/redis.sock', '/var/run/redis/redis.sock'],
            'TCP hostname' => ['redis.internal', null],
            'relative unix URL is not accepted' => ['unix://redis.sock', null],
        ];
    }

    /**
     * TCP DSNs preserve legacy encoding, while Unix socket DSNs use Symfony's documented format.
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
        string $expectedDsn
    ): void {
        $this->assertSame(
            $expectedDsn,
            $this->invokePrivateMethod('buildRedisBaseDsn', [$host, $port, $database, $password])
        );
    }

    /**
     * @return array<string, array{0:string,1:int,2:int,3:string|null,4:string}>
     */
    public static function redisBaseDsnDataProvider(): array
    {
        return [
            'TCP without auth' => [
                '127.0.0.1',
                6379,
                2,
                null,
                'redis://127.0.0.1:6379/2',
            ],
            'TCP auth encoding remains unchanged' => [
                'redis.internal',
                6380,
                1,
                'p@ss:w/rd 1',
                'redis://p%40ss%3Aw%2Frd+1@redis.internal:6380/1',
            ],
            'absolute socket ignores port' => [
                '/var/run/redis/redis.sock',
                6379,
                1,
                null,
                'redis:///var/run/redis/redis.sock/1',
            ],
            'unix URL socket with auth' => [
                'unix:///var/run/redis/redis.sock',
                0,
                2,
                'secret',
                'redis://secret@/var/run/redis/redis.sock/2',
            ],
        ];
    }

    /**
     * Predis transport options use Unix sockets without persistent IDs.
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
        array $expectedParams
    ): void {
        $this->assertSame(
            $expectedParams,
            $this->invokePrivateMethod(
                'buildPredisConnectionParameters',
                [$host, $port, $database, $password]
            )
        );
    }

    /**
     * @return array<string, array{0:string,1:int,2:int,3:string|null,4:array}>
     */
    public static function predisConnectionParametersDataProvider(): array
    {
        return [
            'TCP without auth' => [
                '127.0.0.1',
                6379,
                2,
                null,
                ['scheme' => 'tcp', 'host' => '127.0.0.1', 'port' => 6379, 'database' => 2],
            ],
            'TCP with auth' => [
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
            'absolute socket ignores port' => [
                '/var/run/redis/redis.sock',
                6379,
                1,
                null,
                ['scheme' => 'unix', 'path' => '/var/run/redis/redis.sock', 'database' => 1],
            ],
            'unix URL socket with auth' => [
                'unix:///var/run/redis/redis.sock',
                0,
                2,
                'secret',
                [
                    'scheme' => 'unix',
                    'path' => '/var/run/redis/redis.sock',
                    'database' => 2,
                    'password' => 'secret',
                ],
            ],
        ];
    }

    public function testPredisSocketConnectionDoesNotEnablePersistence(): void
    {
        if (!class_exists(Client::class)) {
            $this->markTestSkipped('Predis is not installed.');
        }

        $client = $this->invokePrivateMethod(
            'createOptimizedPredisConnection',
            ['/var/run/redis/redis.sock', 0, null, 1, true, null, 1.0, null]
        );
        $params = $client->getConnection()->getParameters()->toArray();

        $this->assertSame('unix', $params['scheme']);
        $this->assertSame('/var/run/redis/redis.sock', $params['path']);
        $this->assertSame(1, $params['database']);
        $this->assertArrayNotHasKey('persistent', $params);
        $this->assertArrayNotHasKey('conn_uid', $params);
    }

    public function testPredisTcpConnectionKeepsPersistence(): void
    {
        if (!class_exists(Client::class)) {
            $this->markTestSkipped('Predis is not installed.');
        }
        $client = $this->invokePrivateMethod(
            'createOptimizedPredisConnection',
            ['redis.internal', 6379, null, 1, true, 'cache', 1.0, null]
        );
        $params = $client->getConnection()->getParameters()->toArray();
        $this->assertSame('tcp', $params['scheme']);
        $this->assertSame('redis.internal', $params['host']);
        $this->assertSame(6379, $params['port']);
        $this->assertSame(1, $params['database']);
        $this->assertTrue($params['persistent']);
        $this->assertSame('cache:1:' . getmypid(), $params['conn_uid']);
    }

    public function testPredisSocketReplicaDoesNotEnablePersistence(): void
    {
        if (!class_exists(Client::class)) {
            $this->markTestSkipped('Predis is not installed.');
        }
        $client = $this->invokePrivateMethod(
            'createOptimizedPredisConnection',
            [
                '/var/run/redis/master.sock',
                0,
                null,
                1,
                true,
                null,
                1.0,
                null,
                [['unix:///var/run/redis/replica.sock', 0, 2]],
            ]
        );
        $replica = $client->getConnection()->getConnectionByRole('slave');
        $params = $replica->getParameters()->toArray();
        $this->assertSame('unix', $params['scheme']);
        $this->assertSame('/var/run/redis/replica.sock', $params['path']);
        $this->assertSame(2, $params['database']);
        $this->assertArrayNotHasKey('persistent', $params);
        $this->assertArrayNotHasKey('conn_uid', $params);
    }

    /**
     * Socket addresses must not be split at the colon in a unix:// URL.
     */
    #[DataProvider('socketReplicaDataProvider')]
    public function testSplitHostPortKeepsSocketAddress(
        string $server,
        string $expectedHost,
        int $expectedPort
    ): void {
        $this->assertSame(
            [$expectedHost, $expectedPort, 3],
            $this->invokePrivateMethod('splitHostPort', [$server, 6379, 3])
        );
    }

    /**
     * @return array<string, array{0:string,1:string,2:int}>
     */
    public static function socketReplicaDataProvider(): array
    {
        return [
            'absolute socket path' => ['/var/run/redis/replica.sock', '/var/run/redis/replica.sock', 6379],
            'unix URL socket path' => [
                'unix:///var/run/redis/replica.sock',
                'unix:///var/run/redis/replica.sock',
                6379,
            ],
            'TCP host and port' => ['redis.internal:6380', 'redis.internal', 6380],
        ];
    }

    /**
     * Invoke private provider methods without opening a connection to Redis.
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
