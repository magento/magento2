<?php
/**
 * ACL cache
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl;

class Cache implements CacheInterface
{
    /**
     * Cache
     *
     * @var \Magento\Framework\Config\CacheInterface
     */
    protected $_cache;

    /**
     * Cache key
     *
     * @var string
     */
    protected $_cacheKey;

    /**
     * @var \Magento\Framework\Acl
     */
    protected $_acl = null;

    /**
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheKey
     */
    public function __construct(\Magento\Framework\Config\CacheInterface $cache, $cacheKey)
    {
        $this->_cache = $cache;
        $this->_cacheKey = $cacheKey;
    }

    /**
     * Check whether ACL object is in cache
     *
     * @return bool
     */
    public function has()
    {
        return null !== $this->_acl || $this->_cache->test($this->_cacheKey);
    }

    /**
     * Retrieve ACL object from cache
     *
     * @return \Magento\Framework\Acl
     */
    public function get()
    {
        if (null == $this->_acl) {
            $this->_acl = unserialize($this->_cache->load($this->_cacheKey));
        }
        return $this->_acl;
    }

    /**
     * Save ACL object to cache
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function save(\Magento\Framework\Acl $acl)
    {
        $this->_acl = $acl;
        $this->_cache->save(serialize($acl), $this->_cacheKey);
    }

    /**
     * Clear ACL instance cache
     *
     * @return void
     */
    public function clean()
    {
        $this->_acl = null;
        $this->_cache->remove($this->_cacheKey);
    }
}
