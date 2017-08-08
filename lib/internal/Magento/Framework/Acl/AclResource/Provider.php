<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Acl\AclResource;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class \Magento\Framework\Acl\AclResource\Provider
 *
 */
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
    private $aclDataCache;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @param \Magento\Framework\Config\ReaderInterface $configReader
     * @param TreeBuilder $resourceTreeBuilder
     * @param \Magento\Framework\Acl\Data\CacheInterface $aclDataCache
     * @param Json $serializer
     * @param string $cacheKey
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $configReader,
        TreeBuilder $resourceTreeBuilder,
        \Magento\Framework\Acl\Data\CacheInterface $aclDataCache = null,
        Json $serializer = null,
        $cacheKey = self::ACL_RESOURCES_CACHE_KEY
    ) {
        $this->_configReader = $configReader;
        $this->_resourceTreeBuilder = $resourceTreeBuilder;
        $this->aclDataCache = $aclDataCache ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Config\CacheInterface::class
        );
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->cacheKey = $cacheKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclResources()
    {
        $tree = $this->aclDataCache->load($this->cacheKey);
        if ($tree) {
            return $this->serializer->unserialize($tree);
        }
        $aclResourceConfig = $this->_configReader->read();
        if (!empty($aclResourceConfig['config']['acl']['resources'])) {
            $tree = $this->_resourceTreeBuilder->build($aclResourceConfig['config']['acl']['resources']);
            $this->aclDataCache->save($this->serializer->serialize($tree), $this->cacheKey);
            return $tree;
        }
        return [];
    }
}
