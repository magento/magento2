<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Config\App\Config\Type\System\Reader;

/**
 * System configuration type
 * @api
 * @since 100.1.2
 */
class System implements ConfigTypeInterface
{
    const CACHE_TAG = 'config_scopes';

    const CONFIG_TYPE = 'system';

    /**
     * @var \Magento\Framework\App\Config\ConfigSourceInterface
     */
    private $source;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var \Magento\Framework\App\Config\Spi\PostProcessorInterface
     */
    private $postProcessor;

    /**
     * @var \Magento\Framework\App\Config\Spi\PreProcessorInterface
     */
    private $preProcessor;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cachingNestedLevel;

    /**
     * @var \Magento\Store\Model\Config\Processor\Fallback
     */
    private $fallback;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
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
    private $availableDataScopes = null;

    /**
     * @param \Magento\Framework\App\Config\ConfigSourceInterface $source
     * @param \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
     * @param \Magento\Store\Model\Config\Processor\Fallback $fallback
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor
     * @param int $cachingNestedLevel
     * @param string $configType
     * @param Reader $reader
     */
    public function __construct(
        \Magento\Framework\App\Config\ConfigSourceInterface $source,
        \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor,
        \Magento\Store\Model\Config\Processor\Fallback $fallback,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor,
        $cachingNestedLevel = 1,
        $configType = self::CONFIG_TYPE,
        Reader $reader = null
    ) {
        $this->source = $source;
        $this->postProcessor = $postProcessor;
        $this->preProcessor = $preProcessor;
        $this->cache = $cache;
        $this->cachingNestedLevel = $cachingNestedLevel;
        $this->fallback = $fallback;
        $this->serializer = $serializer;
        $this->configType = $configType;
        $this->reader = $reader ?: ObjectManager::getInstance()->get(Reader::class);
    }

    /**
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
        $pathParts = explode('/', $path);
        if (count($pathParts) === 1 && $pathParts[0] !== 'default') {
            if (!isset($this->data[$pathParts[0]])) {
                $data = $this->readData();
                $this->data = array_replace_recursive($data, $this->data);
            }
            return $this->data[$pathParts[0]];
        }
        $scopeType = array_shift($pathParts);
        if ($scopeType === 'default') {
            if (!isset($this->data[$scopeType])) {
                $this->data = array_replace_recursive($this->loadDefaultScopeData($scopeType), $this->data);
            }
            return $this->getDataByPathParts($this->data[$scopeType], $pathParts);
        }
        $scopeId = array_shift($pathParts);
        if (!isset($this->data[$scopeType][$scopeId])) {
            $this->data = array_replace_recursive($this->loadScopeData($scopeType, $scopeId), $this->data);
        }
        return isset($this->data[$scopeType][$scopeId])
            ? $this->getDataByPathParts($this->data[$scopeType][$scopeId], $pathParts)
            : null;
    }

    /**
     * Load configuration data for all scopes
     *
     * @return array
     */
    private function loadAllData()
    {
        $cachedData = $this->cache->load($this->configType);
        if ($cachedData === false) {
            $data = $this->readData();
        } else {
            $data = $this->serializer->unserialize($cachedData);
        }
        return $data;
    }

    /**
     * Load configuration data for default scope
     *
     * @param string $scopeType
     * @return array
     */
    private function loadDefaultScopeData($scopeType)
    {
        $cachedData = $this->cache->load($this->configType . '_' . $scopeType);
        if ($cachedData === false) {
            $data = $this->readData();
            $this->cacheData($data);
        } else {
            $data = [$scopeType => $this->serializer->unserialize($cachedData)];
        }
        return $data;
    }

    /**
     * Load configuration data for a specified scope
     *
     * @param string $scopeType
     * @param string $scopeId
     * @return array
     */
    private function loadScopeData($scopeType, $scopeId)
    {
        $cachedData = $this->cache->load($this->configType . '_' . $scopeType . '_' . $scopeId);
        if ($cachedData === false) {
            if ($this->availableDataScopes === null) {
                $cachedScopeData = $this->cache->load($this->configType . '_scopes');
                if ($cachedScopeData !== false) {
                    $this->availableDataScopes = $this->serializer->unserialize($cachedScopeData);
                }
            }
            if (is_array($this->availableDataScopes) && !isset($this->availableDataScopes[$scopeType][$scopeId])) {
                return [$scopeType => [$scopeId => []]];
            }
            $data = $this->readData();
            $this->cacheData($data);
        } else {
            $data = [$scopeType => [$scopeId => $this->serializer->unserialize($cachedData)]];
        }
        return $data;
    }

    /**
     * Cache configuration data.
     * Caches data per scope to avoid reading data for all scopes on every request
     *
     * @param array $data
     * @return void
     */
    private function cacheData(array $data)
    {
        $this->cache->save(
            $this->serializer->serialize($data),
            $this->configType,
            [self::CACHE_TAG]
        );
        $this->cache->save(
            $this->serializer->serialize($data['default']),
            $this->configType . '_default',
            [self::CACHE_TAG]
        );
        $scopes = [];
        foreach (['websites', 'stores'] as $curScopeType) {
            foreach ($data[$curScopeType] as $curScopeId => $curScopeData) {
                $scopes[$curScopeType][$curScopeId] = 1;
                $this->cache->save(
                    $this->serializer->serialize($curScopeData),
                    $this->configType . '_' . $curScopeType . '_' . $curScopeId,
                    [self::CACHE_TAG]
                );
            }
        }
        $this->cache->save(
            $this->serializer->serialize($scopes),
            $this->configType . "_scopes",
            [self::CACHE_TAG]
        );
    }

    /**
     * Walk nested hash map by keys from $pathParts
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
     * Clean cache and global variables cache
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
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
    }
}
