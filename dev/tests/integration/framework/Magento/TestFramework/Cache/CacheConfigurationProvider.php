<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Cache;

use Magento\Framework\App\DeploymentConfig;
use Magento\TestFramework\Helper\Bootstrap;

final class CacheConfigurationProvider
{
    /**
     * @return array<string, array{string, array<string, mixed>}>
     */
    public static function provide(): array
    {
        return [
            'zend-file' => ['zend-file', [
                'backend' => 'Cm_Cache_Backend_File',
                'backend_options' => [
                    'cache_dir' => BP . '/var/cache/zend_tests_files',
                ],
            ]],
            'zend-redis' => ['zend-redis', [
                'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => self::redisOptions(self::getRedisServer()),
            ]],
            'zend-l1-l2' => ['zend-l1-l2', [
                'backend' => 'Magento\\Framework\\Cache\\Backend\\RemoteSynchronizedCache',
                'backend_options' => [
                    'remote_backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
                    'remote_backend_options' => self::redisOptions(self::getRedisServer()),
                    'local_backend' => 'Cm_Cache_Backend_File',
                    'local_backend_options' => [
                        'cache_dir' => '/dev/shm/zend_tests_l1',
                    ],
                ],
            ]],
            'symfony-file' => ['symfony-file', [
                'backend' => 'file',
                'backend_options' => [
                    'cache_dir' => BP . '/var/cache/symfony_tests_files',
                    'index_tags' => true,
                ],
            ]],
            'symfony-redis' => ['symfony-redis', [
                'backend' => 'redis',
                'backend_options' => self::redisOptions(self::getRedisServer()),
            ]],
            'symfony-l1-l2' => ['symfony-l1-l2', [
                'backend' => 'symfony_l2',
                'backend_options' => [
                    'remote_backend' => 'redis',
                    'remote_backend_options' => self::redisOptions(self::getRedisServer()),
                    'local_backend' => 'file',
                    'local_backend_options' => [
                        'cache_dir' => '/dev/shm/test_magento_l1',
                        'index_tags' => true,
                    ],
                ],
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function getRedisServer(): string
    {
        try {
            /** @var DeploymentConfig $deploymentConfig */
            $deploymentConfig = Bootstrap::getObjectManager()->get(DeploymentConfig::class);
            $server = $deploymentConfig->get('cache/frontend/default/backend_options/server');
            if (is_string($server) && $server !== '') {
                return $server;
            }
        } catch (\Throwable $exception) {
            // Use the integration-container service name when runtime config is unavailable.
        }

        return '127.0.0.1';
    }

    /**
     * @param string $server
     * @return array<string, mixed>
     */
    private static function redisOptions(string $server): array
    {
        return [
            'server' => $server,
            'port' => 6379,
            'database' => 3,
            'password' => '',
        ];
    }
}
