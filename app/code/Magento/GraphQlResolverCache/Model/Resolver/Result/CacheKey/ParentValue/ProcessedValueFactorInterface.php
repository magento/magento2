<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\ParentValue;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\GenericFactorInterface;

/**
 * Interface for key factors that are used to calculate the resolver cache key.
 */
interface ProcessedValueFactorInterface extends GenericFactorInterface
{
    /**
     * Returns the runtime value that should be used as factor.
     *
     * @param ContextInterface $context
     * @param array|null $processedParentValue
     * @return string
     */
    public function getFactorValue(ContextInterface $context, ?array $processedParentValue = null): string;
}
