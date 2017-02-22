<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Plugin\Model\ResourceModel\Entity;

class Attribute
{
    /**
     * Cache key for store label attribute
     */
    const STORE_LABEL_ATTRIBUTE = 'EAV_STORE_LABEL_ATTRIBUTE';

    /** @var \Magento\Framework\App\CacheInterface */
    protected $cache;

    /** @var bool|null */
    protected $isCacheEnabled = null;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState
    ) {
        $this->cache = $cache;
        $this->isCacheEnabled = $cacheState->isEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER);
    }

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $subject
     * @param callable $proceed
     * @param int $attributeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetStoreLabelsByAttributeId(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $subject,
        \Closure $proceed,
        $attributeId
    ) {
        $cacheId = self::STORE_LABEL_ATTRIBUTE . $attributeId;
        if ($this->isCacheEnabled && ($storeLabels = $this->cache->load($cacheId))) {
            return unserialize($storeLabels);
        }
        $storeLabels = $proceed($attributeId);
        if ($this->isCacheEnabled) {
            $this->cache->save(
                serialize($storeLabels),
                $cacheId,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                ]
            );
        }
        return $storeLabels;
    }
}
