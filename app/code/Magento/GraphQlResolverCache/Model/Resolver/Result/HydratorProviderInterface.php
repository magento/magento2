<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Interface for resolver-based hydrator provider.
 */
interface HydratorProviderInterface
{
    /**
     * Returns hydrator for the given resolver, null if no hydrators configured.
     *
     * @param ResolverInterface $resolver
     *
     * @return HydratorInterface|null
     */
    public function getHydratorForResolver(ResolverInterface $resolver) : ?HydratorInterface;
}
