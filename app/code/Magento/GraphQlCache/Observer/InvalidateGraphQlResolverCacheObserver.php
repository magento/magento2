<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Observer;

use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\TagResolver as TagResolver;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;

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
     * @var array
     */
    private $invalidatableObjectTypes;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param CacheState $cacheState
     * @param TagResolver $tagResolver
     * @param array $invalidatableObjectTypes
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        CacheState $cacheState,
        TagResolver $tagResolver,
        array $invalidatableObjectTypes = []
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->cacheState = $cacheState;
        $this->tagResolver = $tagResolver;
        $this->invalidatableObjectTypes = $invalidatableObjectTypes;
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
