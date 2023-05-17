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
interface ParentValueFactorProviderInterface extends FactorProviderInterface
{
    /**
     * Returns the runtime value that should be used as factor.
     *
     * @param ContextInterface $context
     * @param array|null $parentResolverData
     * @return string
     */
    public function getFactorValue(ContextInterface $context, ?array $parentResolverData = null): string;
}
