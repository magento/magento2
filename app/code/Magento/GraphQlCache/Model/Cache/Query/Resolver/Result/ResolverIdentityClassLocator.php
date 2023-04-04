<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQlCache\Model\Resolver\IdentityPool;

class ResolverIdentityClassLocator
{
    /**
     * @var IdentityPool
     */
    private $identityPool;

    /**
     * Map of Resolver Class Name => Identity Provider
     *
     * @var string[]
     */
    private array $cacheableResolverClassNameIdentityMap;

    /**
     * @param IdentityPool $identityPool
     * @param array $cacheableResolverClassNameIdentityMap
     */
    public function __construct(
        IdentityPool $identityPool,
        array $cacheableResolverClassNameIdentityMap
    ) {
        $this->identityPool = $identityPool;
        $this->cacheableResolverClassNameIdentityMap = $cacheableResolverClassNameIdentityMap;
    }

    /**
     * Get Identity provider based on $resolver
     *
     * @param ResolverInterface $resolver
     * @return IdentityInterface|null
     */
    public function getIdentityFromResolver(ResolverInterface $resolver): ?IdentityInterface
    {
        $resolverClassHierarchy = array_merge(
            [get_class($resolver) => get_class($resolver)],
            class_parents($resolver),
            class_implements($resolver)
        );

        $cacheableResolverClassNames = array_keys($this->cacheableResolverClassNameIdentityMap);

        $matchingCacheableResolverClassNames = array_intersect($cacheableResolverClassNames, $resolverClassHierarchy);

        if (!count($matchingCacheableResolverClassNames)) {
            return null;
        }

        $matchingCacheableResolverClassName = reset($matchingCacheableResolverClassNames);
        $matchingCacheableResolverIdentityClassName = $this->cacheableResolverClassNameIdentityMap[
            $matchingCacheableResolverClassName
        ];

        return $this->identityPool->get($matchingCacheableResolverIdentityClassName);
    }
}
