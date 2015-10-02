<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Config\CacheInterface;

/**
 * Cache helper
 */
class Cache extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $directory;

    /**
     * @var string
     */
    protected $pathToCacheFile = 'partners';

    /**
     * @var Reader
     */
    protected $moduleReader;

    /**
     * Configuration cache model
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param Context $context
     * @param CacheInterface $cache
     */
    public function __construct(
        Context $context,
        CacheInterface $cache
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
     * @param $partners
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
