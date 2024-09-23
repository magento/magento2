<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Observer;

use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\TagResolver;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;

/**
 * Invalidates graphql resolver result cache.
 */
class InvalidateGraphQlResolverCacheObserver implements ObserverInterface
{
    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var CacheState
     */
    private $cacheState;

    /**
     * @var TagResolver
     */
    private $tagResolver;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param CacheState $cacheState
     * @param TagResolver $tagResolver
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        CacheState $cacheState,
        TagResolver $tagResolver
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->cacheState = $cacheState;
        $this->tagResolver = $tagResolver;
    }

    /**
     * Clean identities of event object from GraphQL Resolver cache
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $object = $observer->getEvent()->getObject();

        if (!is_object($object)) {
            return;
        }

        if (!$this->cacheState->isEnabled(GraphQlResolverCache::TYPE_IDENTIFIER)) {
            return;
        }

        $tags = $this->tagResolver->getTags($object);

        if (!empty($tags)) {
            $this->graphQlResolverCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
        }
    }
}
