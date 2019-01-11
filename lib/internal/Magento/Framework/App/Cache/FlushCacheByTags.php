<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Cache;

use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * Automatic cache cleaner plugin
 */
class FlushCacheByTags
{
    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    private $cachePool;

    /**
     * @var array
     */
    private $cacheList;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var \Magento\Framework\App\Cache\Tag\Resolver
     */
    private $tagResolver;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cachePool
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param string[] $cacheList
     * @param \Magento\Framework\App\Cache\Tag\Resolver $tagResolver
     */
    public function __construct(
        FrontendPool $cachePool,
        StateInterface $cacheState,
        array $cacheList,
        Resolver $tagResolver
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
     */
    public function aroundSave(AbstractResource $subject, \Closure $proceed, AbstractModel $object): AbstractResource
    {
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
    public function aroundDelete(AbstractResource $subject, \Closure $proceed, AbstractModel $object): AbstractResource
    {
        $tags = $this->tagResolver->getTags($object);
        $result = $proceed($object);
        $this->cleanCacheByTags($tags);

        return $result;
    }

    /**
     * Clean cache by tags
     *
     * @param string[] $tags
     * @return void
     */
    private function cleanCacheByTags(array $tags): void
    {
        if (!$tags) {
            return;
        }
        foreach ($this->cacheList as $cacheType) {
            if ($this->cacheState->isEnabled($cacheType)) {
                $this->cachePool->get($cacheType)->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                    \array_unique($tags)
                );
            }
        }
    }
}
