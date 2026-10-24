<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
     * Whether to use lua on garbage collection
     *
     * @var bool
     */
    private bool $useLuaOnGc;

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->preloadKeys = $options['preload_keys'] ?? [];
        parent::__construct($options);
        $this->useLuaOnGc = isset($options['use_lua_on_gc']) ? (bool) $options['use_lua_on_gc'] : (bool) $this->_useLua;
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

            $redisResponse = $redis->exec();
            $this->preloadedData = is_array($redisResponse) ?
                array_filter(array_combine($this->preloadKeys, $redisResponse)) :
                [];
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
    public function save($data, $id, $tags = [], $specificLifetime = 86_400_000)
    {
        // @todo add special handling of MAGE tag, save clenup
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

    /**
     * @inheritDoc
     */
    protected function _collectGarbage()
    {
        $useLua = $this->_useLua;
        $this->_useLua = $this->useLuaOnGc;
        try {
            parent::_collectGarbage();
        } finally {
            $this->_useLua = $useLua;
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
