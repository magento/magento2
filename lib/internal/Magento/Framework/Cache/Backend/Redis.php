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
     * Remove cache entries by id, also removing them from each of their tags' id index.
     *
     * Upstream only unlinks the data keys here and leaves the ids behind in every zc:ti:<tag>
     * set, unlike its own single-id remove() which cleans both
     *
     * @param array $ids
     */
    protected function _removeByIds($ids)
    {
        if (!$ids) {
            return;
        }

        foreach (array_chunk($ids, $this->_removeChunkSize) as $idsChunk) {
            $idsPerTag = $this->_groupIdsByTag($idsChunk, $this->_fetchTagsPerId($idsChunk));

            $this->_redis->pipeline()->multi();

            // Remove data.
            $this->_redis->unlink($this->_preprocessIds($idsChunk));

            // Remove IDs from the list of all IDs.
            if ($this->_notMatchingTags) {
                $this->_redis->sRem(self::SET_IDS, $idsChunk);
            }

            // Remove all IDs belonging to each tag in one command.
            foreach ($idsPerTag as $tag => $tagIds) {
                $this->_redis->sRem(self::PREFIX_TAG_IDS . $tag, $tagIds);
            }

            $this->_redis->exec();
        }
    }

    /**
     * Remove cache entries matching any of the given tags, also cleaning up their full tag index.
     *
     * Upstream's non-Lua fallback only unlinks the id-lists of the tags passed in, leaving a
     * removed id behind in the index of any *other* tag it also carries. Delegating
     * id removal to {@see _removeByIds()} fixes that, since it looks up and clears every tag
     * an id actually belongs to rather than only the ones supplied here.
     *
     * @param array $tags
     */
    protected function _removeByMatchingAnyTags($tags)
    {
        if ($this->_useLua) {
            parent::_removeByMatchingAnyTags($tags);
            return;
        }

        $ids = $this->getIdsMatchingAnyTags($tags);

        $this->_removeByIds($ids);

        $this->_redis->pipeline()->multi();

        // Remove tag id lists.
        $this->_redis->unlink($this->_preprocessTagIds($tags));

        // Remove tags from list of tags.
        $this->_redis->sRem(self::SET_TAGS, $tags);

        $this->_redis->exec();
    }

    /**
     * Fetch each id's stored tag list (raw, encoded) in a single pipelined round trip.
     *
     * @param array $idsChunk
     * @return array
     */
    private function _fetchTagsPerId(array $idsChunk): array
    {
        $this->_redis->pipeline();

        foreach ($idsChunk as $id) {
            $this->_redis->hGet(self::PREFIX_KEY . $id, self::FIELD_TAGS);
        }

        $result = $this->_redis->exec();

        if (!is_array($result) || count($result) !== count($idsChunk)) {
            // Surface a failed/partial pipeline instead of silently skipping tag-index cleanup
            // for the whole chunk (AC-18197).
            trigger_error(
                'Magento\Framework\Cache\Backend\Redis::_fetchTagsPerId(): tag lookup pipeline '
                . 'failed or returned a partial result; tag index cleanup will be skipped for '
                . 'this chunk of ' . count($idsChunk) . ' id(s).',
                E_USER_WARNING
            );

            return [];
        }

        return $result;
    }

    /**
     * Invert id→tags to tag→ids, allowing a single SREM per tag instead of one SREM per (id, tag) pair.
     *
     * @param array $idsChunk
     * @param array $tagsPerId Raw hGet results, keyed by the same index as $idsChunk.
     * @return array
     */
    private function _groupIdsByTag(array $idsChunk, array $tagsPerId): array
    {
        $idsPerTag = [];

        foreach ($idsChunk as $index => $id) {
            foreach (explode(',', $this->_decodeData($tagsPerId[$index] ?? false)) as $tag) {
                if ($tag !== '') {
                    $idsPerTag[$tag][] = $id;
                }
            }
        }

        return $idsPerTag;
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
