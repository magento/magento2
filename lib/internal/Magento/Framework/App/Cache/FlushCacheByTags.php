<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

/**
 * Automatic cache cleaner plugin
 * @since 2.0.9
 */
class FlushCacheByTags
{
    /**
     * @var Type\FrontendPool
     * @since 2.0.9
     */
    private $cachePool;

    /**
     * @var array
     * @since 2.0.9
     */
    private $cacheList;

    /**
     * @var StateInterface
     * @since 2.0.9
     */
    private $cacheState;

    /**
     * @var Tag\Resolver
     * @since 2.1.3
     */
    private $tagResolver;

    /**
     * FlushCacheByTags constructor.
     *
     * @param Type\FrontendPool $cachePool
     * @param StateInterface $cacheState
     * @param array $cacheList
     * @param Tag\Resolver $tagResolver
     * @since 2.0.9
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cachePool,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        array $cacheList,
        \Magento\Framework\App\Cache\Tag\Resolver $tagResolver
    ) {
        $this->cachePool = $cachePool;
        $this->cacheState = $cacheState;
        $this->cacheList = $cacheList;
        $this->tagResolver = $tagResolver;
    }

    /**
     * Clean cache on save object
     *
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\AbstractResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.9
     */
    public function aroundSave(
        \Magento\Framework\Model\ResourceModel\AbstractResource $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $result = $proceed($object);
        $tags = $this->tagResolver->getTags($object);
        $this->cleanCacheByTags($tags);

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
     * @since 2.0.9
     */
    public function aroundDelete(
        \Magento\Framework\Model\ResourceModel\AbstractResource $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $tags = $this->tagResolver->getTags($object);
        $result = $proceed($object);
        $this->cleanCacheByTags($tags);
        return $result;
    }

    /**
     * Clean cache by tags
     *
     * @param  string[] $tags
     * @return void
     * @since 2.0.9
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
