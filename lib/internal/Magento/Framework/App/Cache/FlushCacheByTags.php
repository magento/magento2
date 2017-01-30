<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

/**
 * Automatic cache cleaner plugin
 */
class FlushCacheByTags
{
    /**
     * @var Type\FrontendPool
     */
    private $cachePool;

    /**
     * @var array
     */
    private $cacheList;

    /**
     * @var StateInterface
     */
    private $cacheState;

    /**
     * FlushCacheByTags constructor.
     *
     * @param Type\FrontendPool $cachePool
     * @param StateInterface $cacheState
     * @param array $cacheList
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cachePool,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        array $cacheList
    ) {
        $this->cachePool = $cachePool;
        $this->cacheState = $cacheState;
        $this->cacheList = $cacheList;
    }

    /**
     * Clean cache on save object
     *
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\AbstractResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        $subject,
        \Closure $proceed,
        $object = null
    ) {
        $tags = [];
        if ($object instanceof \Magento\Framework\Model\AbstractModel) {
            $tags = $object->getIdentities();
        }
        $result = $proceed($object);
        if ($object instanceof \Magento\Framework\Model\AbstractModel) {
            $this->cleanCacheByTags($tags);
        }
        return $result;
    }

    /**
     * Clean cache on delete object
     *
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\AbstractResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        $subject,
        \Closure $proceed,
        $object = null
    ) {
        $tags = [];
        if ($object instanceof \Magento\Framework\Model\AbstractModel) {
            $tags = $object->getIdentities();
        }
        $result = $proceed($object);
        $this->cleanCacheByTags($tags);
        return $result;
    }

    /**
     * Clean cache by tags
     *
     * @param  string[] $tags
     * @return void
     */
    private function cleanCacheByTags($tags)
    {
        if (empty($tags)) {
            return;
        }
        foreach ($this->cacheList as $cacheType) {
            if ($this->cacheState->isEnabled($cacheType)) {
                $this->cachePool->get($cacheType)->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                    array_unique($tags)
                );
            }
        }
    }
}
