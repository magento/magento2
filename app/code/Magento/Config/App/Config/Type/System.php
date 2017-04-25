<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\DataObject;

/**
 * Class process source, cache them and retrieve value by path
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
     * @var DataObject
     */
    private $data;

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
     * Key name for flag which displays whether configuration is cached or not.
     *
     * Once configuration is cached additional flag pushed to cache storage
     * to be able check cache existence without data load.
     *
     * @var string
     */
    private $cacheExistenceKey;

    /**
     * @param \Magento\Framework\App\Config\ConfigSourceInterface $source
     * @param \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
     * @param \Magento\Store\Model\Config\Processor\Fallback $fallback
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor
     * @param int $cachingNestedLevel
     * @param string $configType
     */
    public function __construct(
        \Magento\Framework\App\Config\ConfigSourceInterface $source,
        \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor,
        \Magento\Store\Model\Config\Processor\Fallback $fallback,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor,
        $cachingNestedLevel = 1,
        $configType = self::CONFIG_TYPE
    ) {
        $this->source = $source;
        $this->postProcessor = $postProcessor;
        $this->preProcessor = $preProcessor;
        $this->cache = $cache;
        $this->cachingNestedLevel = $cachingNestedLevel;
        $this->fallback = $fallback;
        $this->serializer = $serializer;
        $this->configType = $configType;
        $this->cacheExistenceKey = $this->configType . '_CACHE_EXISTS';
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        if ($path === null) {
            $path = '';
        }
        if ($this->isConfigRead($path)) {
            return $this->data->getData($path);
        }

        if (!empty($path) && $this->isCacheExists()) {
            return $this->readFromCache($path);
        }

        $config = $this->loadConfig();
        $this->cacheConfig($config);
        $this->data = new DataObject($config);
        return $this->data->getData($path);
    }

    /**
     * Check whether configuration is cached
     *
     * In case configuration cache exists method 'load' returns
     * value equal to $this->cacheExistenceKey
     *
     * @return bool
     */
    private function isCacheExists()
    {
        return $this->cache->load($this->cacheExistenceKey) !== false;
    }

    /**
     * Explode path by '/'(forward slash) separator
     *
     * In case $path string contains forward slash symbol(/) the $path is exploded and parts array is returned
     * In other case empty array is returned
     *
     * @param string $path
     * @return array
     */
    private function getPathParts($path)
    {
        $pathParts = [];
        if (strpos($path, '/') !== false) {
            $pathParts = explode('/', $path);
        }
        return $pathParts;
    }

    /**
     * Check whether requested configuration data is read to memory
     *
     * Because of configuration is cached partially each part can be loaded separately
     * Method performs check if corresponding system configuration part is already loaded to memory
     * and value can be retrieved directly without cache look up
     *
     *
     * @param string $path
     * @return bool
     */
    private function isConfigRead($path)
    {
        $pathParts = $this->getPathParts($path);
        return !empty($pathParts) && isset($this->data[$pathParts[0]][$pathParts[1]]);
    }

    /**
     * Load configuration from all the sources
     *
     * System configuration is loaded in 3 steps performing consecutive calls to
     * Pre Processor, Fallback Processor, Post Processor accordingly
     *
     * @return array
     */
    private function loadConfig()
    {
        $data = $this->preProcessor->process($this->source->get());
        $this->data = new DataObject($data);
        $data = $this->fallback->process($data);
        $this->data = new DataObject($data);

        return $this->postProcessor->process($data);
    }

    /**
     *
     * Load configuration and caching it by parts.
     *
     * To be cached configuration is loaded first.
     * Then it is cached by parts to minimize memory usage on load.
     * Additional flag cached as well to give possibility check cache existence without data load.
     *
     * @param array $data
     * @return void
     */
    private function cacheConfig($data)
    {
        foreach ($data as $scope => $scopeData) {
            foreach ($scopeData as $key => $config) {
                $this->cache->save(
                    $this->serializer->serialize($config),
                    $this->configType . '_' . $scope . $key,
                    [self::CACHE_TAG]
                );
            }
        }
        $this->cache->save($this->cacheExistenceKey, $this->cacheExistenceKey, [self::CACHE_TAG]);
    }

    /**
     * Read cached configuration
     *
     * Read section of system configuration corresponding to requested $path from cache
     * Configuration stored to internal property right after load to prevent additional
     * requests to cache storage
     *
     * @param string $path
     * @return mixed
     */
    private function readFromCache($path)
    {
        if ($this->data === null) {
            $this->data = new DataObject();
        }

        $result = null;
        $pathParts = $this->getPathParts($path);
        if (!empty($pathParts)) {
            $result = $this->cache->load($this->configType . '_' . $pathParts[0] . $pathParts[1]);
            if ($result !== false) {
                $readData = $this->data->getData();
                $readData[$pathParts[0]][$pathParts[1]] = $this->serializer->unserialize($result);
                $this->data->setData($readData);
            }
        }

        return $this->data->getData($path);
    }

    /**
     * Clean cache and global variables cache
     *
     * Next items cleared:
     * - Internal property intended to store already loaded configuration data
     * - All records in cache storage tagged with CACHE_TAG
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
    }
}
