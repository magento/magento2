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

    /**
     * Apply the legacy Zend Redis L1/L2 cache configuration.
     *
     * @param ConfigData $configData
     * @param array $options
     * @param string $idPrefix
     * @return void
     */
    public function applyZend(ConfigData $configData, array $options, string $idPrefix): void
    {
        $preloadKeys = $this->getPreloadKeys($idPrefix);
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
            'id_prefix' => $idPrefix,
            'backend' => \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            'backend_options' => [
                'remote_backend' => \Magento\Framework\Cache\Backend\Valkey::class,
                'remote_backend_options' => $remote,
                'local_backend' => 'Cm_Cache_Backend_File',
                'local_backend_options' => ['cache_dir' => BP . '/var/cache/zend_tests_l1'],
            ],
            'frontend_options' => ['write_control' => false],
        ];

        $configData->set('cache/frontend/default', $frontend);
        $staleFrontend = $frontend;
        $staleFrontend['backend_options']['use_stale_cache'] = true;
        $configData->set('cache/frontend/stale_cache_enabled', $staleFrontend);

        // full_page is intentionally omitted: the full-page cache must stay on its dedicated
        // 'page_cache' frontend. Routing it to 'stale_cache_enabled' would move FPC onto the shared
        // default-cache L2 (same Redis db), where default-cache tag invalidations collide with FPC
        // entries and drop unrelated pages. Keep this list aligned with applySymfony().
        $staleCacheTypes = [
            'layout',
            'block_html',
            'reflection',
            'config_integration',
            'config_integration_api',
            'translate',
        ];
        foreach ($staleCacheTypes as $type) {
            $configData->set('cache/type/' . $type . '/frontend', 'stale_cache_enabled');
        }
    }

    /**
     * Apply the Symfony Redis L1/L2 cache configuration.
     *
     * @param ConfigData $configData
     * @param array $options
     * @param string $idPrefix
     * @return void
     */
    public function applySymfony(ConfigData $configData, array $options, string $idPrefix): void
    {
        $preloadKeys = $this->getPreloadKeys($idPrefix, ':hash');
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
            'id_prefix' => $idPrefix,
            'backend' => 'symfony_l2',
            'backend_options' => [
                'remote_backend' => 'valkey',
                'remote_backend_options' => $remote,
                'local_backend' => 'file',
                'local_backend_options' => ['cache_dir' => BP . '/var/cache/magento_l1'],
            ],
        ];

        $configData->set('cache/frontend/default', $frontend);
        $stale = $frontend;
        $stale['backend_options']['remote_backend_options']['persistent_id'] = 'magento_l2_stale';
        $stale['backend_options']['local_backend_options']['cache_dir'] = BP . '/var/cache/magento_l1_stale';
        $stale['backend_options']['use_stale_cache'] = true;
        $configData->set('cache/frontend/stale_cache_enabled', $stale);

        // full_page is intentionally omitted: like the Zend profile (applyZend routes no cache type to
        // the stale frontend), the full-page cache must stay on its dedicated 'page_cache' frontend.
        // Routing it to 'stale_cache_enabled' would move FPC onto the shared default-cache L2 (same
        // Redis db), where default-cache tag invalidations collide with FPC entries and drop unrelated
        // pages (e.g. invalidating one CMS page evicts another).
        $staleCacheTypes = [
            'layout',
            'block_html',
            'reflection',
            'config_integration',
            'config_integration_api',
            'translate',
        ];
        foreach ($staleCacheTypes as $type) {
            $configData->set('cache/type/' . $type . '/frontend', 'stale_cache_enabled');
        }
    }

    /**
     * Return a string option value or its default.
     *
     * @param array $options
     * @param string $key
     * @param string $default
     * @return string
     */
    private function value(array $options, string $key, string $default): string
    {
        return (string)($options[$key] ?? $default);
    }

    /**
     * Build preload keys using the same prefix configured for the frontend.
     *
     * Zend stores the plain IDs; Symfony stores the tag-hash marker suffix.
     *
     * @param string $idPrefix
     * @param string $suffix
     * @return array
     */
    private function getPreloadKeys(string $idPrefix, string $suffix = ''): array
    {
        return array_map(
            static fn (string $key): string => $idPrefix . $key . $suffix,
            self::PRELOAD_KEY_NAMES
        );
    }
}
