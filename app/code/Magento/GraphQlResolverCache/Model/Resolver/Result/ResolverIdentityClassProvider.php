<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;

class ResolverIdentityClassProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Map of Resolver Class Name => Identity Provider
     *
     * @var string[]
     */
    private array $cacheableResolverClassNameIdentityMap;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $cacheableResolverClassNameIdentityMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $cacheableResolverClassNameIdentityMap
    ) {
        $this->objectManager = $objectManager;
        $this->cacheableResolverClassNameIdentityMap = $cacheableResolverClassNameIdentityMap;
    }

    /**
     * Get Identity provider based on $resolver instance.
     *
     * @param ResolverInterface $resolver
     * @return Cache\IdentityInterface|null
     */
    public function getIdentityFromResolver(ResolverInterface $resolver): ?Cache\IdentityInterface
    {
        $matchingIdentityProviderClassName = null;

        foreach ($this->cacheableResolverClassNameIdentityMap as $resolverClassName => $identityProviderClassName) {
            if ($resolver instanceof $resolverClassName) {
                $matchingIdentityProviderClassName = $identityProviderClassName;
                break;
            }
        }

        if (!$matchingIdentityProviderClassName) {
            return null;
        }

        return $this->objectManager->get($matchingIdentityProviderClassName);
    }
}
