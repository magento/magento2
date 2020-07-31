<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Backend;

/**
 * Redis wrapper to extend current implementation behaviour.
 */
class Redis extends \Cm_Cache_Backend_Redis
{
    /**
     * Local state of preloaded keys.
     *
     * @var array
     */
    private $preloadedData = [];

    /**
     * Array of keys to be preloaded.
     *
     * @var array
     */
    private $preloadKeys = [];

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->preloadKeys = $options['preload_keys'] ?? [];
        parent::__construct($options);
    }

    /**
     * Load value with given id from cache
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return bool|string
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        if (!empty($this->preloadKeys) && empty($this->preloadedData)) {
            $redis =  $this->_slave ?? $this->_redis;
            $redis = $redis->pipeline();

            foreach ($this->preloadKeys as $key) {
                $redis->hGet(self::PREFIX_KEY . $key, self::FIELD_DATA);
            }

            $this->preloadedData = array_filter(array_combine($this->preloadKeys, $redis->exec()));
        }

        if (isset($this->preloadedData[$id])) {
            return $this->_decodeData($this->preloadedData[$id]);
        }

        return parent::load($id, $doNotTestCacheValidity);
    }

    /**
     * Cover errors on save operations, which may occurs when Redis cannot evict keys, which is expected in some cases.
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param bool $specificLifetime
     * @return bool
     */
    public function save($data, $id, $tags = [], $specificLifetime = false)
    {
        try {
            $result = parent::save($data, $id, $tags, $specificLifetime);
        } catch (\Throwable $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function remove($id)
    {
        try {
            $result = parent::remove($id);
        } catch (\Throwable $exception) {
            $result = false;
        }

        return $result;
    }
}
