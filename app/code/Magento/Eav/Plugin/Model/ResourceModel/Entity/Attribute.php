<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Plugin\Model\ResourceModel\Entity;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;

class Attribute
{
    /**
     * Cache key for store label attribute
     */
    const STORE_LABEL_ATTRIBUTE = 'EAV_STORE_LABEL_ATTRIBUTE';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StateInterface
     */
    private $cacheState;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param SerializerInterface $serializer
     * @codeCoverageIgnore
     */
    public function __construct(
        CacheInterface $cache,
        StateInterface $cacheState,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
    }

    /**
     * @param AttributeResource $subject
     * @param callable $proceed
     * @param int $attributeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetStoreLabelsByAttributeId(
        AttributeResource $subject,
        \Closure $proceed,
        $attributeId
    ) {
        $cacheId = self::STORE_LABEL_ATTRIBUTE . $attributeId;
        if ($this->isCacheEnabled() && ($storeLabels = $this->cache->load($cacheId))) {
            return $this->serializer->unserialize($storeLabels);
        }
        $storeLabels = $proceed($attributeId);
        if ($this->isCacheEnabled()) {
            $this->cache->save(
                $this->serializer->serialize($storeLabels),
                $cacheId,
                [
                    Type::CACHE_TAG,
                    EntityAttribute::CACHE_TAG
                ]
            );
        }
        return $storeLabels;
    }

    /**
     * Check if cache is enabled
     * 
     * @return bool
     */
    private function isCacheEnabled()
    {
        return $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER);
    }
}
