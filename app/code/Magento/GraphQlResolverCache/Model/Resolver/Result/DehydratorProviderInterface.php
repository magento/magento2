<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Interface for resolver-based dehydrator provider.
 */
interface DehydratorProviderInterface
{
    /**
     * Returns dehydrator for the given resolver, null if no dehydrators configured.
     *
     * @param ResolverInterface $resolver
     *
     * @return DehydratorInterface|null
     */
    public function getDehydratorForResolver(ResolverInterface $resolver) : ?DehydratorInterface;
}
