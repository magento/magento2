<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Represents loaded and cached configuration data, should be used to gain access to different types
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @api
 * @since 100.0.2
 */
class Data implements \Magento\Framework\Config\DataInterface
{
    /**
     * Configuration reader
     *
     * @var ReaderInterface
     */
    protected $_reader;

    /**
     * Configuration cache
     *
     * @var CacheInterface
     */
    protected $_cache;

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * @var array
     */
    protected $cacheTags = [];

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Optional mutex to prevent a cache stampede when regenerating expensive config data.
     *
     * @var LockGuardedCacheLoader|null
     */
    private $lockGuardedCacheLoader;

    /**
     * Constructor
     *
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * @param array|null $cacheTags
     * @param LockGuardedCacheLoader|null $lockGuardedCacheLoader
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        ?SerializerInterface $serializer = null,
        ?array $cacheTags = null,
        ?LockGuardedCacheLoader $lockGuardedCacheLoader = null,
    ) {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        if ($cacheTags) {
            $this->cacheTags = $cacheTags;
        }
        // Intentionally no ObjectManager fallback: null keeps the original unguarded behavior, so the
        // mutex is only engaged for cache ids that explicitly opt in via DI.
        $this->lockGuardedCacheLoader = $lockGuardedCacheLoader;
        $this->initData();
    }

    /**
     * Initialise data for configuration
     *
     * @return void
     */
    protected function initData()
    {
        if ($this->lockGuardedCacheLoader === null) {
            $data = $this->cache->load($this->cacheId);
            if (false === $data) {
                $data = $this->reader->read();
                $this->cache->save($this->serializer->serialize($data), $this->cacheId, $this->cacheTags);
            } else {
                $data = $this->serializer->unserialize($data);
            }
        } else {
            $data = $this->loadWithLock();
        }

        $this->merge($data);
    }

    /**
     * Load config data through the mutex so concurrent cold reads regenerate it once, not once per request
     *
     * @return array
     */
    private function loadWithLock(): array
    {
        $loadAction = function () {
            $cachedData = $this->cache->load($this->cacheId);
            // The loader treats only strict false as a miss; an empty serialized array is a valid hit.
            return $cachedData === false ? false : $this->serializer->unserialize($cachedData);
        };

        $data = $this->lockGuardedCacheLoader->lockedLoadData(
            $this->cacheId,
            $loadAction,
            fn () => $this->reader->read(),
            function ($data) {
                $this->cache->save($this->serializer->serialize($data), $this->cacheId, $this->cacheTags);
            }
        );

        return (array)$data;
    }

    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        $this->_data = array_replace_recursive($this->_data, $config);
    }

    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     * @return array|mixed|null
     */
    public function get($path = null, $default = null)
    {
        if ($path === null) {
            return $this->_data;
        }
        $keys = explode('/', $path);
        $data = $this->_data;
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /**
     * Clear cache data
     *
     * @return void
     */
    public function reset()
    {
        $this->cache->remove($this->cacheId);
        $this->_data = [];
        $configData = $this->reader->read();
        if ($configData) {
            $this->merge($configData);
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
