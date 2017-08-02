<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Represents loaded and cached configuration data, should be used to gain access to different types
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @api
 * @since 2.0.0
 */
class Data implements \Magento\Framework\Config\DataInterface
{
    /**
     * Configuration reader
     *
     * @var ReaderInterface
     * @since 2.0.0
     */
    protected $_reader;

    /**
     * Configuration cache
     *
     * @var CacheInterface
     * @since 2.0.0
     */
    protected $_cache;

    /**
     * Cache tag
     *
     * @var string
     * @since 2.0.0
     */
    protected $_cacheId;

    /**
     * Cache tags
     *
     * @var array
     * @since 2.0.0
     */
    protected $cacheTags = [];

    /**
     * Config data
     *
     * @var array
     * @since 2.0.0
     */
    protected $_data = [];

    /**
     * @var ReaderInterface
     * @since 2.0.0
     */
    private $reader;

    /**
     * @var CacheInterface
     * @since 2.0.0
     */
    private $cache;

    /**
     * @var string
     * @since 2.0.0
     */
    private $cacheId;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        SerializerInterface $serializer = null
    ) {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->initData();
    }

    /**
     * Initialise data for configuration
     *
     * @return void
     * @since 2.0.0
     */
    protected function initData()
    {
        $data = $this->cache->load($this->cacheId);
        if (false === $data) {
            $data = $this->reader->read();
            $this->cache->save($this->serializer->serialize($data), $this->cacheId, $this->cacheTags);
        } else {
            $data = $this->serializer->unserialize($data);
        }

        $this->merge($data);
    }

    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function reset()
    {
        $this->cache->remove($this->cacheId);
    }
}
