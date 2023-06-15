<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Interface for key factors that are used to calculate the resolver cache key basing on parent value.
 */
interface ParentValueFactorProviderInterface
{
    /**
     * Name of the cache key factor.
     *
     * @return string
     */
    public function getFactorName(): string;

    /**
     * Checks if the original resolver data required.
     *
     * Must return true if any:
     * - original resolved data is required to resolve key factor
     *
     * Can return false if any:
     * - key factor can be resolved from unprocessed cached value
     *
     * @return bool
     */
    public function isRequiredOrigData(): bool;

    /**
     * Returns the runtime value that should be used as factor.
     *
     * @param ContextInterface $context
     * @param array $parentValue
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getFactorValue(ContextInterface $context, array $parentValue): string;
}
