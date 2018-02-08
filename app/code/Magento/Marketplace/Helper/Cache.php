<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Helper;

use Magento\Framework\Filesystem;

/**
 * Cache helper
 */
class Cache extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    protected $pathToCacheFile = 'partners';

    /**
     * Configuration cache model
     *
     * @var \Magento\Framework\Config\CacheInterface
     */
    protected $cache;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Config\CacheInterface $cache
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Config\CacheInterface $cache
    ) {
        $this->cache = $cache;
        parent::__construct($context);
    }

    /**
     * Load partners from cache
     *
     * @return array
     */
    public function loadPartnersFromCache()
    {
        $data = $this->getCache()->load($this->pathToCacheFile);
        if (false !== $data) {
            $data = unserialize($data);
        }
        return $data;
    }

    /**
     * Save composer packages available for update to cache
     *
     * @param string $partners
     * @return bool
     */
    public function savePartnersToCache($partners)
    {
        return $this->getCache()->save(serialize($partners), $this->pathToCacheFile);
    }

    /**
     * @return \Magento\Framework\Config\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }
}
