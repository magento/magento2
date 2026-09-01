<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\Config\Data\ConfigData;

/** Builds the complete cache/frontend configuration for L1/L2 profiles. */
class L1L2Cache
{
    public const PRELOAD_KEY_NAMES = [
        'EAV_ENTITY_TYPES',
        'GLOBAL_PRIMARY_PLUGIN_LIST',
        'GLOBAL_PRIMARY_FRONTEND_PLUGIN_LIST',
        'SYSTEM_DEFAULT',
    ];

    public const CACHE_HOST = '127.0.0.1';

    public function applyZend(ConfigData $configData, array $options): void
    {
        $preloadKeys = $this->getPreloadKeys($options);
        $remote = [
            'persistent' => 0,
            'server' => $this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER, self::CACHE_HOST),
            'database' => $this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE, '5'),
            'port' => $this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_PORT, '6379'),
            'password' => $this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD, ''),
            'compress_data' => '0',
            'preload_keys' => $preloadKeys,
        ];

        $frontend = [
            'id_prefix' => $options[Cache::INPUT_KEY_CACHE_ID_PREFIX] ?? '69d_',
            'backend' => \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            'backend_options' => [
                'remote_backend' => \Magento\Framework\Cache\Backend\Redis::class,
                'remote_backend_options' => $remote,
                'local_backend' => 'Cm_Cache_Backend_File',
                'local_backend_options' => ['cache_dir' => '/dev/shm/'],
            ],
            'frontend_options' => ['write_control' => false],
        ];

        $configData->set('cache/frontend/default', $frontend);
        $configData->set('cache/frontend/stale_cache_enabled', $frontend + [
            'backend_options' => $frontend['backend_options'] + ['use_stale_cache' => true],
        ]);
    }

    public function applySymfony(ConfigData $configData, array $options): void
    {
        $preloadKeys = $this->getPreloadKeys($options, ':hash');
        $remote = [
            'server' => $this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER, self::CACHE_HOST),
            'database' => $this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE, '5'),
            'port' => (int)$this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_PORT, '6379'),
            'password' => $this->value($options, Cache::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD, ''),
            'serializer' => 'igbinary',
            'compression_lib' => 'gzip',
            'compression_data' => '0',
            'use_lua' => '1',
            'use_lua_on_gc' => '1',
            'preload_keys' => $preloadKeys,
        ];

        $frontend = [
            'id_prefix' => $options[Cache::INPUT_KEY_CACHE_ID_PREFIX] ?? '69d_',
            'backend' => 'symfony_l2',
            'backend_options' => [
                'remote_backend' => 'redis',
                'remote_backend_options' => $remote,
                'local_backend' => 'file',
                'local_backend_options' => ['cache_dir' => '/dev/shm/magento_l1'],
            ],
        ];

        $configData->set('cache/frontend/default', $frontend);
        $stale = $frontend;
        $stale['backend_options']['remote_backend_options']['persistent_id'] = 'magento_l2_stale';
        $stale['backend_options']['local_backend_options']['cache_dir'] = '/dev/shm/magento_l1_stale';
        $stale['backend_options']['use_stale_cache'] = true;
        $configData->set('cache/frontend/stale_cache_enabled', $stale);

        foreach (['layout', 'block_html', 'reflection', 'config_integration', 'config_integration_api', 'full_page', 'translate'] as $type) {
            $configData->set('cache/type/' . $type . '/frontend', 'stale_cache_enabled');
        }
    }

    private function value(array $options, string $key, string $default): string
    {
        return (string)($options[$key] ?? $default);
    }

    /**
     * Build preload keys using the same prefix configured for the frontend.
     * Zend stores the plain IDs; Symfony stores the tag-hash marker suffix.
     */
    private function getPreloadKeys(array $options, string $suffix = ''): array
    {
        $prefix = (string)($options[Cache::INPUT_KEY_CACHE_ID_PREFIX] ?? '69d_');

        return array_map(
            static fn (string $key): string => $prefix . $key . $suffix,
            self::PRELOAD_KEY_NAMES
        );
    }
}
