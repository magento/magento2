<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

class ResolverLocator
{
    /**
     * Map of Resolver Class Name => Identity Provider
     *
     * @var string[]
     */
    private array $cacheableResolverClassNameIdentityMap;

    /**
     * @param array $cacheableResolverClassNameIdentityMap
     */
    public function __construct(array $cacheableResolverClassNameIdentityMap)
    {
        $this->cacheableResolverClassNameIdentityMap = $cacheableResolverClassNameIdentityMap;
    }

    /**
     * Get map of Resolver Class Name => Identity Provider
     *
     * @return string[]
     */
    public function getCacheableResolverClassNameIdentityMap(): array
    {
        return $this->cacheableResolverClassNameIdentityMap;
    }
}
