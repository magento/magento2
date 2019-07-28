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
        $loadAction = function () {
            $cachedData = $this->cache->load($this->configType);
            $data = false;
            if ($cachedData !== false) {
                $data = $this->serializer->unserialize($this->encryptor->decrypt($cachedData));
            }
            return $data;
        };

        return $this->lockQuery->lockedLoadData(
            self::$lockName,
            $loadAction,
            \Closure::fromCallable([$this, 'readData']),
            \Closure::fromCallable([$this, 'cacheData'])
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
            $cachedData = $this->cache->load($this->configType . '_' . $scopeType);
            $scopeData = false;
            if ($cachedData !== false) {
                $scopeData = [$scopeType => $this->serializer->unserialize($this->encryptor->decrypt($cachedData))];
            }
            return $scopeData;
        };

        return $this->lockQuery->lockedLoadData(
            self::$lockName,
            $loadAction,
            \Closure::fromCallable([$this, 'readData']),
            \Closure::fromCallable([$this, 'cacheData'])
        );
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
            $cachedData = $this->cache->load($this->configType . '_' . $scopeType . '_' . $scopeId);
            $scopeData = false;
            if ($cachedData === false) {
                if ($this->availableDataScopes === null) {
                    $cachedScopeData = $this->cache->load($this->configType . '_scopes');
                    if ($cachedScopeData !== false) {
                        $serializedCachedData = $this->encryptor->decrypt($cachedScopeData);
                        $this->availableDataScopes = $this->serializer->unserialize($serializedCachedData);
                    }
                }
                if (is_array($this->availableDataScopes) && !isset($this->availableDataScopes[$scopeType][$scopeId])) {
                    $scopeData = [$scopeType => [$scopeId => []]];
                }
            } else {
                $serializedCachedData = $this->encryptor->decrypt($cachedData);
                $scopeData = [$scopeType => [$scopeId => $this->serializer->unserialize($serializedCachedData)]];
            }

            return $scopeData;
        };

        return $this->lockQuery->lockedLoadData(
            self::$lockName,
            $loadAction,
            \Closure::fromCallable([$this, 'readData']),
            \Closure::fromCallable([$this, 'cacheData'])
        );
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
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data)),
            $this->configType,
            [self::CACHE_TAG]
        );
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data['default'])),
            $this->configType . '_default',
            [self::CACHE_TAG]
        );
        $scopes = [];
        foreach ([StoreScope::SCOPE_WEBSITES, StoreScope::SCOPE_STORES] as $curScopeType) {
            foreach ($data[$curScopeType] ?? [] as $curScopeId => $curScopeData) {
                $scopes[$curScopeType][$curScopeId] = 1;
                $this->cache->save(
                    $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($curScopeData)),
                    $this->configType . '_' . $curScopeType . '_' . $curScopeId,
                    [self::CACHE_TAG]
                );
            }
        }
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($scopes)),
            $this->configType . '_scopes',
            [self::CACHE_TAG]
        );
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
}
