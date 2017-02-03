<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\App\ResourceConnection\ConfigInterface;

class QueryResolver
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * Cache tags
     *
     * @var array
     */
    private $cacheTags = [];

    /**
     * @param ConfigInterface $config
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        ConfigInterface $config,
        CacheInterface $cache,
        $cacheId = 'connection_config_cache'
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
    }

    /**
     * Get flag value
     *
     * @return bool
     */
    public function isSingleQuery()
    {
        if (!isset($this->data['checkout'])) {
            $this->initData();
        }
        return $this->data['checkout'];
    }

    /**
     * Initialise data for configuration
     * @return void
     */
    protected function initData()
    {
        $data = $this->cache->load($this->cacheId);
        if (false === $data) {
            $singleQuery = $this->config->getConnectionName('checkout_setup') == 'default' ? true : false;
            $data['checkout'] = $singleQuery;
            $this->cache->save(serialize($data), $this->cacheId, $this->cacheTags);
        } else {
            $data = unserialize($data);
        }
        $this->merge($data);
    }

    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        $this->data = array_replace_recursive($this->data, $config);
    }
}
