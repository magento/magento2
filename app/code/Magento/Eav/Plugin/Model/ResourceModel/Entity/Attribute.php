<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Plugin\Model\ResourceModel\Entity;

use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class \Magento\Eav\Plugin\Model\ResourceModel\Entity\Attribute
 *
 * @since 2.0.0
 */
class Attribute
{
    /**
     * Cache key for store label attribute
     */
    const STORE_LABEL_ATTRIBUTE = 'EAV_STORE_LABEL_ATTRIBUTE';

    /**
     * @var CacheInterface
     * @since 2.0.0
     */
    private $cache;

    /**
     * @var StateInterface
     * @since 2.2.0
     */
    private $cacheState;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param SerializerInterface $serializer
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.2.0
     */
    private function isCacheEnabled()
    {
        return $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER);
    }
}
