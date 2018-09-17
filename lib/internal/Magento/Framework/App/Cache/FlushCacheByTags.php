<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Cache\Tag\Resolver;

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
     * @var Tag\Resolver
     */
    private $tagResolver;

    /**
     * FlushCacheByTags constructor.
     *
     * @param Type\FrontendPool $cachePool
     * @param StateInterface $cacheState
     * @param array $cacheList
     * @param Tag\Resolver|null $tagResolver
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cachePool,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        array $cacheList,
        \Magento\Framework\App\Cache\Tag\Resolver $tagResolver = null
    ) {
        $this->cachePool = $cachePool;
        $this->cacheState = $cacheState;
        $this->cacheList = $cacheList;
        $this->tagResolver = $tagResolver ?: ObjectManager::getInstance()->get(Resolver::class);
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
