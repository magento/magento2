<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Acl\AclResource;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class Provider implements ProviderInterface
{
    /**
     * Cache key for ACL roles cache
     */
    const ACL_RESOURCES_CACHE_KEY = 'provider_acl_resources_cache';

    /**
     * @var \Magento\Framework\Config\ReaderInterface
     */
    protected $_configReader;

    /**
     * @var TreeBuilder
     */
    protected $_resourceTreeBuilder;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface
     */
    private $cache;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Config\ReaderInterface $configReader
     * @param TreeBuilder $resourceTreeBuilder
     * @param \Magento\Framework\Acl\Data\CacheInterface $cache
     * @param Json $serializer
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $configReader,
        TreeBuilder $resourceTreeBuilder,
        \Magento\Framework\Acl\Data\CacheInterface $cache = null,
        Json $serializer = null
    ) {
        $this->_configReader = $configReader;
        $this->_resourceTreeBuilder = $resourceTreeBuilder;
        $this->cache = $cache ?: ObjectManager::getInstance()->get(\Magento\Framework\Config\CacheInterface::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAclResources()
    {
        $tree = $this->cache->load(self::ACL_RESOURCES_CACHE_KEY);
        if ($tree) {
            return $this->serializer->unserialize($tree);
        }
        $aclResourceConfig = $this->_configReader->read();
        if (!empty($aclResourceConfig['config']['acl']['resources'])) {
            $tree = $this->_resourceTreeBuilder->build($aclResourceConfig['config']['acl']['resources']);
            $this->cache->save($this->serializer->serialize($tree), self::ACL_RESOURCES_CACHE_KEY);
            return $tree;
        }
        return [];
    }
}
