<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Interface for key factors that are used to calculate the resolver cache key.
 */
interface GenericFactorProviderInterface
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
     * @return string
     */
    public function getFactorValue(ContextInterface $context): string;
}
