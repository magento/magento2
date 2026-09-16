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
     * Save both cache entries in one Redis transaction, preventing reads from seeing only one entry.
     *
     * Mirrors the non-Lua branch of the parent's own save() for each entry, but queues both
     * inside one MULTI/EXEC block instead of issuing two independent save() calls (AC-18194).
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param bool|int|null $specificLifetime
     * @param string $pairedData
     * @param string $pairedId
     * @param array $pairedTags
     * @param bool|int|null $pairedSpecificLifetime
     * @return bool
     */
    public function saveAtomicPair(
        string $data,
        string $id,
        array $tags,
        $specificLifetime,
        string $pairedData,
        string $pairedId,
        array $pairedTags,
        $pairedSpecificLifetime
    ): bool {
        $tags = array_values(array_flip(array_flip($tags)));
        $pairedTags = array_values(array_flip(array_flip($pairedTags)));

        $lifetime = $this->_getAutoExpiringLifetime($this->getLifetime($specificLifetime), $id);
        $lifetime = $lifetime === null ? $lifetime : (int) $lifetime;
        $pairedLifetime = $this->_getAutoExpiringLifetime($this->getLifetime($pairedSpecificLifetime), $pairedId);
        $pairedLifetime = $pairedLifetime === null ? $pairedLifetime : (int) $pairedLifetime;

        $oldTags = $this->_fetchOwnTags($id);
        $oldPairedTags = $this->_fetchOwnTags($pairedId);

        $this->_redis->pipeline()->multi();
        $this->_queueSave($id, $data, $tags, $lifetime, $oldTags);
        $this->_queueSave($pairedId, $pairedData, $pairedTags, $pairedLifetime, $oldPairedTags);
        $result = $this->_redis->exec();

        return isset($result[0]) && (bool) $result[0];
    }

    /**
     * Remove two cache entries (e.g. a value and its paired version marker) as a single Redis
     * transaction, so a concurrent read can never observe one removed and not the other
     * (AC-18194).
     *
     * @param string $id
     * @param string $pairedId
     * @return bool
     */
    public function removeAtomicPair(string $id, string $pairedId): bool
    {
        $tags = $this->_fetchOwnTags($id);
        $pairedTags = $this->_fetchOwnTags($pairedId);

        $this->_redis->pipeline()->multi();
        $this->_queueRemove($id, $tags);
        $this->_queueRemove($pairedId, $pairedTags);
        $result = $this->_redis->exec();

        return isset($result[0]) && (bool) $result[0];
    }

    /**
     * Read the tags currently stored against an id's own hash, same as the parent's remove().
     *
     * @param string $id
     * @return array
     */
    private function _fetchOwnTags(string $id): array
    {
        return explode(',', (string) $this->_decodeData($this->_redis->hGet(self::PREFIX_KEY . $id, self::FIELD_TAGS)));
    }

    /**
     * Queue the commands for one save(), identical to the parent's non-Lua save() body.
     *
     * @param string $id
     * @param string $data
     * @param array $tags
     * @param int|null $lifetime
     * @param array $oldTags
     * @return void
     */
    private function _queueSave(string $id, string $data, array $tags, $lifetime, array $oldTags): void
    {
        $this->_redis->hMSet(self::PREFIX_KEY . $id, [
            self::FIELD_DATA => $this->_encodeData($data, $this->_compressData),
            self::FIELD_TAGS => $this->_encodeData(implode(',', $tags), $this->_compressTags),
            self::FIELD_MTIME => time(),
            self::FIELD_INF => $lifetime === null ? 1 : 0,
        ]);

        if ($lifetime !== false && $lifetime !== null) {
            $this->_redis->expire(self::PREFIX_KEY . $id, min($lifetime, self::MAX_LIFETIME));
        }

        if ($tags) {
            $this->_redis->sAdd(self::SET_TAGS, $tags);
            foreach ($tags as $tag) {
                $this->_redis->sAdd(self::PREFIX_TAG_IDS . $tag, $id);
            }
        }

        if ($remTags = ($oldTags ? array_diff($oldTags, $tags) : false)) {
            foreach ($remTags as $tag) {
                if ($tag !== '') {
                    $this->_redis->sRem(self::PREFIX_TAG_IDS . $tag, $id);
                }
            }
        }

        if ($this->_notMatchingTags) {
            $this->_redis->sAdd(self::SET_IDS, $id);
        }
    }

    /**
     * Queue the commands for one remove(), identical to the parent's own remove() body.
     *
     * @param string $id
     * @param array $tags
     * @return void
     */
    private function _queueRemove(string $id, array $tags): void
    {
        $this->_redis->unlink(self::PREFIX_KEY . $id);

        if ($this->_notMatchingTags) {
            $this->_redis->sRem(self::SET_IDS, $id);
        }

        foreach ($tags as $tag) {
            if ($tag !== '') {
                $this->_redis->sRem(self::PREFIX_TAG_IDS . $tag, $id);
            }
        }
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
