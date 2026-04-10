<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader\Compiled;
use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Serialize\SerializerInterface;

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
     * @var ConfigWriterInterface|null
     */
    private $configWriter;

    /**
     * Constructor
     *
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * @param array|null $cacheTags
     * @param ConfigWriterInterface|null $configWriter
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        ?SerializerInterface $serializer = null,
        ?array $cacheTags = null,
        ?ConfigWriterInterface $configWriter = null,
    ) {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->configWriter = $configWriter;
        if ($cacheTags) {
            $this->cacheTags = $cacheTags;
        }
        $this->initData();
    }

    /**
     * Initialise data for configuration
     *
     * @return void
     */
    protected function initData()
    {
        if ($this->configWriter && $this->isCompiledConfigAvailable($this->cacheId)) {
            $this->merge($this->loadCompiledConfig($this->cacheId));
            return;
        }

        $data = $this->cache->load($this->cacheId);
        if (false === $data) {
            $data = $this->reader->read();
            $this->cache->save($this->serializer->serialize($data), $this->cacheId, $this->cacheTags);
        } else {
            $data = $this->serializer->unserialize($data);
        }

        if ($this->configWriter) {
            $this->configWriter->write($this->cacheId, $data);
        }

        $this->merge($data);
    }

    /**
     * Check if a compiled PHP config file is available
     *
     * @param string $key
     * @return bool
     */
    protected function isCompiledConfigAvailable(string $key): bool
    {
        return file_exists(Compiled::getFilePath($key));
    }

    /**
     * Load configuration from a compiled PHP file
     *
     * @param string $key
     * @return array
     */
    protected function loadCompiledConfig(string $key): array
    {
        return include Compiled::getFilePath($key);
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
        $this->removeCompiledConfig($this->cacheId);
        $this->_data = [];
        $configData = $this->reader->read();
        if ($configData) {
            $this->merge($configData);
        }
    }

    /**
     * Remove compiled PHP config file if it exists
     *
     * @param string $key
     * @return void
     */
    protected function removeCompiledConfig(string $key): void
    {
        if (!$this->configWriter) {
            return;
        }
        $compiledPath = Compiled::getFilePath($key);
        if (file_exists($compiledPath)) {
            @unlink($compiledPath);
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
