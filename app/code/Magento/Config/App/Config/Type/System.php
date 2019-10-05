<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Config\App\Config\Type\System\Reader;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Framework\Encryption\Encryptor;
use Magento\Store\Model\ScopeInterface as StoreScope;

/**
 * System configuration type
 *
 * @api
 * @since 100.1.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class System implements ConfigTypeInterface
{
    /**
     * Config cache tag.
     */
    const CACHE_TAG = 'config_scopes';

    /**
     * System config type.
     */
    const CONFIG_TYPE = 'system';

    /**
     * Cache key for storing current prefix for config cache
     */
    const CACHE_KEY_FOR_PREFIX = 'system_cache_prefix';

    /**
     * Stale cache key for reading while new cache is generated
     */
    const STALE_CACHE_KEY_FOR_PREFIX = 'stale_system_cache_prefix';

    /**
     * Name of the lock to acquire during write
     */
    const LOCK_NAME = 'SYSTEM_CONFIG';

    /**
     * @var string
     */
    private static $lockName = self::LOCK_NAME;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var PostProcessorInterface
     */
    private $postProcessor;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * The type of config.
     *
     * @var string
     */
    private $configType;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * List of scopes that were retrieved from configuration storage
     *
     * Is used to make sure that we don't try to load non-existing configuration scopes.
     *
     * @var array
     */
    private $availableDataScopes;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var LockGuardedCacheLoader
     */
    private $lockQuery;

    /**
     * Cache prefix
     *
     * Allows to continue reading stale cache while new cache
     * is generated and prevents race condition on write operations
     *
     * @var string
     */
    private $cachePrefix;

    /**
     * @param ConfigSourceInterface $source
     * @param PostProcessorInterface $postProcessor
     * @param Fallback $fallback
     * @param FrontendInterface $cache
     * @param SerializerInterface $serializer
     * @param PreProcessorInterface $preProcessor
     * @param int $cachingNestedLevel
     * @param string $configType
     * @param Reader|null $reader
     * @param Encryptor|null $encryptor
     * @param LockManagerInterface|null $locker
     * @param LockGuardedCacheLoader|null $lockQuery
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConfigSourceInterface $source,
        PostProcessorInterface $postProcessor,
        Fallback $fallback,
        FrontendInterface $cache,
        SerializerInterface $serializer,
        PreProcessorInterface $preProcessor,
        $cachingNestedLevel = 1,
        $configType = self::CONFIG_TYPE,
        Reader $reader = null,
        Encryptor $encryptor = null,
        LockManagerInterface $locker = null,
        LockGuardedCacheLoader $lockQuery = null
    ) {
        $this->postProcessor = $postProcessor;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->configType = $configType;
        $this->reader = $reader ?: ObjectManager::getInstance()->get(Reader::class);
        $this->encryptor = $encryptor
            ?: ObjectManager::getInstance()->get(Encryptor::class);
        $this->lockQuery = $lockQuery
            ?: ObjectManager::getInstance()->get(LockGuardedCacheLoader::class);
    }

    /**
     * Get configuration value by path
     *
     * System configuration is separated by scopes (default, websites, stores). Configuration of a scope is inherited
     * from its parent scope (store inherits website).
     *
     * Because there can be many scopes on single instance of application, the configuration data can be pretty large,
     * so it does not make sense to load all of it on every application request. That is why we cache configuration
     * data by scope and only load configuration scope when a value from that scope is requested.
     *
     * Possible path values:
     * '' - will return whole system configuration (default scope + all other scopes)
     * 'default' - will return all default scope configuration values
     * '{scopeType}' - will return data from all scopes of a specified {scopeType} (websites, stores)
     * '{scopeType}/{scopeCode}' - will return data for all values of the scope specified by {scopeCode} and scope type
     * '{scopeType}/{scopeCode}/some/config/variable' - will return value of the config variable in the specified scope
     *
     * @inheritdoc
     * @since 100.1.2
     */
    public function get($path = '')
    {
        if ($path === '') {
            $this->data = array_replace_recursive($this->loadAllData(), $this->data);

            return $this->data;
        }

        return $this->getWithParts($path);
    }

    /**
     * Proceed with parts extraction from path.
     *
     * @param string $path
     * @return array|int|string|boolean
     */
    private function getWithParts($path)
    {
        $pathParts = explode('/', $path);

        if (count($pathParts) === 1 && $pathParts[0] !== ScopeInterface::SCOPE_DEFAULT) {
            if (!isset($this->data[$pathParts[0]])) {
                $data = $this->readData();
                $this->data = array_replace_recursive($data, $this->data);
            }

            return $this->data[$pathParts[0]];
        }

        $scopeType = array_shift($pathParts);

        if ($scopeType === ScopeInterface::SCOPE_DEFAULT) {
            if (!isset($this->data[$scopeType])) {
                $this->data[$scopeType] = $this->loadDefaultScopeData($scopeType);
            }

            return $this->getDataByPathParts($this->data[$scopeType], $pathParts);
        }

        $scopeId = array_shift($pathParts);

        if (!isset($this->data[$scopeType][$scopeId])) {
            $scopeData = $this->loadScopeData($scopeType, $scopeId);
            $this->data[$scopeType][$scopeId] = $scopeData;
        }

        return isset($this->data[$scopeType][$scopeId])
            ? $this->getDataByPathParts($this->data[$scopeType][$scopeId], $pathParts)
            : null;
    }

    /**
     * Walk nested hash map by keys from $pathParts.
     *
     * @param array $data to walk in
     * @param array $pathParts keys path
     * @return mixed
     */
    private function getDataByPathParts($data, $pathParts)
    {
        foreach ($pathParts as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof \Magento\Framework\DataObject) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * The freshly read data.
     *
     * @return array
     */
    private function readData(): array
    {
        $this->data = $this->reader->read();
        $this->data = $this->postProcessor->process(
            $this->data
        );

        return $this->data;
    }

    /**
     * Clean cache and global variables cache.
     *
     * Next items cleared:
     * - Internal property intended to store already loaded configuration data
     * - All records in cache storage tagged with CACHE_TAG
     *
     * @return void
     * @since 100.1.2
     */
    public function clean()
    {
        $this->data = [];
        $cleanAction = function () {
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
        };

        $this->lockQuery->lockedCleanData(
            self::$lockName,
            $cleanAction
        );
    }

    /**
     * Load configuration data for all scopes.
     *
     * @return array
     */
    private function loadAllData()
    {
        return $this->loadFromCacheAndDecode($this->configType) ?: $this->readData();
    }

    /**
     * Load configuration data for default scope.
     *
     * @param string $scopeType
     * @return array
     */
    private function loadDefaultScopeData($scopeType)
    {
        $data = $this->loadDataFromCacheForScopeType($scopeType);

        if ($data === false) {
            $data = $this->readData()[$scopeType] ?? [];
        }

        return $data;
    }

    /**
     * Load configuration data for a specified scope.
     *
     * @param string $scopeType
     * @param string $scopeId
     * @return array
     */
    private function loadScopeData($scopeType, $scopeId)
    {
        $scopeData = $this->loadFromCacheAndDecode(
            $this->configType . '_' . $scopeType . '_' . $scopeId
        );

        if ($scopeData !== false) {
            return $scopeData;
        }

        $availableScopes = $this->getAvailableDataScopes();

        if ($availableScopes && !isset($availableScopes[$scopeType][$scopeId])) {
            $scopeData = $this->loadFromCacheAndDecode(
                $this->configType
            );

            if (isset($scopeData[$scopeType][$scopeId])) {
                return $scopeData[$scopeType][$scopeId];
            }
        }

        return $this->readData()[$scopeType][$scopeId] ?? [];
    }

    /**
     * Loads all cache data for configuration
     *
     * @return array|bool
     */
    private function loadAllDataFromCache()
    {
        return $this->loadFromCacheAndDecode($this->configType);
    }

    /**
     * Loads data from cache for a specified scope type
     *
     * @param string $scopeType
     * @return array|bool
     */
    private function loadDataFromCacheForScopeType($scopeType)
    {
        $scopeData = $this->loadFromCacheAndDecode(
            $this->configType . '_' . $scopeType
        );

        if ($scopeData === false) {
            $scopeDataInAllScopes = $this->loadAllDataFromCache();
            if (isset($scopeDataInAllScopes[$scopeType])) {
                return $scopeDataInAllScopes[$scopeType];
            }
        }

        return $scopeData;
    }

    /**
     * Loads data from cache by key and decodes it into ready to use data
     *
     * @param string $cacheKey
     * @return array|bool
     */
    private function loadFromCacheAndDecode(string $cacheKey)
    {
        $cachePrefix = $this->loadCachePrefix();

        if (!$cachePrefix) {
            return false;
        }

        return $this->loadFromCacheAndDecodeWithPrefix($cacheKey, $cachePrefix);
    }

    /**
     * Loads data from cache by key and decodes it into ready to use data
     *
     * @param string $cacheKey
     * @param string $cachePrefix
     * @return array|bool
     */
    private function loadFromCacheAndDecodeWithPrefix(string $cacheKey, string $cachePrefix)
    {
        $cachedData = $this->cache->load($cachePrefix . $cacheKey);

        if ($cachedData === false) {
            return false;
        }

        $decodedData = $this->serializer->unserialize($this->encryptor->decrypt($cachedData));

        return $decodedData;
    }

    /**
     * Loads cache prefix is not previously loaded
     *
     * In case of cache prefix is not available in cache storage,
     * it elects main process which will write data to configuration cache.
     * So it tries to acquire lock if cache prefix is missing and
     * uses dataSaver as a way to trigger cache generation by returning null
     * as data saver only invoked when lock succeeds.
     */
    private function loadCachePrefix()
    {
        if ($this->cachePrefix !== null) {
            return $this->cachePrefix;
        }

        $this->cachePrefix = $this->lockQuery->nonBlockingLockedLoadData(
            self::$lockName,
            function () {
                return $this->cache->load(self::CACHE_KEY_FOR_PREFIX);
            },
            function () {
                return $this->cache->load(self::STALE_CACHE_KEY_FOR_PREFIX) ?? '';
            },
            \Closure::fromCallable([$this, 'cacheData'])
        );

        return $this->cachePrefix;
    }

    /**
     * Cache configuration data.
     *
     * Caches data per scope to avoid reading data for all scopes on every request
     *
     * @param string $previousCachePrefix
     * @return string
     */
    private function cacheData(string $previousCachePrefix)
    {
        $this->cachePrefix = $this->generateCachePrefix();

        $data = $this->readData();

        $cacheToStore = [
            $this->configType => $data,
            $this->configType . '_default' => $data['default']
        ];

        $scopes = [];
        foreach ([StoreScope::SCOPE_WEBSITES, StoreScope::SCOPE_STORES] as $curScopeType) {
            foreach ($data[$curScopeType] ?? [] as $curScopeId => $curScopeData) {
                $scopes[$curScopeType][$curScopeId] = 1;
                $cacheToStore[$this->configType . '_' . $curScopeType . '_' . $curScopeId] = $curScopeData;
            }
        }

        $cacheToStore[$this->configType . '_scopes'] = $scopes;

        foreach ($cacheToStore as $cacheKey => $cacheData) {
            $this->saveArrayToCache($cacheKey, $this->cachePrefix, $cacheData);
        }

        $this->saveArrayToCache($this->configType . '_expire_keys', $this->cachePrefix, array_keys($cacheToStore));

        $this->cache->save(
            $this->cachePrefix,
            self::CACHE_KEY_FOR_PREFIX,
            [self::CACHE_TAG]
        );

        if ($previousCachePrefix) {
            $this->expirePreviousCacheAfterAMinute($previousCachePrefix);
        }

        $this->cache->save(
            $this->cachePrefix,
            self::STALE_CACHE_KEY_FOR_PREFIX
        );

        return $this->cachePrefix;
    }

    /**
     * Retrieves available data scopes from cache
     *
     * @return string[]
     */
    private function getAvailableDataScopes(): array
    {
        if ($this->availableDataScopes === null) {
            $this->availableDataScopes = $this->loadFromCacheAndDecode($this->configType . '_scopes') ?: [];
        }

        return $this->availableDataScopes;
    }

    /**
     * Saves data by encoding it for a storage
     *
     * @param string $cacheKey
     * @param string $cachePrefix
     * @param array $data
     */
    private function saveArrayToCache(string $cacheKey, string $cachePrefix, array $data)
    {
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data)),
            $cachePrefix . $cacheKey,
            [$this->cachePrefix]
        );
    }

    /**
     * Generates new cache prefix
     *
     * @return string
     */
    private function generateCachePrefix(): string
    {
        return uniqid($this->configType);
    }

    /**
     * Expires previous configuration cache after one minute
     *
     * Saving cache with expire time prevents slow access for still open connection
     *
     * @param string $previousCachePrefix
     */
    private function expirePreviousCacheAfterAMinute(string $previousCachePrefix)
    {
        $keysToExpire = $this->loadFromCacheAndDecodeWithPrefix(
            $this->configType . '_scopes_keys',
            $previousCachePrefix
        ) ?: [];

        foreach ($keysToExpire as $cacheKey) {
            $value = $this->cache->load($previousCachePrefix . $cacheKey);
            if (!$value) {
                continue;
            }

            $this->cache->save($value, $previousCachePrefix . $cacheKey, [], 60);
        }
    }
}
