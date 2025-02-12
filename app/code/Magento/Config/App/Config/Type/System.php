<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\App\Config\Type;

use Magento\Config\App\Config\Type\System\Reader;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Store\Model\ScopeInterface as StoreScope;
use Psr\Log\LoggerInterface;

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
    public const CACHE_TAG = 'config_scopes';

    /**
     * System config type.
     */
    public const CONFIG_TYPE = 'system';

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
     * @var StateInterface
     */
    private $cacheState;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * System constructor.
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
     * @param StateInterface|null $cacheState
     * @param LoggerInterface $logger
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
        ?Reader $reader = null,
        ?Encryptor $encryptor = null,
        ?LockManagerInterface $locker = null,
        ?LockGuardedCacheLoader $lockQuery = null,
        ?StateInterface $cacheState = null,
        ?LoggerInterface $logger = null
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
        $this->cacheState = $cacheState
            ?: ObjectManager::getInstance()->get(StateInterface::class);
        $this->logger = $logger
            ?: ObjectManager::getInstance()->get(LoggerInterface::class);
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
            $this->data = $this->loadAllData();
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
                $this->readData();
            }

            return $this->data[$pathParts[0]];
        }

        $scopeType = array_shift($pathParts);

        if ($scopeType === ScopeInterface::SCOPE_DEFAULT) {
            if (!isset($this->data[$scopeType])) {
                $scopeData = $this->loadDefaultScopeData() ?? [];
                $this->setDataByScopeType($scopeType, $scopeData);
            }

            return $this->getDataByPathParts($this->data[$scopeType], $pathParts);
        }

        $scopeId = array_shift($pathParts);

        if (!isset($this->data[$scopeType][$scopeId])) {
            $scopeData = $this->loadScopeData($scopeType, $scopeId) ?? [];
            $this->setDataByScopeId($scopeType, $scopeId, $scopeData);
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
        if (!$this->cacheState->isEnabled(Config::TYPE_IDENTIFIER)) {
            return $this->readData();
        }

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
     * @return array
     */
    private function loadDefaultScopeData()
    {
        if (!$this->cacheState->isEnabled(Config::TYPE_IDENTIFIER)) {
            return $this->readData();
        }

        $loadAction = function () {
            $scopeType = ScopeInterface::SCOPE_DEFAULT;
            $cachedData = $this->cache->load($this->configType . '_' . $scopeType);
            $scopeData = false;
            if ($cachedData !== false) {
                try {
                    $scopeData = [$scopeType => $this->serializer->unserialize($this->encryptor->decrypt($cachedData))];
                } catch (\InvalidArgumentException $e) {
                    $this->logger->warning($e->getMessage());
                    $scopeData = false;
                }
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
        if (!$this->cacheState->isEnabled(Config::TYPE_IDENTIFIER)) {
            return $this->readData();
        }

        $loadAction = function () use ($scopeType, $scopeId) {
            /* Note: configType . '_scopes' needs to be loaded first to avoid race condition where cache finishes
               saving after configType . '_' . $scopeType . '_' . $scopeId but before configType . '_scopes'. */
            $cachedScopeData = $this->cache->load($this->configType . '_scopes');
            $cachedData = $this->cache->load($this->configType . '_' . $scopeType . '_' . $scopeId);
            $scopeData = false;
            if ($cachedData === false) {
                if ($this->availableDataScopes === null) {
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
     * Sets data according to scope type.
     *
     * @param string|null $scopeType
     * @param array $scopeData
     * @return void
     */
    private function setDataByScopeType(?string $scopeType, array $scopeData): void
    {
        if (!isset($this->data[$scopeType]) && isset($scopeData[$scopeType])) {
            $this->data[$scopeType] = $scopeData[$scopeType];
        }
    }

    /**
     * Sets data according to scope type and id.
     *
     * @param string|null $scopeType
     * @param string|null $scopeId
     * @param array $scopeData
     * @return void
     */
    private function setDataByScopeId(?string $scopeType, ?string $scopeId, array $scopeData): void
    {
        if (!isset($this->data[$scopeType][$scopeId]) && isset($scopeData[$scopeType][$scopeId])) {
            $this->data[$scopeType][$scopeId] = $scopeData[$scopeType][$scopeId];
        }
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
        $cleanAction = function () {
            $this->cacheData($this->readData()); // Note: If cache is enabled, pre-load the new config data.
        };
        $this->data = [];
        if (!$this->cacheState->isEnabled(Config::TYPE_IDENTIFIER)) {
            // Note: If cache is disabled, we still clean cache in case it will be enabled later
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
            return;
        }
        $this->lockQuery->lockedCleanData(self::$lockName, $cleanAction);
    }

    /**
     * Prepares data for cache by serializing and encrypting them
     *
     * Prepares data per scope to avoid reading data for all scopes on every request
     *
     * @param array $data
     * @return array
     */
    private function prepareDataForCache(array $data) :array
    {
        $dataToSave = [];
        $dataToSave[] = [
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data)),
            $this->configType,
            [System::CACHE_TAG]
        ];
        $dataToSave[] = [
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data['default'])),
            $this->configType . '_default',
            [System::CACHE_TAG]
        ];
        $scopes = [];
        foreach ([StoreScope::SCOPE_WEBSITES, StoreScope::SCOPE_STORES] as $curScopeType) {
            foreach ($data[$curScopeType] ?? [] as $curScopeId => $curScopeData) {
                $scopes[$curScopeType][$curScopeId] = 1;
                $dataToSave[] = [
                    $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($curScopeData)),
                    $this->configType . '_' . $curScopeType . '_' . $curScopeId,
                    [System::CACHE_TAG]
                ];
            }
        }
        $dataToSave[] = [
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($scopes)),
            $this->configType . '_scopes',
            [System::CACHE_TAG]
        ];
        return $dataToSave;
    }

    /**
     * Cache prepared configuration data.
     *
     * Takes data prepared by prepareDataForCache
     *
     * @param array $dataToSave
     * @return void
     */
    private function cachePreparedData(array $dataToSave) : void
    {
        foreach ($dataToSave as $datumToSave) {
            $this->cache->save($datumToSave[0], $datumToSave[1], $datumToSave[2]);
        }
    }

    /**
     * Gets configuration then cleans and warms it while locked
     *
     * This is to reduce the lock time after flushing config cache.
     *
     * @param callable $cleaner
     * @return void
     */
    public function cleanAndWarmDefaultScopeData(callable $cleaner)
    {
        if (!$this->cacheState->isEnabled(Config::TYPE_IDENTIFIER)) {
            $cleaner();
            return;
        }
        $loadAction = function () {
            return false;
        };
        $dataCollector = function () use ($cleaner) {
            /* Note: call to readData() needs to be inside lock to avoid race conditions such as multiple
               saves at the same time. */
            $newData = $this->readData();
            $preparedData = $this->prepareDataForCache($newData);
            unset($newData);
            $cleaner(); // Note: This is where other readers start waiting for us to finish saving cache.
            return $preparedData;
        };
        $dataSaver = function (array $preparedData) {
            $this->cachePreparedData($preparedData);
        };
        $this->lockQuery->lockedLoadData(self::$lockName, $loadAction, $dataCollector, $dataSaver);
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
