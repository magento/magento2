<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Factory that creates cache frontend instances based on options
 */
namespace Magento\Framework\App\Cache\Frontend;

use Exception;
use LogicException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\Cache\Backend\Eaccelerator;
use Magento\Framework\Cache\Backend\RemoteSynchronizedCache;
use Magento\Framework\Cache\Frontend\Adapter\PreloadingSymfonyAdapter;
use Magento\Framework\Cache\Frontend\Adapter\Symfony;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\Cache\Frontend\Decorator\Compression as CompressionDecorator;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Profiler;
use UnexpectedValueException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Factory
{
    /**
     * Default cache entry lifetime
     */
    public const DEFAULT_LIFETIME = 7200;

    /**
     * Caching params, that applied for all cache frontends regardless of type
     */
    public const PARAM_CACHE_FORCED_OPTIONS = 'cache_options';

    /**
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var Filesystem
     */
    private $_filesystem;

    /**
     * Cache options to be enforced for all instances being created
     *
     * @var array
     */
    private $_enforcedOptions = [];

    /**
     * Configuration of decorators that are to be applied to every cache frontend being instantiated, format:
     * array(
     *  array('class' => '<decorator_class>', 'arguments' => array()),
     *  ...
     * )
     *
     * @var array
     */
    private $_decorators = [];

    /**
     * Default cache backend type
     *
     * @var string
     */
    protected $_defaultBackend = 'file';

    /**
     * Options for default backend
     *
     * @var array
     */
    protected $_backendOptions = [
        'hashed_directory_level' => 1,
        'file_name_prefix' => 'mage',
    ];

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * SymfonyAdapterProvider instance for creating Symfony cache adapters
     *
     * @var SymfonyAdapterProvider
     */
    private SymfonyAdapterProvider $adapterProvider;

    /**
     * Cached directory paths (performance optimization)
     *
     * @var array
     */
    private array $cachedDirectories = [];

    /**
     * Cached extension availability checks (performance optimization)
     *
     * @var array
     */
    private array $extensionCache = [];

    /**
     * Cached ID prefix (performance optimization)
     *
     * @var string|null
     */
    private ?string $cachedIdPrefix = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Filesystem $filesystem
     * @param ResourceConnection $resource
     * @param SymfonyAdapterProvider $adapterProvider
     * @param array $enforcedOptions
     * @param array $decorators
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Filesystem $filesystem,
        ResourceConnection $resource,
        SymfonyAdapterProvider $adapterProvider,
        array $enforcedOptions = [],
        array $decorators = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_filesystem = $filesystem;
        $this->_resource = $resource;
        $this->adapterProvider = $adapterProvider;
        $this->_enforcedOptions = $enforcedOptions;
        $this->_decorators = $decorators;
    }

    /**
     * Return newly created cache frontend instance
     *
     * @param array $options
     * @return FrontendInterface
     */
    public function create(array $options)
    {
        $options = $this->_getExpandedOptions($options);

        // Optimize: Cache directory operations
        foreach (['backend_options', 'slow_backend_options'] as $section) {
            if (!empty($options[$section]['cache_dir'])) {
                $cacheDir = $options[$section]['cache_dir'];
                if (!isset($this->cachedDirectories[$cacheDir])) {
                    $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                    $directory->create($cacheDir);
                    $this->cachedDirectories[$cacheDir] = $directory->getAbsolutePath($cacheDir);
                }
                $options[$section]['cache_dir'] = $this->cachedDirectories[$cacheDir];
            }
        }

        // Optimize: Use cached ID prefix or generate once
        $idPrefix = $this->getIdPrefix($options);
        $options['frontend_options']['cache_id_prefix'] = $idPrefix;

        $backend = $this->_getBackendOptions($options);
        $frontend = $this->_getFrontendOptions($options);

        // Start profiling
        $profilerTags = [
            'group' => 'cache',
            'operation' => 'cache:create',
            'frontend_type' => $frontend['type'],
            'backend_type' => $backend['type'],
        ];
        Profiler::start('cache_frontend_create', $profilerTags);

        // Check for special backend types
        $backendType = $options['backend'] ?? $this->_defaultBackend;

        if ($this->isSymfonyL2Cache($backendType)) {
            // SymfonyL2Cache backend for L2 cache with Symfony
            $result = $this->createSymfonyL2Cache($options);
        } else {
            // Use Symfony cache - fully backward compatible, no Zend cache needed
            $result = $this->createSymfonyCache($options);
        }

        $result = $this->_applyDecorators($result);

        // stop profiling
        Profiler::stop('cache_frontend_create');
        return $result;
    }

    /**
     * Get or generate cache ID prefix (optimized with caching)
     *
     * @param array $options
     * @return string
     */
    private function getIdPrefix(array $options): string
    {
        // Check explicit prefix in options
        $idPrefix = $options['id_prefix'] ?? $options['prefix'] ?? '';

        if (!empty($idPrefix)) {
            return $idPrefix;
        }

        // Use cached prefix if available
        if ($this->cachedIdPrefix !== null) {
            return $this->cachedIdPrefix;
        }

        // Generate and cache prefix
        $configDirPath = $this->_filesystem->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath();
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $this->cachedIdPrefix = substr(md5($configDirPath), 0, 3) . '_';

        return $this->cachedIdPrefix;
    }

    /**
     * Return options expanded with enforced values
     *
     * @param array $options
     * @return array
     */
    private function _getExpandedOptions(array $options)
    {
        return array_replace_recursive($options, $this->_enforcedOptions);
    }

    /**
     * Apply decorators to a cache frontend instance and return the topmost one
     *
     * @param FrontendInterface $frontend
     * @return FrontendInterface
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    private function _applyDecorators(FrontendInterface $frontend)
    {
        foreach ($this->_decorators as $decoratorConfig) {
            if (!isset($decoratorConfig['class'])) {
                throw new LogicException('Class has to be specified for a cache frontend decorator.');
            }
            $decoratorClass = $decoratorConfig['class'];
            $decoratorParams = isset($decoratorConfig['parameters']) ? $decoratorConfig['parameters'] : [];
            $decoratorParams['frontend'] = $frontend;
            // conventionally, 'frontend' argument is a decoration subject
            $frontend = $this->_objectManager->create($decoratorClass, $decoratorParams);
            if (!$frontend instanceof FrontendInterface) {
                throw new UnexpectedValueException('Decorator has to implement the cache frontend interface.');
            }
        }
        return $frontend;
    }

    /**
     * Check if extension is loaded (cached for performance)
     *
     * @param string $extension
     * @return bool
     */
    private function isExtensionLoaded(string $extension): bool
    {
        if (!isset($this->extensionCache[$extension])) {
            $this->extensionCache[$extension] = extension_loaded($extension);
        }
        return $this->extensionCache[$extension];
    }

    /**
     * Get cache backend options. Result array contain backend type ('type' key) and backend options ('options')
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getBackendOptions(array $cacheOptions) //phpcs:ignore Generic.Metrics.NestingLevel
    {
        $enableTwoLevels = false;
        $type = $cacheOptions['backend'] ?? $this->_defaultBackend;
        $options = (isset($cacheOptions['backend_options']) && is_array($cacheOptions['backend_options']))
            ? $cacheOptions['backend_options']
            : [];

        $backendType = false;
        $typeLower = strtolower($type);

        switch ($typeLower) {
            case 'sqlite':
                if ($this->isExtensionLoaded('sqlite') && isset($options['cache_db_complete_path'])) {
                    $backendType = 'Sqlite';
                }
                break;
            case 'memcached':
                if ($this->isExtensionLoaded('memcached')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enableTwoLevels = true;
                    $backendType = 'Libmemcached';
                } elseif ($this->isExtensionLoaded('memcache')) {
                    if (isset($cacheOptions['memcached'])) {
                        $options = $cacheOptions['memcached'];
                    }
                    $enableTwoLevels = true;
                    $backendType = 'Memcached';
                }
                break;
            case 'apc':
                if ($this->isExtensionLoaded('apc') && ini_get('apc.enabled')) {
                    $enableTwoLevels = true;
                    $backendType = 'Apc';
                }
                break;
            case 'xcache':
                if ($this->isExtensionLoaded('xcache')) {
                    $enableTwoLevels = true;
                    $backendType = 'Xcache';
                }
                break;
            case 'eaccelerator':
            case 'varien_cache_backend_eaccelerator':
                if ($this->isExtensionLoaded('eaccelerator') && ini_get('eaccelerator.enable')) {
                    $enableTwoLevels = true;
                    $backendType = Eaccelerator::class;
                }
                break;
            case 'database':
                $backendType = Database::class;
                $options = $this->_getDbAdapterOptions();
                break;
            case 'remote_synchronized_cache':
                $backendType = RemoteSynchronizedCache::class;
                $options['remote_backend'] = Database::class;
                $options['remote_backend_options'] = $this->_getDbAdapterOptions();
                $options['local_backend'] = 'file';
                // Use cached directory operation
                if (!isset($this->cachedDirectories['cache'])) {
                    $cacheDir = $this->_filesystem->getDirectoryWrite(DirectoryList::CACHE);
                    $this->cachedDirectories['cache'] = $cacheDir->getAbsolutePath();
                    $cacheDir->create();
                }
                $options['local_backend_options']['cache_dir'] = $this->cachedDirectories['cache'];
                break;
            default:
                // For custom backend types, use the type as-is if it's a valid class
                if ($type != $this->_defaultBackend && class_exists($type, true)) {
                    $backendType = $type;
                }
        }

        if (!$backendType) {
            $backendType = $this->_defaultBackend;
            // Use cached directory operation
            if (!isset($this->cachedDirectories['cache'])) {
                $cacheDir = $this->_filesystem->getDirectoryWrite(DirectoryList::CACHE);
                $this->cachedDirectories['cache'] = $cacheDir->getAbsolutePath();
                $cacheDir->create();
            }
            $this->_backendOptions['cache_dir'] = $this->cachedDirectories['cache'];
        }

        // Merge with default backend options (optimized)
        foreach ($this->_backendOptions as $option => $value) {
            if (!array_key_exists($option, $options)) {
                $options[$option] = $value;
            }
        }

        $backendOptions = ['type' => $backendType, 'options' => $options];
        if ($enableTwoLevels) {
            $backendOptions = $this->_getTwoLevelsBackendOptions($backendOptions, $cacheOptions);
        }
        return $backendOptions;
    }

    /**
     * Get options for database backend type
     *
     * @return array
     */
    protected function _getDbAdapterOptions()
    {
        $options['adapter_callback'] = function () {
            return $this->_resource->getConnection();
        };
        $options['data_table_callback'] = function () {
            return $this->_resource->getTableName('cache');
        };
        $options['tags_table_callback'] = function () {
            return $this->_resource->getTableName('cache_tag');
        };
        return $options;
    }

    /**
     * Initialize two levels backend model options
     *
     * @param array $fastOptions fast level backend type and options
     * @param array $cacheOptions all cache options
     * @return array
     */
    protected function _getTwoLevelsBackendOptions($fastOptions, $cacheOptions)
    {
        $options = [];
        $options['fast_backend'] = $fastOptions['type'];
        $options['fast_backend_options'] = $fastOptions['options'];
        $options['fast_backend_custom_naming'] = true;
        $options['fast_backend_autoload'] = true;
        $options['slow_backend_custom_naming'] = true;
        $options['slow_backend_autoload'] = true;

        if (isset($cacheOptions['auto_refresh_fast_cache'])) {
            $options['auto_refresh_fast_cache'] = (bool)$cacheOptions['auto_refresh_fast_cache'];
        } else {
            $options['auto_refresh_fast_cache'] = false;
        }
        if (isset($cacheOptions['slow_backend'])) {
            $options['slow_backend'] = $cacheOptions['slow_backend'];
        } else {
            $options['slow_backend'] = $this->_defaultBackend;
        }
        if (isset($cacheOptions['slow_backend_options'])) {
            $options['slow_backend_options'] = $cacheOptions['slow_backend_options'];
        } else {
            $options['slow_backend_options'] = $this->_backendOptions;
        }
        if ($options['slow_backend'] == 'database') {
            $options['slow_backend'] = Database::class;
            $options['slow_backend_options'] = $this->_getDbAdapterOptions();
            if (isset($cacheOptions['slow_backend_store_data'])) {
                $options['slow_backend_options']['store_data'] = (bool)$cacheOptions['slow_backend_store_data'];
            } else {
                $options['slow_backend_options']['store_data'] = false;
            }
        }

        $backend = ['type' => 'TwoLevels', 'options' => $options];
        return $backend;
    }

    /**
     * Get options of cache frontend
     *
     * @param  array $cacheOptions
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getFrontendOptions(array $cacheOptions)
    {
        $options = isset($cacheOptions['frontend_options']) ? $cacheOptions['frontend_options'] : [];
        if (!array_key_exists('caching', $options)) {
            $options['caching'] = true;
        }
        if (!array_key_exists('lifetime', $options)) {
            $options['lifetime'] = isset(
                $cacheOptions['lifetime']
            ) ? $cacheOptions['lifetime'] : self::DEFAULT_LIFETIME;
        }
        if (!array_key_exists('automatic_cleaning_factor', $options)) {
            $options['automatic_cleaning_factor'] = 0;
        }
        $options['type'] = isset($cacheOptions['frontend']) ? $cacheOptions['frontend'] : Symfony::class;
        return $options;
    }

    /**
     * Prepare and cache directory paths for cache storage
     *
     * @param array $options
     * @return void
     */
    private function prepareCacheDirectories(array &$options): void
    {
        foreach (['backend_options', 'slow_backend_options'] as $section) {
            if (!empty($options[$section]['cache_dir'])) {
                $cacheDir = $options[$section]['cache_dir'];
                if (!isset($this->cachedDirectories[$cacheDir])) {
                    $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                    $directory->create($cacheDir);
                    $this->cachedDirectories[$cacheDir] = $directory->getAbsolutePath($cacheDir);
                }
                $options[$section]['cache_dir'] = $this->cachedDirectories[$cacheDir];
            }
        }
    }

    /**
     * Create cache frontend instance using Symfony Cache
     *
     * This method creates a Symfony-based cache adapter that implements FrontendInterface.
     * It provides PSR-6 compliant caching while maintaining full backward compatibility.
     *
     * @param array $options
     * @return FrontendInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function createSymfonyCache(array $options): FrontendInterface
    {
        $options = $this->_getExpandedOptions($options);

        // Prepare cache directories
        $this->prepareCacheDirectories($options);

        // Optimize: Use cached ID prefix
        $idPrefix = $this->getIdPrefix($options);

        // Get backend configuration
        // For Symfony cache, use the original backend string from config, not the resolved Zend class
        $originalBackendType = $options['backend'] ?? $this->_defaultBackend;
        $backend = $this->_getBackendOptions($options);
        $backendOptions = $backend['options'];

        // Get default lifetime
        $frontend = $this->_getFrontendOptions($options);
        $defaultLifetime = $frontend['lifetime'] ?? self::DEFAULT_LIFETIME;

        // Detect if this is page cache
        $frontendId = $options['frontend_id'] ?? null;
        $isPageCache = in_array($frontendId, ['page_cache', 'full_page'], true);

        // Start profiling
        $profilerTags = [
            'group' => 'cache',
            'operation' => 'cache:create_symfony',
            'backend_type' => $originalBackendType,
        ];
        Profiler::start('cache_symfony_create', $profilerTags);

        try {
            // Use injected adapter provider instance
            $adapterProvider = $this->adapterProvider;

            // Create cache adapter factory closure (for fork detection)
            // Use originalBackendType so SymfonyAdapterProvider can map it correctly
            $cacheFactory = function () use (
                $adapterProvider,
                $originalBackendType,
                $backendOptions,
                $idPrefix,
                $defaultLifetime
            ) {
                return $adapterProvider->createAdapter(
                    $originalBackendType,
                    $backendOptions,
                    $idPrefix,
                    $defaultLifetime
                );
            };

            // Create initial cache pool
            $cachePool = $cacheFactory();

            // Create tag adapter for backend-specific operations
            $adapter = $adapterProvider->createTagAdapter(
                $originalBackendType,
                $cachePool,
                $idPrefix,
                $isPageCache,
                $backendOptions
            );

            // Create Symfony adapter with fork detection support and tag adapter
            $result = $this->_objectManager->create(
                Symfony::class,
                [
                    'cacheFactory' => $cacheFactory,
                    'adapter' => $adapter,
                    'defaultLifetime' => $defaultLifetime,
                    'idPrefix' => $idPrefix,
                ]
            );

            // Apply compression decorator if enabled in backend options
            if ($this->isCompressionEnabled($backendOptions)) {
                $result = $this->applyCompressionDecorator($result, $backendOptions);
            }

            // Apply other decorators
            $result = $this->_applyDecorators($result);

            // Apply preloading wrapper if preload_keys configured
            if (!empty($backendOptions['preload_keys']) && is_array($backendOptions['preload_keys'])) {
                $result = $this->_objectManager->create(
                    PreloadingSymfonyAdapter::class,
                    [
                        'adapter' => $result,
                        'preloadKeys' => $backendOptions['preload_keys'],
                    ]
                );
            }

        } catch (\Exception $e) {
            Profiler::stop('cache_symfony_create');

            // Log the error but don't re-throw - SymfonyAdapterProvider has fallback logic
            // Re-throw exception only for critical errors (not connection failures)
            throw new \RuntimeException(
                'Failed to create Symfony cache: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        Profiler::stop('cache_symfony_create');
        return $result;
    }

    /**
     * Check if compression is enabled in backend options
     *
     * @param array $backendOptions
     * @return bool
     */
    private function isCompressionEnabled(array $backendOptions): bool
    {
        // Check if compress_data is explicitly enabled (value '1' or true)
        return isset($backendOptions['compress_data'])
            && ($backendOptions['compress_data'] === '1' || $backendOptions['compress_data'] === 1);
    }

    /**
     * Apply compression decorator to cache frontend
     *
     * @param FrontendInterface $frontend
     * @param array $backendOptions
     * @return FrontendInterface
     */
    private function applyCompressionDecorator(
        FrontendInterface $frontend,
        array $backendOptions
    ): FrontendInterface {
        // Get compression threshold (default: 2048 bytes)
        // Matches legacy Zend cache default of 512, but increased for better performance
        $threshold = (int)($backendOptions['compression_threshold'] ?? 2048);

        // Get compression library (default: gzip for best compatibility)
        // Supported: gzip, snappy, lzf, lz4, zstd
        $compressionLib = $backendOptions['compression_lib'] ?? 'gzip';
        if (empty($compressionLib)) {
            $compressionLib = 'gzip'; // Default to gzip if empty string
        }

        // Get compression level (1-9, default: 6)
        $compressionLevel = (int)($backendOptions['compression_level'] ?? 6);

        // Create and return compression decorator
        return $this->_objectManager->create(
            CompressionDecorator::class,
            [
                'frontend' => $frontend,
                'threshold' => $threshold,
                'compressionLib' => $compressionLib,
                'compressionLevel' => $compressionLevel,
            ]
        );
    }

    /**
     * Check if backend is SymfonyL2Cache
     *
     * @param string $backendType
     * @return bool
     */
    private function isSymfonyL2Cache(string $backendType): bool
    {
        $backendLower = strtolower($backendType);

        // Check for symfony_l2 or l2_symfony or SymfonyL2Cache
        return in_array($backendLower, [
            'symfony_l2',
            'l2_symfony',
            'symfony_l2_cache',
            'magento\framework\cache\backend\symfonyl2cache',
        ], true);
    }

    /**
     * Create SymfonyL2Cache (Clean L2 cache for Symfony)
     *
     * @param array $options
     * @return FrontendInterface
     * @throws \Exception
     */
    private function createSymfonyL2Cache(array $options): FrontendInterface
    {
        $backendOptions = $options['backend_options'] ?? [];

        // Get remote backend configuration (L2 - persistent, shared)
        $remoteBackend = $backendOptions['remote_backend'] ?? 'redis';
        $remoteBackendOptions = $backendOptions['remote_backend_options'] ?? [];

        // Get local backend configuration (L1 - fast, local)
        $localBackend = $backendOptions['local_backend'] ?? 'file';
        $localBackendOptions = $backendOptions['local_backend_options'] ?? [];

        // Get common options
        $frontend = $this->_getFrontendOptions($options);
        $defaultLifetime = $frontend['lifetime'] ?? self::DEFAULT_LIFETIME;

        Profiler::start('cache_symfony_l2_create', [
            'group' => 'cache',
            'operation' => 'cache:create_symfony_l2',
            'remote_backend' => $remoteBackend,
            'local_backend' => $localBackend,
        ]);

        try {
            // Create remote backend (L2 - Symfony)
            $remoteOptions = array_merge($options, [
                'backend' => $remoteBackend,
                'backend_options' => $remoteBackendOptions,
            ]);
            $remoteFrontend = $this->createSymfonyCache($remoteOptions);

            // Create local backend (L1 - Symfony)
            $localOptions = array_merge($options, [
                'backend' => $localBackend,
                'backend_options' => $localBackendOptions,
            ]);
            $localFrontend = $this->createSymfonyCache($localOptions);

            // Create SymfonyL2Cache backend
            $l2Backend = $this->_objectManager->create(
                \Magento\Framework\Cache\Backend\SymfonyL2Cache::class,
                [
                    'remote' => $remoteFrontend,
                    'local' => $localFrontend,
                    'options' => [
                        'cleanup_percentage' => $backendOptions['cleanup_percentage'] ?? 90,
                        'use_stale_cache' => $backendOptions['use_stale_cache'] ?? false,
                    ],
                ]
            );

            // Wrap in frontend adapter
            $result = $this->_objectManager->create(
                \Magento\Framework\Cache\Frontend\Adapter\RemoteSynchronizedSymfonyAdapter::class,
                [
                    'backend' => $l2Backend,
                    'defaultLifetime' => $defaultLifetime,
                ]
            );

            Profiler::stop('cache_symfony_l2_create');
            return $result;

        } catch (\Exception $e) {
            Profiler::stop('cache_symfony_l2_create');
            throw new \RuntimeException(
                'Failed to create Symfony L2 cache: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}
