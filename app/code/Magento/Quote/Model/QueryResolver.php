<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\App\ResourceConnection\ConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

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
     * @var SerializerInterface
     */
    private $serializer;

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
     * @param SerializerInterface $serializer
     * @param string $cacheId
     */
    public function __construct(
        ConfigInterface $config,
        CacheInterface $cache,
        SerializerInterface $serializer,
        $cacheId = 'connection_config_cache'
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->serializer = $serializer;
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
     *
     * @return void
     */
    protected function initData()
    {
        $data = $this->cache->load($this->cacheId);
        if (false === $data) {
            $singleQuery = $this->config->getConnectionName('checkout_setup') == 'default' ? true : false;
            $data['checkout'] = $singleQuery;
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
     *
     * @return void
     */
    public function merge(array $config)
    {
        $this->data = array_replace_recursive($this->data, $config);
    }
}
