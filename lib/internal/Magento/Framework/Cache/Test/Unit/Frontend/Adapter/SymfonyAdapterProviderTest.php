<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Serialize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for SymfonyAdapterProvider Redis connection configuration
 */
class SymfonyAdapterProviderTest extends TestCase
{
    /**
     * @var SymfonyAdapterProvider
     */
    private SymfonyAdapterProvider $provider;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new SymfonyAdapterProvider(
            $this->createStub(Filesystem::class),
            $this->createStub(ResourceConnection::class),
            $this->createStub(Serialize::class)
        );
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
