<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * Exercises Redis cache frontends over a preconfigured Unix domain socket.
 *
 * Set MAGENTO_TEST_REDIS_SOCKET to an absolute socket path to enable this test.
 *
 * @magentoAppIsolation enabled
 */
class SymfonyRedisUnixSocketTest extends TestCase
{
    /**
     * @param string $socketFormat
     * @param bool $persistent
     */
    #[DataProvider('socketConfigurationDataProvider')]
    public function testCacheFrontendsUseSocketAndKeepDatabasesIsolated(
        string $socketFormat,
        bool $persistent
    ): void {
        $this->runSocketConfiguration($socketFormat, $persistent);
    }

    private function runSocketConfiguration(string $socketFormat, bool $persistent): void
    {
        $socketPath = $this->getSocketPath();
        $server = $this->formatSocketServer($socketFormat, $socketPath);
        $provider = $this->createProvider();
        $namespace = 'acp2e5506_' . bin2hex(random_bytes(8));
        $key = 'same_key_for_both_databases';
        $frontends = $this->createFrontends($provider, $server, $persistent, $namespace);
        $this->exerciseAndCleanFrontends($frontends, $key);
    }

    private function formatSocketServer(string $socketFormat, string $socketPath): string
    {
        return $socketFormat === 'unix-uri' ? 'unix://' . $socketPath : $socketPath;
    }

    /**
     * @param array<string, CacheItemPoolInterface> $frontends
     * @param string $key
     * @return void
     */
    private function exerciseAndCleanFrontends(array $frontends, string $key): void
    {
        try {
            $this->assertDatabaseIsolation($frontends, $key);
        } finally {
            $this->removeCacheItems($frontends, $key);
        }
    }

    private function getSocketPath(): string
    {
        $socketPath = getenv('MAGENTO_TEST_REDIS_SOCKET');
        if (!is_string($socketPath) || $socketPath === '') {
            $this->markTestSkipped('Set MAGENTO_TEST_REDIS_SOCKET to run Unix socket Redis integration coverage.');
        }

        if (str_starts_with($socketPath, 'unix://')) {
            $socketPath = substr($socketPath, strlen('unix://'));
        }
        $this->assertStringStartsWith('/', $socketPath, 'Redis socket must be an absolute path.');
        $this->assertFileExists($socketPath, 'Redis Unix socket is not available.');

        return $socketPath;
    }

    private function createProvider(): SymfonyAdapterProvider
    {
        $objectManager = Bootstrap::getObjectManager();
        return $objectManager->create(
            SymfonyAdapterProvider::class,
            [
                'filesystem' => $objectManager->get(Filesystem::class),
                'resource' => $objectManager->get(ResourceConnection::class),
                'serializer' => $objectManager->get(SerializerInterface::class),
            ]
        );
    }

    /**
     * @param SymfonyAdapterProvider $provider
     * @param string $server
     * @param bool $persistent
     * @param string $namespace
     * @return array<string, CacheItemPoolInterface>
     */
    private function createFrontends(
        SymfonyAdapterProvider $provider,
        string $server,
        bool $persistent,
        string $namespace
    ): array {
        $password = getenv('MAGENTO_TEST_REDIS_PASSWORD');
        $frontends = [];
        foreach (['default' => 1, 'page_cache' => 2] as $frontendName => $database) {
            $frontend = $provider->createAdapter(
                'valkey',
                [
                    'server' => $server,
                    'port' => 0,
                    'database' => $database,
                    'password' => is_string($password) ? $password : '',
                    'persistent' => $persistent,
                ],
                $namespace,
                0
            );
            $this->assertInstanceOf(
                RedisAdapter::class,
                $frontend,
                'The Redis configuration must not fall back to a filesystem cache.'
            );
            $frontends[$frontendName] = $frontend;
        }

        return $frontends;
    }

    /**
     * @param array<string, CacheItemPoolInterface> $frontends
     * @param string $key
     * @return void
     */
    private function assertDatabaseIsolation(array $frontends, string $key): void
    {
        foreach (['default' => 'default-value', 'page_cache' => 'page-cache-value'] as $name => $value) {
            $item = $frontends[$name]->getItem($key);
            $item->set($value);
            $this->assertTrue($frontends[$name]->save($item));
        }

        $this->assertSame('default-value', $frontends['default']->getItem($key)->get());
        $this->assertSame('page-cache-value', $frontends['page_cache']->getItem($key)->get());
    }

    /**
     * @param array<string, CacheItemPoolInterface> $frontends
     * @param string $key
     * @return void
     */
    private function removeCacheItems(array $frontends, string $key): void
    {
        foreach ($frontends as $frontend) {
            $frontend->deleteItem($key);
        }
    }

    /**
     * @return array<string, array{0:string,1:bool}>
     */
    public static function socketConfigurationDataProvider(): array
    {
        return [
            'absolute socket with persistence' => ['absolute', true],
            'absolute socket without persistence' => ['absolute', false],
            'unix URL socket with persistence' => ['unix-uri', true],
            'unix URL socket without persistence' => ['unix-uri', false],
        ];
    }
}
