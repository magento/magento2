<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\App\ResourceConnection\ConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class \Magento\Quote\Model\QueryResolver
 *
 * @since 2.0.0
 */
class QueryResolver
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $data = [];

    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    private $config;

    /**
     * @var CacheInterface
     * @since 2.0.0
     */
    private $cache;

    /**
     * @var string
     * @since 2.0.0
     */
    private $cacheId;

    /**
     * Cache tags
     *
     * @var array
     * @since 2.0.0
     */
    private $cacheTags = [];

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param ConfigInterface $config
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface $serializer
     * @since 2.0.0
     */
    public function __construct(
        ConfigInterface $config,
        CacheInterface $cache,
        $cacheId = 'connection_config_cache',
        SerializerInterface $serializer = null
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Get flag value
     *
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @return void
     * @since 2.0.0
     */
    public function merge(array $config)
    {
        $this->data = array_replace_recursive($this->data, $config);
    }
}
