<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Decorator class for compressing data before storing in cache
 *
 * @todo re-implement as a cache frontend decorator similarly to \Magento\Framework\Cache\Frontend\Decorator\*
 */
namespace Magento\Framework\Cache\Backend\Decorator;

class Compression extends \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator
{
    /**
     * Prefix of compressed strings
     */
    const COMPRESSION_PREFIX = 'CACHE_COMPRESSION';

    /**
     * Array of specific options. Made in separate array to distinguish from parent options
     * @var array
     */
    protected $_decoratorOptions = ['compression_threshold' => 512];

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string  $cacheId                Cache id
     * @param  boolean $noTestCacheValidity    If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($cacheId, $noTestCacheValidity = false)
    {
        $data = $this->_backend->load($cacheId, $noTestCacheValidity);

        if ($data && $this->_isDecompressionNeeded($data)) {
            $data = self::_decompressData($data);
        }

        return $data;
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string $data              Datas to cache
     * @param string $cacheId           Cache id
     * @param string[] $tags            Array of strings, the cache record will be tagged by each string entry
     * @param bool $specificLifetime    If != false, set a specific lifetime for this cache record
     *                                  (null => infinite lifetime)
     * @param int $priority             integer between 0 (very low priority) and 10 (maximum priority) used by
     *                                  some particular backends
     * @return bool true if no problem
     */
    public function save($data, $cacheId, $tags = [], $specificLifetime = false, $priority = 8)
    {
        if ($this->_isCompressionNeeded($data)) {
            $data = self::_compressData($data);
        }

        return $this->_backend->save($data, $cacheId, $tags, $specificLifetime, $priority);
    }

    /**
     * Compress data and add specific prefix to distinguish compressed and non-compressed data
     *
     * @param string $data
     * @return string
     */
    protected static function _compressData($data)
    {
        return self::COMPRESSION_PREFIX . gzcompress($data);
    }

    /**
     * Get whether compression is needed
     *
     * @param string $data
     * @return bool
     */
    protected function _isCompressionNeeded($data)
    {
        return strlen($data) > (int)$this->_decoratorOptions['compression_threshold'];
    }

    /**
     * Remove special prefix and decompress data
     *
     * @param string $data
     * @return string
     */
    protected static function _decompressData($data)
    {
        return gzuncompress(substr($data, strlen(self::COMPRESSION_PREFIX)));
    }

    /**
     * Get whether decompression is needed
     *
     * @param string $data
     * @return bool
     */
    protected function _isDecompressionNeeded($data)
    {
        return strpos($data, self::COMPRESSION_PREFIX) === 0;
    }
}
