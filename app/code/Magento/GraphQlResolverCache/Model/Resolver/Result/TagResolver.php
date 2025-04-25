<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\Cache\Tag\Strategy\Factory as OtherCachesStrategyFactory;
use Magento\GraphQlResolverCache\App\Cache\Tag\Strategy\Locator as ResolverCacheStrategyLocator;

class TagResolver extends Resolver
{
    /**
     * @var ResolverCacheStrategyLocator
     */
    private $resolverCacheTagStrategyLocator;

    /**
     * @var array
     */
    private $invalidatableObjectTypes;

    /**
     * GraphQL Resolver cache-specific tag resolver for the purpose of invalidation
     *
     * @param ResolverCacheStrategyLocator $resolverCacheStrategyLocator
     * @param OtherCachesStrategyFactory $otherCachesStrategyFactory
     * @param array $invalidatableObjectTypes
     */
    public function __construct(
        ResolverCacheStrategyLocator $resolverCacheStrategyLocator,
        OtherCachesStrategyFactory $otherCachesStrategyFactory,
        array $invalidatableObjectTypes = []
    ) {
        $this->resolverCacheTagStrategyLocator = $resolverCacheStrategyLocator;
        $this->invalidatableObjectTypes = $invalidatableObjectTypes;

        parent::__construct($otherCachesStrategyFactory);
    }

    /**
     * @inheritdoc
     */
    public function getTags($object)
    {
        $isInvalidatable = false;

        foreach ($this->invalidatableObjectTypes as $invalidatableObjectType) {
            $isInvalidatable = $object instanceof $invalidatableObjectType;

            if ($isInvalidatable) {
                break;
            }
        }

        if (!$isInvalidatable) {
            return [];
        }

        $resolverCacheTagStrategy = $this->resolverCacheTagStrategyLocator->getStrategy($object);

        if ($resolverCacheTagStrategy) {
            return $resolverCacheTagStrategy->getTags($object);
        }

        return parent::getTags($object);
    }
}
