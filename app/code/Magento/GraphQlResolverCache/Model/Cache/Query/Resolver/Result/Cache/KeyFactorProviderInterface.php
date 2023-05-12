<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Cache;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Interface for key factors that are used to calculate the resolver cache key.
 */
interface KeyFactorProviderInterface
{
    /**
     * Name of the cache key factor.
     *
     * @return string
     */
    public function getFactorName(): string;

    /**
     * Returns the runtime value that should be used as factor.
     *
     * @param ContextInterface $context
     * @param array|null $parentResolverData
     * @return string
     */
    public function getFactorValue(ContextInterface $context, ?array $parentResolverData = null): string;
}
