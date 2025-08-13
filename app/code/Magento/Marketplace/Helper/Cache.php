<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Config\CacheInterface $cache,
        ?SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
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
            $data = $this->serializer->unserialize($data);
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
        return $this->getCache()->save($this->serializer->serialize($partners), $this->pathToCacheFile);
    }

    /**
     * @return \Magento\Framework\Config\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }
}
