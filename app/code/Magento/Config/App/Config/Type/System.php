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
    const STALE_CACHE_PREFIX = 'stale';

    /**
     * @var string
     */
    private static $lockName = 'SYSTEM_CONFIG';

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
                $this->data = array_replace_recursive($this->loadDefaultScopeData($scopeType), $this->data);
            }

            return $this->getDataByPathParts($this->data[$scopeType], $pathParts);
        }

        $scopeId = array_shift($pathParts);

        if (!isset($this->data[$scopeType][$scopeId])) {
            $scopeData = $this->loadScopeData($scopeType, $scopeId);

            if (!isset($this->data[$scopeType][$scopeId])) {
                $this->data = array_replace_recursive($scopeData, $this->data);
            }
        }

        return isset($this->data[$scopeType][$scopeId])
            ? $this->getDataByPathParts($this->data[$scopeType][$scopeId], $pathParts)
            : null;
    }

    /**
     * Load configuration data for all scopes.
     *
     * @return array
     */
    private function loadAllData()
    {
        return $this->lockQuery->nonBlockingLockedLoadData(
            self::$lockName,
            \Closure::fromCallable([$this, 'loadAllDataFromCache']),
            \Closure::fromCallable([$this, 'readData']),
            function ($data) {
                $this->cacheData($data);
                return $data;
            },
            \Closure::fromCallable([$this, 'loadAllStaleDataFromCache'])
        );
    }

    /**
     * Load configuration data for default scope.
     *
     * @param string $scopeType
     * @return array
     */
    private function loadDefaultScopeData($scopeType)
    {
        $loadAction = function () use ($scopeType) {
            return $this->loadDataFromCacheForScopeType($scopeType);
        };

        return $this->lockQuery->nonBlockingLockedLoadData(
            self::$lockName,
            $loadAction,
            \Closure::fromCallable([$this, 'readData']),
            function ($data) use ($scopeType) {
                $this->cacheData($data);
                return [$scopeType => $data[$scopeType] ?? []];
            },
            function () use ($scopeType) {
                $scopeData = $this->loadAllStaleDataFromCache()[$scopeType] ?? false;
                return $scopeData ? [$scopeType => $scopeData] : false;
            }
        );
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
     * Loads all cache data for configuration
     *
     * @return array|bool
     */
    private function loadAllStaleDataFromCache()
    {
        return $this->loadFromCacheAndDecode(self::STALE_CACHE_PREFIX . '_' . $this->configType);
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
            $this->configType . '_' . $scopeType,
            function ($cacheData) use ($scopeType) {
                return [$scopeType => $cacheData];
            }
        );

        if ($scopeData === false) {
            $scopeData = $this->loadAllDataFromCache()[$scopeType] ?? false;
        }

        return $scopeData;
    }

    /**
     * Loads data from cache by key and decodes it into ready to use data
     *
     * @param string $cacheKey
     * @param callable|null $dataFormatter
     * @return array|bool
     */
    private function loadFromCacheAndDecode(string $cacheKey, callable $dataFormatter = null)
    {
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData === false) {
            return false;
        }

        $decodedData = $this->serializer->unserialize($this->encryptor->decrypt($cachedData));

        if ($dataFormatter) {
            $decodedData = $dataFormatter($decodedData);
        }

        return $decodedData;
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
        $loadAction = function () use ($scopeType, $scopeId) {
            $scopeData = $this->loadFromCacheAndDecode(
                $this->configType . '_' . $scopeType . '_' . $scopeId,
                function ($cachedData) use ($scopeType, $scopeId) {
                    return [$scopeType => [$scopeId => $cachedData]];
                }
            );

            if ($scopeData !== false) {
                return $scopeData;
            }

            $availableScopes = $this->getAvailableDataScopes();

            if ($availableScopes && !isset($availableScopes[$scopeType][$scopeId])) {
                $scopeData = $this->loadFromCacheAndDecode(
                    $this->configType,
                    function ($cachedData) use ($scopeType, $scopeId) {
                        return $cachedData[$scopeType][$scopeId] ?? [];
                    }
                );

                return [$scopeType => [$scopeId => $scopeData]];
            }

            return $scopeData;
        };

        return $this->lockQuery->nonBlockingLockedLoadData(
            self::$lockName,
            $loadAction,
            \Closure::fromCallable([$this, 'readData']),
            function ($data) use ($scopeType, $scopeId) {
                $this->cacheData($data);
                return [$scopeType => [$scopeId => [$data[$scopeType][$scopeId] ?? []]]];
            },
            function () use ($scopeType, $scopeId) {
                $staleData = $this->loadAllStaleDataFromCache();

                if ($staleData) {
                    return $staleData[$scopeType][$scopeId] ?? [];
                }

                return false;
            }
        );
    }

    private function getAvailableDataScopes(): array
    {
        if ($this->availableDataScopes === null) {
            $this->loadFromCacheAndDecode($this->configType . '_scopes', function ($scopes) {
                $this->availableDataScopes = $scopes;
            });
        }

        return $this->availableDataScopes ?? [];
    }

    /**
     * Cache configuration data.
     *
     * Caches data per scope to avoid reading data for all scopes on every request
     *
     * @param array $data
     * @return void
     */
    private function cacheData(array $data)
    {
        $this->saveToCache($this->configType, $data);
        $this->saveToCache($this->configType . '_default', $data['default']);

        $this->saveToCacheWithCacheTag(
            self::STALE_CACHE_PREFIX . '_' .  $this->configType,
            $data,
            []
        );

        $scopes = [];
        foreach ([StoreScope::SCOPE_WEBSITES, StoreScope::SCOPE_STORES] as $curScopeType) {
            foreach ($data[$curScopeType] ?? [] as $curScopeId => $curScopeData) {
                $scopes[$curScopeType][$curScopeId] = 1;
                $this->saveToCache($this->configType . '_' . $curScopeType . '_' . $curScopeId, $curScopeData);
            }
        }

        $this->saveToCache($this->configType . '_scopes', $scopes);
    }

    /**
     * Saves data by encoding it for a storage
     *
     * Automatically adds a cache key
     *
     * @param string $cacheKey
     * @param array $data
     */
    private function saveToCache(string $cacheKey, array $data)
    {
        $cacheTags = [self::CACHE_TAG];
        $this->saveToCacheWithCacheTag($cacheKey, $data, $cacheTags);
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
     * @param string $cacheKey
     * @param array $data
     * @param array $cacheTags
     */
    private function saveToCacheWithCacheTag(string $cacheKey, array $data, array $cacheTags)
    {
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data)),
            $cacheKey,
            $cacheTags
        );
    }
}
