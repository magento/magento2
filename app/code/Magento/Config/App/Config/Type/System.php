<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\Config\Processor\Fallback;

/**
 * Class process source, cache them and retrieve value by path
 */
class System implements ConfigTypeInterface
{
    const CACHE_TAG = 'config_scopes';

    const CONFIG_TYPE = 'system';

    /**
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * @var PostProcessorInterface
     */
    private $postProcessor;

    /**
     * @var PreProcessorInterface
     */
    private $preProcessor;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cachingNestedLevel;

    /**
     * @var Fallback
     */
    private $fallback;

    /**
     * Key name for flag which displays whether configuration is cached or not.
     *
     * Once configuration is cached additional flag pushed to cache storage
     * to be able check cache existence without data load.
     *
     * @var string
     */
    private $cacheExistenceKey = self::CONFIG_TYPE . '_CACHE_EXISTS';

    /**
     * System constructor.
     * @param ConfigSourceInterface $source
     * @param PostProcessorInterface $postProcessor
     * @param Fallback $fallback
     * @param FrontendInterface $cache
     * @param PreProcessorInterface $preProcessor
     * @param int $cachingNestedLevel
     */
    public function __construct(
        ConfigSourceInterface $source,
        PostProcessorInterface $postProcessor,
        Fallback $fallback,
        FrontendInterface $cache,
        PreProcessorInterface $preProcessor,
        $cachingNestedLevel = 1
    ) {
        $this->source = $source;
        $this->postProcessor = $postProcessor;
        $this->preProcessor = $preProcessor;
        $this->cache = $cache;
        $this->cachingNestedLevel = $cachingNestedLevel;
        $this->fallback = $fallback;
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
     * @return bool
     */
    private function isCacheExists()
    {
        return $this->cache->load($this->cacheExistenceKey) !== false;
    }

    /**
     * Explode path by '/'(forward slash) separator
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
                    serialize($config),
                    self::CONFIG_TYPE . '_' . $scope . $key,
                    [self::CACHE_TAG]
                );
            }
        }
        $this->cache->save('1', $this->cacheExistenceKey, [self::CACHE_TAG]);
    }

    /**
     * Read cached configuration
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
            $result = $this->cache->load(self::CONFIG_TYPE . '_' . $pathParts[0] . $pathParts[1]);
        }

        if ($result !== false && $result !== null) {
            $readData = $this->data->getData();
            $readData[$pathParts[0]][$pathParts[1]] = unserialize($result);
            $this->data = new DataObject($readData);
        }

        return $this->data->getData($path);
    }

    /**
     * Clean cache and global variables cache
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
    }
}
